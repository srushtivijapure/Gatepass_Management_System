<?php
/**
 * Forgot Password flow (no email/SMTP required).
 *
 * Step 1 (action=verify): user enters their registered email + department.
 *   If it matches a row in `users`, we mark them as "verified" for this
 *   session only and show the "set new password" form on index.php.
 *
 * Step 2 (action=reset): user submits a new password. We update it in
 *   BOTH the `users` table and the matching student_registration /
 *   faculty_registration table, then clear the verified flag.
 *
 * NOTE: If you later get a working SMTP/email provider (e.g. Brevo,
 * SendGrid, Mailgun - InfinityFree's free tier does not support SMTP),
 * you can swap Step 1 for a "send reset link with token" flow instead.
 * The token table / mail-sending code would go right here.
 */

session_start();
include('connection.php');

$action = $_POST['action'] ?? '';

if ($action === 'verify') {

    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $department = trim($_POST['department'] ?? '');

    if (empty($email) || empty($department)) {
        $_SESSION['reset_message'] = "Please enter both your email and department.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    $query = "SELECT user_id, department FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (strcasecmp(trim($user['department']), $department) === 0) {
            // Verified - allow password reset for this session
            $_SESSION['reset_verified_user_id'] = $user['user_id'];
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

    $conn->close();
    header('Location: index.php#forgotPasswordStep1');
    exit();

} elseif ($action === 'reset') {

    if (!isset($_SESSION['reset_verified_user_id'])) {
        $_SESSION['reset_message'] = "Please verify your identity first.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php#forgotPasswordStep1');
        exit();
    }

    $user_id = intval($_SESSION['reset_verified_user_id']);
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (strlen($new_password) < 6) {
        $_SESSION['reset_message'] = "Password must be at least 6 characters long.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['reset_message'] = "Passwords do not match.";
        $_SESSION['reset_message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the shared users table
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    $stmt->execute();
    $stmt->close();

    // Find the role so we know which secondary table to update
    $role_query = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $role_query->bind_param("i", $user_id);
    $role_query->execute();
    $role_result = $role_query->get_result();
    $role_row = $role_result->fetch_assoc();
    $role_query->close();

    if ($role_row) {
        if ($role_row['role'] === 'student') {
            $stmt2 = $conn->prepare("UPDATE student_registration SET password = ? WHERE student_id = ?");
            $stmt2->bind_param("si", $hashed_password, $user_id);
            $stmt2->execute();
            $stmt2->close();
        } elseif ($role_row['role'] === 'faculty') {
            $stmt2 = $conn->prepare("UPDATE faculty_registration SET password = ? WHERE faculty_id = ?");
            $stmt2->bind_param("si", $hashed_password, $user_id);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    unset($_SESSION['reset_verified_user_id']);
    $_SESSION['reset_message'] = "Password updated successfully! You can now log in.";
    $_SESSION['reset_message_type'] = 'success';

    $conn->close();
    header('Location: index.php');
    exit();

} else {
    header('Location: index.php');
    exit();
}
