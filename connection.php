<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}

#$hostname = "localhost";
#$username = "root";     
#$password = "";          
#$database = "gatepass_db_new";


$hostname = "sql208.infinityfree.com";
$username = "if0_42231043";     
$password = "Srushti1432005";          
$database = "if0_42231043_gatepass_db_new";

$conn = mysqli_connect($hostname, $username, $password, $database);


if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

#echo "You are connected"; 
?>
