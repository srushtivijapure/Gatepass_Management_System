<?php
session_start();
include('connection.php');

if (!isset($_SESSION['faculty_id'])) {
    echo "Not logged in";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);
    $status = $_POST['status']; // 'approved' or 'rejected'
    $stage = $_POST['stage'];   // 'tg', 'cc', or 'hod'

    // Get the logged-in faculty's real name, so we record WHO acted
    $faculty_id = intval($_SESSION['faculty_id']);
    $fname_query = "SELECT faculty_fullname FROM faculty_registration WHERE faculty_id = $faculty_id";
    $fname_result = mysqli_query($conn, $fname_query);
    $fname_row = mysqli_fetch_assoc($fname_result);
    $faculty_name = mysqli_real_escape_string($conn, $fname_row['faculty_fullname'] ?? 'Unknown');

    // Only allow these 3 known stages
    $valid_stages = ['tg', 'cc', 'hod'];
    if (!in_array($stage, $valid_stages)) {
        echo "Invalid stage";
        exit;
    }

    $status_column = $stage . "_status";
    $approved_by_column = $stage . "_approved_by";

    if ($status === 'approved') {
        $new_value = 1;
    } elseif ($status === 'rejected') {
        $new_value = 2;
    } else {
        echo "Invalid status";
        exit;
    }

    // Update that specific stage's status and who acted on it
    $update_query = "UPDATE gate_pass_requests 
                      SET $status_column = $new_value, $approved_by_column = '$faculty_name' 
                      WHERE request_id = $request_id";

    if (!mysqli_query($conn, $update_query)) {
        echo "Error updating stage: " . mysqli_error($conn);
        exit;
    }

    // Recalculate the OVERALL status from all 3 stages
    $check_query = "SELECT tg_status, cc_status, hod_status FROM gate_pass_requests WHERE request_id = $request_id";
    $check_result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($check_result);

    if ($row['tg_status'] == 2 || $row['cc_status'] == 2 || $row['hod_status'] == 2) {
        $overall_status = 2; // rejected at some stage
    } elseif ($row['tg_status'] == 1 && $row['cc_status'] == 1 && $row['hod_status'] == 1) {
        $overall_status = 1; // all 3 stages approved
    } else {
        $overall_status = 0; // still pending somewhere
    }

    mysqli_query($conn, "UPDATE gate_pass_requests SET status = $overall_status WHERE request_id = $request_id");

    echo "success";
    mysqli_close($conn);
} else {
    echo "Invalid request";
}
?>