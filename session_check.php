<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session expired! Please log in again.'); window.location.href='index.php';</script>";
    exit();
}
?>
