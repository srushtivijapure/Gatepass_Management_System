<?php
/**
 * Student Profile Update with Transaction Support
 * Updates both student_registration and users tables atomically
 * Ensures new credentials work immediately after update
 */

session_start();
include('connection.php');

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    $_SESSION['error'] = "Please log in first.";
    header("Location: index.php");
    exit();
}

$student_id = intval($_SESSION['student_id']);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation
if (empty($name) || empty($email)) {
    $_SESSION['update_error'] = "Name and email are required.";
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['update_error'] = "Invalid email format.";
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();
}

if (strlen($name) < 2) {
    $_SESSION['update_error'] = "Name must be at least 2 characters long.";
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();
}

try {
    // Check if new email is already used by another user
    $email_check = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $email_check->bind_param("si", $email, $student_id);
    $email_check->execute();
    $email_result = $email_check->get_result();

    if ($email_result->num_rows > 0) {
        $email_check->close();
        $_SESSION['update_error'] = "This email is already in use by another account.";
        header("Location: student-dashboard.php?student_id=$student_id");
        exit();
    }
    $email_check->close();

    // Capture the CURRENT email from `users` before we change anything.
    // We need this as the lookup key for student_registration, since that
    // table may still be keyed by an out-of-sync student_id (legacy rows
    // created before registration kept both tables' IDs in sync).
    $old_email_stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $old_email_stmt->bind_param("i", $student_id);
    $old_email_stmt->execute();
    $old_email_row = $old_email_stmt->get_result()->fetch_assoc();
    $old_email_stmt->close();
    $old_email = $old_email_row['email'] ?? $email;

    // Start transaction for atomic updates
    $conn->begin_transaction();

    // Update users table FIRST (primary authentication table)
    $stmt1 = $conn->prepare("
        UPDATE users 
        SET name = ?, email = ? 
        WHERE user_id = ?
    ");

    if (!$stmt1) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt1->bind_param("ssi", $name, $email, $student_id);

    if (!$stmt1->execute()) {
        throw new Exception("Failed to update users table: " . $stmt1->error);
    }

    $affected_rows_1 = $stmt1->affected_rows;
    $stmt1->close();

    // Update student_registration table to keep profile in sync.
    // Match by student_id normally, but also fall back to the old email
    // in the same statement (via OR) - this way even if student_id is
    // stale/orphaned for this account, the email-based match still finds
    // and fixes the right row, AND re-syncs the id going forward.
    $stmt2 = $conn->prepare("
        UPDATE student_registration 
        SET full_name = ?, email = ?, student_id = ?
        WHERE student_id = ? OR email = ?
    ");

    if (!$stmt2) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt2->bind_param("ssiis", $name, $email, $student_id, $student_id, $old_email);

    if (!$stmt2->execute()) {
        throw new Exception("Failed to update student_registration: " . $stmt2->error);
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
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();

} catch (Exception $e) {
    // Rollback on any error
    $conn->rollback();
    
    $_SESSION['update_error'] = "Error updating profile: " . $e->getMessage();
    $conn->close();
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();
}
