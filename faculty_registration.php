<?php
session_start();
include 'connection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!$conn) {
    $_SESSION['register_error'] = "Database connection failed. Please try again later.";
    $_SESSION['show_form'] = 'faculty';
    header("Location: index.php#registerOptions");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $faculty_fullname = trim($_POST["fullname"] ?? '');
    $faculty_email    = trim($_POST["email"] ?? '');
    $password         = trim($_POST["password"] ?? '');
    $faculty_dept     = trim($_POST["dept"] ?? '');
    $faculty_tgbatch  = trim($_POST["tgbatch"] ?? '');

    $errors = [];

    if (empty($faculty_fullname) || empty($faculty_email) || empty($password) || empty($faculty_dept) || empty($faculty_tgbatch)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($faculty_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(' ', $errors);
        $_SESSION['show_form'] = 'faculty';
        header("Location: index.php#registerOptions");
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists in faculty_registration
    $check_stmt = $conn->prepare("SELECT faculty_email FROM faculty_registration WHERE faculty_email = ?");
    $check_stmt->bind_param("s", $faculty_email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $check_stmt->close();
        $_SESSION['register_error'] = "This email is already registered. Please use another email or log in.";
        $_SESSION['show_form'] = 'faculty';
        header("Location: index.php#registerOptions");
        exit();
    }
    $check_stmt->close();

    // Also guard against duplicate email in users table
    $check_users = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check_users->bind_param("s", $faculty_email);
    $check_users->execute();
    $users_result = $check_users->get_result();
    if ($users_result->num_rows > 0) {
        $check_users->close();
        $_SESSION['register_error'] = "This email is already registered. Please use another email or log in.";
        $_SESSION['show_form'] = 'faculty';
        header("Location: index.php#registerOptions");
        exit();
    }
    $check_users->close();

    // Insert into users table FIRST
    $role = "faculty";
    $user_insert = $conn->prepare("INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, ?, ?)");
    $user_insert->bind_param("sssss", $faculty_fullname, $faculty_email, $hashed_password, $role, $faculty_dept);

    if (!$user_insert->execute()) {
        $_SESSION['register_error'] = "Could not create account. Please try again.";
        $_SESSION['show_form'] = 'faculty';
        header("Location: index.php#registerOptions");
        exit();
    }

    $new_user_id = $conn->insert_id;
    $user_insert->close();

    // Insert into faculty_registration with that ID
    $stmt = $conn->prepare("INSERT INTO faculty_registration (faculty_id, faculty_fullname, faculty_email, password, faculty_dept, faculty_tgbatch) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $new_user_id, $faculty_fullname, $faculty_email, $hashed_password, $faculty_dept, $faculty_tgbatch);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        $_SESSION['register_success'] = "Registration successful! You can now log in.";
        header("Location: index.php");
        exit();
    } else {
        $stmt->close();
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        $_SESSION['show_form'] = 'faculty';
        header("Location: index.php#registerOptions");
        exit();
    }
}

header("Location: index.php");
exit();
