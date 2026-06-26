<?php
session_start();
include 'connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $enrollmentNo = trim($_POST["enrollment_no"] ?? '');
    $fullname     = trim($_POST["studentname"] ?? '');
    $semail       = trim($_POST["studentemail"] ?? '');
    $spassword    = trim($_POST["studentpassword"] ?? '');
    $sdept        = trim($_POST["dept"] ?? '');
    $year         = trim($_POST["year"] ?? '');

    $errors = [];

    if (empty($enrollmentNo) || empty($fullname) || empty($semail) || empty($spassword) || empty($sdept) || empty($year)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($semail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($spassword) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(' ', $errors);
        $_SESSION['show_form'] = 'student';
        header("Location: index.php#registerOptions");
        exit();
    }

    // Check if email already exists (student_registration table)
    $check_stmt = $conn->prepare("SELECT email FROM student_registration WHERE email = ?");
    $check_stmt->bind_param("s", $semail);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $check_stmt->close();
        $_SESSION['register_error'] = "This email is already registered. Please use another email or log in.";
        $_SESSION['show_form'] = 'student';
        header("Location: index.php#registerOptions");
        exit();
    }
    $check_stmt->close();

    // Also guard against duplicate email in users table (shared login table)
    $check_users = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_users->bind_param("s", $semail);
    $check_users->execute();
    $users_result = $check_users->get_result();
    if ($users_result->num_rows > 0) {
        $check_users->close();
        $_SESSION['register_error'] = "This email is already registered. Please use another email or log in.";
        $_SESSION['show_form'] = 'student';
        header("Location: index.php#registerOptions");
        exit();
    }
    $check_users->close();

    $hashed_password = password_hash($spassword, PASSWORD_DEFAULT);

    // Insert into users table FIRST (shared login table)
    $role = "student";
    $user_insert = $conn->prepare("INSERT INTO users (name, email, password, role, department, year) VALUES (?, ?, ?, ?, ?, ?)");
    $user_insert->bind_param("ssssss", $fullname, $semail, $hashed_password, $role, $sdept, $year);

    if (!$user_insert->execute()) {
        $_SESSION['register_error'] = "Could not create account. Please try again.";
        $_SESSION['show_form'] = 'student';
        header("Location: index.php#registerOptions");
        exit();
    }
    $new_user_id = $conn->insert_id;
    $user_insert->close();

    $insert_stmt = $conn->prepare("INSERT INTO student_registration (enrollment_no, full_name, email, password, department, studying_year, student_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssssi", $enrollmentNo, $fullname, $semail, $hashed_password, $sdept, $year, $new_user_id);

    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        $conn->close();
        $_SESSION['register_success'] = "Registration successful! You can now log in.";
        header("Location: index.php");
        exit();
    } else {
        $insert_stmt->close();
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        $_SESSION['show_form'] = 'student';
        header("Location: index.php#registerOptions");
        exit();
    }
}
// If accessed directly without POST data, just send back to the registration page
header("Location: index.php");
exit();
