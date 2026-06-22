<?php
session_start();
include('connection.php');
if (!isset($_SESSION['faculty_id'])) {
    header("Location: index.php");
    exit();
}
$faculty_id = intval($_SESSION['faculty_id']);
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);

// Update faculty_registration with the correct column names
$query = "UPDATE faculty_registration SET faculty_fullname='$name', faculty_email='$email' WHERE faculty_id=$faculty_id";

if (mysqli_query($conn, $query)) {
    // Also update the users table so login/profile stay in sync
    $update_users = "UPDATE users SET name='$name', email='$email' WHERE user_id=$faculty_id";
    mysqli_query($conn, $update_users);

    header("Location: faculty-dashboard.php");
    exit();
} else {
    echo "Error updating profile: " . mysqli_error($conn);
}
mysqli_close($conn);
?>