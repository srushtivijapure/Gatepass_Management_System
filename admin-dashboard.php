<?php
// Include the database connection
include('connection.php');

// Fetch student records
$student_query = "SELECT * FROM student_registration";
$student_result = $conn->query($student_query);

// Fetch faculty records
$faculty_query = "SELECT * FROM faculty_registration";
$faculty_result = $conn->query($faculty_query);

// Fetch request logs
$request_query = "SELECT r.student_id, r.reason, r.status, s.full_name 
                  FROM gate_pass_requests r
                  JOIN student_registration s ON r.student_id = s.student_id";
$request_result = $conn->query($request_query);

// Fetch system analytics
$total_requests_query = "SELECT COUNT(*) FROM gate_pass_requests";
$total_requests_result = $conn->query($total_requests_query);
$total_requests = $total_requests_result->fetch_row()[0];

$approved_requests_query = "SELECT COUNT(*) FROM gate_pass_requests WHERE status = '1'";
$approved_requests_result = $conn->query($approved_requests_query);
$approved_requests = $approved_requests_result->fetch_row()[0];

$rejected_requests_query = "SELECT COUNT(*) FROM gate_pass_requests WHERE status = '0'";
$rejected_requests_result = $conn->query($rejected_requests_query);
$rejected_requests = $rejected_requests_result->fetch_row()[0];

$conn->close(); // Close the DB connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; background-color: #343a40; color: white; padding: 20px; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.2); }
        .main-content { margin-left: 270px; padding: 20px; }
        .card { box-shadow: 0px 4px 8px rgba(0,0,0,0.1); margin-top: 20px; }
        .d-none { display: none; }

        .menu-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: #343a40;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 20px;
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1040;
        }
        .sidebar-overlay.active { display: block; }

        @media (max-width: 768px) {
            .menu-toggle { display: block; }
            .sidebar { left: -260px; width: 220px; transition: left 0.3s ease; z-index: 1050; }
            .sidebar.active { left: 0; }
            .main-content { margin-left: 0; padding: 70px 12px 20px 12px; }
            h2 { font-size: 1.4rem; }
            h4 { font-size: 1.1rem; }
            .card { padding: 1rem !important; }
            table { font-size: 0.85rem; }
        }
    </style>
</head>
<body>

    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar">
        <h4>Admin Dashboard</h4>
        <a href="#studentRecords" onclick="showSection('studentRecords')">Student Records</a>
        <a href="#facultyRecords" onclick="showSection('facultyRecords')">Faculty Records</a>
        <a href="#requestLogs" onclick="showSection('requestLogs')">Request Logs</a>
        <a href="#analytics" onclick="showSection('analytics')">System Analytics</a>
        <a href="#logout" onclick="logout()">Logout</a>
    </div>

    <div class="main-content">
        <h2>Welcome, Admin</h2>
        
        <!-- Student Records -->
        <div id="studentRecords" class="card p-4">
            <h4>Student Records</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Name</th><th>Studying Year</th><th>Email</th></tr></thead>
                    <tbody>
                        <?php while ($row = $student_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['studying_year']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Faculty Records -->
        <div id="facultyRecords" class="card p-4 d-none">
            <h4>Faculty Records</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Name</th><th>Department</th><th>Email</th></tr></thead>
                    <tbody>
                        <?php while ($row = $faculty_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['faculty_fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['faculty_dept']); ?></td>
                            <td><?php echo htmlspecialchars($row['faculty_email']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Request Logs -->
        <div id="requestLogs" class="card p-4 d-none">
            <h4>Request Logs</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Student Name</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php while ($row = $request_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo $row['status'] == 1 ? 'Approved' : 'Rejected'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Analytics -->
        <div id="analytics" class="card p-4 d-none">
            <h4>System Analytics</h4>
            <p>Total Requests: <span id="totalRequests"><?php echo $total_requests; ?></span></p>
            <p>Approved Requests: <span id="approvedRequests"><?php echo $approved_requests; ?></span></p>
            <p>Rejected Requests: <span id="rejectedRequests"><?php echo $rejected_requests; ?></span></p>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.card').forEach(card => card.classList.add('d-none'));
            document.getElementById(sectionId).classList.remove('d-none');
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.sidebar-overlay').classList.remove('active');
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
        
        function logout() {
            alert('Logging out...');
            window.location.href = 'index.php';  
        }
    </script>

</body>
</html>