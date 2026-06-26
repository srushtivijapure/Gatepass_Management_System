<?php
/**
 * Faculty Profile Update with Transaction Support
 * Updates both faculty_registration and users tables atomically
 * Ensures new credentials work immediately after update
 */

session_start();
include('connection.php');

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    $_SESSION['error'] = "Please log in first.";
    header("Location: index.php");
    exit();
}

$faculty_id = intval($_SESSION['faculty_id']);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation
if (empty($name) || empty($email)) {
    $_SESSION['update_error'] = "Name and email are required.";
    header("Location: faculty-dashboard.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['update_error'] = "Invalid email format.";
    header("Location: faculty-dashboard.php");
    exit();
}

if (strlen($name) < 2) {
    $_SESSION['update_error'] = "Name must be at least 2 characters long.";
    header("Location: faculty-dashboard.php");
    exit();
}

try {
    // Check if new email is already used by another user
    $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $email_check->bind_param("si", $email, $faculty_id);
    $email_check->execute();
    $email_result = $email_check->get_result();

    if ($email_result->num_rows > 0) {
        $email_check->close();
        $_SESSION['update_error'] = "This email is already in use by another account.";
        header("Location: faculty-dashboard.php");
        exit();
    }
    $email_check->close();

    // Capture current email before changing anything, used as a fallback
    // lookup key for faculty_registration in case faculty_id ever drifts
    // out of sync with users.user_id.
    $old_email_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $old_email_stmt->bind_param("i", $faculty_id);
    $old_email_stmt->execute();
    $old_email_row = $old_email_stmt->get_result()->fetch_assoc();
    $old_email_stmt->close();
    $old_email = $old_email_row['email'] ?? $email;

    // Start transaction for atomic updates
    $conn->begin_transaction();

    // Update faculty_registration table with correct column names.
    // Match by faculty_id OR by the old email, and re-sync the id while
    // we're at it - protects against legacy rows where the two tables'
    // ids have drifted apart.
    $stmt1 = $conn->prepare("
        UPDATE faculty_registration 
        SET faculty_fullname = ?, faculty_email = ?, faculty_id = ?
        WHERE faculty_id = ? OR faculty_email = ?
    ");
    
    if (!$stmt1) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt1->bind_param("ssiis", $name, $email, $faculty_id, $faculty_id, $old_email);
    
    if (!$stmt1->execute()) {
        throw new Exception("Failed to update faculty_registration: " . $stmt1->error);
    }

    $affected_rows_1 = $stmt1->affected_rows;
    $stmt1->close();

    // Update users table to keep login synchronized
    $stmt2 = $conn->prepare("
        UPDATE users 
        SET name = ?, email = ? 
        WHERE user_id = ?
    ");

    if (!$stmt2) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt2->bind_param("ssi", $name, $email, $faculty_id);

    if (!$stmt2->execute()) {
        throw new Exception("Failed to update users table: " . $stmt2->error);
    }

    $affected_rows_2 = $stmt2->affected_rows;
    $stmt2->close();

    // Commit transaction
    $conn->commit();

    // Verify updates
    if ($affected_rows_1 > 0 || $affected_rows_2 > 0) {
        // Update session with new info
        $_SESSION['username'] = $name;
        $_SESSION['user_email'] = $email;

        $_SESSION['update_success'] = "✓ Profile updated successfully! You can now log in with your new email.";
        
    } else {
        $_SESSION['update_message'] = "No changes were made to your profile.";
    }

    $conn->close();
    header("Location: faculty-dashboard.php");
    exit();

} catch (Exception $e) {
    // Rollback on any error
    $conn->rollback();
    
    $_SESSION['update_error'] = "Error updating profile: " . $e->getMessage();
    $conn->close();
    header("Location: faculty-dashboard.php");
    exit();
}
