<?php
/**
 * Forgot Password flow with transaction support.
 *
 * Step 1: User enters registered email + department for identity verification
 * Step 2: User submits new password - updates BOTH tables atomically
 */

session_start();
include('connection.php');

$action = $_POST['action'] ?? '';

if ($action === 'verify') {

    $email = trim($_POST['email'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (empty($email) || empty($department)) {
        $_SESSION['reset_message'] = "Please enter both your email and department.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_message'] = "Please enter a valid email address.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    // Use prepared statement to prevent SQL injection
    $query = $conn->prepare("SELECT user_id, department FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (strcasecmp(trim($user['department']), trim($department)) === 0) {
            // Verified - allow password reset for this session
            $_SESSION['reset_verified_user_id'] = $user['user_id'];
            $_SESSION['reset_verified_email'] = $email;  // Store for audit trail
            $_SESSION['reset_message'] = "Identity verified. Please set your new password below.";
            $_SESSION['reset_message_type'] = 'success';
        } else {
            $_SESSION['reset_message'] = "Email and department do not match our records.";
            $_SESSION['reset_message_type'] = 'danger';
        }
    } else {
        $_SESSION['reset_message'] = "No account found with that email.";
        $_SESSION['reset_message_type'] = 'danger';
    }

    $query->close();
    $conn->close();
    header('Location: index.php#forgotPasswordStep1');
    exit();

} elseif ($action === 'reset') {

    // Verify user went through step 1
    if (!isset($_SESSION['reset_verified_user_id'])) {
        $_SESSION['reset_message'] = "Please verify your identity first.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    $user_id = intval($_SESSION['reset_verified_user_id']);
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($new_password)) {
        $_SESSION['reset_message'] = "Password cannot be empty.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    if (strlen($new_password) < 6) {
        $_SESSION['reset_message'] = "Password must be at least 6 characters long.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['reset_message'] = "Passwords do not match.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Start transaction for atomic updates
    try {
        $conn->begin_transaction();

        // Step 1: Get the user's role
        $role_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $role_query->bind_param("i", $user_id);
        $role_query->execute();
        $role_result = $role_query->get_result();
        $role_row = $role_result->fetch_assoc();
        $role_query->close();

        if (!$role_row) {
            throw new Exception("User not found.");
        }

        // Step 2: Update users table (primary authentication table)
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $hashed_password, $user_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update users table: " . $stmt->error);
        }
        $stmt->close();

        // Step 3: Update role-specific table based on user's role.
        // Match by id OR by email - this self-heals legacy accounts where
        // student_registration.student_id / faculty_registration.faculty_id
        // drifted out of sync with users.user_id (created before the
        // registration script kept both tables linked correctly).
        if ($role_row['role'] === 'student') {
            $email_lookup = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $email_lookup->bind_param("i", $user_id);
            $email_lookup->execute();
            $email_row = $email_lookup->get_result()->fetch_assoc();
            $email_lookup->close();
            $current_email = $email_row['email'] ?? null;

            $stmt2 = $conn->prepare("
                UPDATE student_registration
                SET password = ?, student_id = ?
                WHERE student_id = ? OR email = ?
            ");
            if (!$stmt2) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt2->bind_param("siis", $hashed_password, $user_id, $user_id, $current_email);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to update student_registration: " . $stmt2->error);
            }
            $stmt2->close();

        } elseif ($role_row['role'] === 'faculty') {
            $email_lookup = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $email_lookup->bind_param("i", $user_id);
            $email_lookup->execute();
            $email_row = $email_lookup->get_result()->fetch_assoc();
            $email_lookup->close();
            $current_email = $email_row['email'] ?? null;

            $stmt2 = $conn->prepare("
                UPDATE faculty_registration
                SET password = ?, faculty_id = ?
                WHERE faculty_id = ? OR faculty_email = ?
            ");
            if (!$stmt2) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt2->bind_param("siis", $hashed_password, $user_id, $user_id, $current_email);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to update faculty_registration: " . $stmt2->error);
            }
            $stmt2->close();
        }

        // Commit transaction
        $conn->commit();

        // Clear session variables
        unset($_SESSION['reset_verified_user_id']);
        unset($_SESSION['reset_verified_email']);

        $_SESSION['reset_message'] = "✓ Password updated successfully! You can now log in with your new password.";
        $_SESSION['reset_message_type'] = 'success';

    } catch (Exception $e) {
        // Rollback transaction on any error
        $conn->rollback();

        $_SESSION['reset_message'] = "Error updating password: " . $e->getMessage();
        $_SESSION['reset_message_type'] = 'danger';
    }

    $conn->close();
    header('Location: index.php');
    exit();

} else {
    header('Location: index.php');
    exit();
}
