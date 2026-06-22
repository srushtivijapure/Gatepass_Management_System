<?php
session_start(); // Start the session

// Check if session ID is set
if (!isset($_SESSION["student_id"])) {
    echo "Session ID: " . session_id(); // Output the session ID for debugging
    die("Session not found. Please log in again.");
}

include('connection.php'); // Ensure this file exists and connects to DB

// Check the student ID stored in the session
echo "Student ID from session: " . $_SESSION["student_id"] . "<br>";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}

if (!isset($_POST["year"], $_POST["teacher"], $_POST["classcordinator"], $_POST["hod"], $_POST["reason"])) {
    die("Missing form fields.");
}

$student_id = $_SESSION["student_id"];
$year = $_POST["year"];
$teacher = $_POST["teacher"];
$class_coordinator = $_POST["classcordinator"];
$hod = $_POST["hod"];
$reason = $_POST["reason"];
$status = "Pending"; // Default status

// Check if the student ID exists in the student_registration table
$checkStudentQuery = "SELECT * FROM student_registration WHERE student_id = ?";
$stmt = $conn->prepare($checkStudentQuery);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Student ID does not exist in the database.");
}

// Continue with the insertion if the student ID exists
$query = "INSERT INTO `gate_pass_requests`(`student_id`, `reason`, `status`, `year`, `teacher`, `class_coordinator`, `hod`) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

// Binding the parameters - note that student_id is an integer and other fields are strings
$stmt->bind_param("issssss", $student_id, $reason, $status, $year, $teacher, $class_coordinator, $hod);

if ($stmt->execute()) {
    echo "<script>alert('Gate Pass Request Submitted Successfully!'); window.location.href='student-dashboard.php?student_id=$student_id';</script>";
    exit;
} else {
    die("Database error: " . $stmt->error); // Show the error if insertion fails
}

$stmt->close();
$conn->close();
?>
