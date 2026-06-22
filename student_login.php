<?php
session_start();
include 'connection.php';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Please enter both email and password.";
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {

    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        if ($user['role'] != 'student') {
            $_SESSION['error'] = "This account is registered as Faculty. Please use Faculty Login.";
            header("Location: index.php");
            exit();
        }

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['student_id'] = $user['user_id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role'] = 'student';
        $_SESSION['dept'] = $user['department'];
        $_SESSION['year'] = $user['year'];

        $stmt->close();
        $conn->close();

        header("Location: student-dashboard.php?student_id=" . $user['user_id']);
        exit();

    } else {
        $_SESSION['error'] = "Incorrect password.";
    }

} else {
    $_SESSION['error'] = "No account found with that email.";
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
