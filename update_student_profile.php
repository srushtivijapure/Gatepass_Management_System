<?php
session_start();
include('connection.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = intval($_SESSION['student_id']);
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);

$update_users = "UPDATE users SET name='$name', email='$email' WHERE user_id=$student_id";
mysqli_query($conn, $update_users);

$update_student = "UPDATE student_registration SET full_name='$name', email='$email' WHERE student_id=$student_id";

if (mysqli_query($conn, $update_student)) {
    header("Location: student-dashboard.php?student_id=$student_id");
    exit();
} else {
    echo "Error updating profile: " . mysqli_error($conn);
}
mysqli_close($conn);
?>