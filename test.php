<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "Step 1: PHP is running.<br>";

include('connection.php');
echo "Step 2: connection.php loaded without crashing.<br>";

echo "If you see this, the connection worked!";
?>