<?php
include('connection.php');

if (!isset($_GET['student_id'])) {
    echo "Student ID not provided!";
    exit();
}

$student_id = intval($_GET['student_id']);

$query = "SELECT * FROM student_registration WHERE student_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student_data = $student_result->fetch_assoc();

$gp_query = "SELECT * FROM gate_pass_requests WHERE student_id = ? ORDER BY student_id DESC";
$gp_stmt = $conn->prepare($gp_query);
$gp_stmt->bind_param("i", $student_id);
$gp_stmt->execute();
$gp_result = $gp_stmt->get_result();

$past_query = "SELECT * FROM gate_pass_requests WHERE student_id = ? AND status = 1 ORDER BY student_id DESC";
$past_stmt = $conn->prepare($past_query);
$past_stmt->bind_param("i", $student_id);
$past_stmt->execute();
$past_result = $past_stmt->get_result();

function renderStageStatus($status, $approved_by) {
    if ($status == 0) {
        return '<span class="badge bg-warning text-dark">Pending</span>';
    } elseif ($status == 1) {
        return '<span class="badge bg-success">Approved by ' . htmlspecialchars($approved_by) . '</span>';
    } elseif ($status == 2) {
        return '<span class="badge bg-danger">Rejected by ' . htmlspecialchars($approved_by) . '</span>';
    } else {
        return '<span class="badge bg-secondary">Unknown</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; background-color: #007bff; color: white; padding: 20px; position: fixed; top: 0; left: 0; overflow-y: auto; }
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
            background: #007bff;
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
            .btn { width: 100%; margin-bottom: 6px; }
            table { font-size: 0.85rem; }
        }
    </style>
</head>
<body>

    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar">
        <h4>Student Dashboard</h4>
        <a href="#applyPass" onclick="showSection('applyPass')">Apply for Gate Pass</a>
        <a href="#viewStatus" onclick="showSection('viewStatus')">View Status</a>
        <a href="#pastRequests" onclick="showSection('pastRequests')">Past Requests</a>
        <a href="#profileSettings" onclick="showSection('profileSettings')">Profile Settings</a>
        <a href="index.php">Logout</a>
    </div>

    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($student_data['full_name'] ?? 'Student'); ?>!</h2>

        <!-- Apply for Gate Pass -->
        <div id="applyPass" class="card p-4">
            <h4>Apply for Gate Pass</h4>
            <form action="student_apply_gp.php" method="POST">
                <div class="mb-3">
                    <label for="year" class="form-label">Select Year</label>
                    <select class="form-control" id="year" name="year" required>
                        <option value="" selected disabled>Select Year</option>
                        <option value="Second Year">Second Year</option>
                        <option value="Third Year">Third Year</option>
                        <option value="Fourth Year">Fourth Year</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="teacher" class="form-label">Teacher Guardian</label>
                    <select class="form-control" id="teacher" name="teacher" required>
                        <option value="" selected disabled>Select Teacher Guardian</option>
                        <option value="Prof.U.S.Gatkul">Prof.U.S.Gatkul</option>
                        <option value="Prof.A.M.Gunje">Prof.A.M.Gunje</option>
                        <option value="Prof.A.D.Ruikar">Prof.A.D.Ruikar</option>
                        <option value="Prof.A.G.Gund">Prof.A.G.Gund</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="coordinator" class="form-label">Class Coordinator</label>
                    <select class="form-control" id="coordinator" name="classcordinator" required>
                        <option value="" selected disabled>Select Class Coordinator</option>
                        <option value="Prof.U.S.Gatkul">Prof.U.S.Gatkul</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="hod" class="form-label">Head of Department</label>
                    <input type="text" class="form-control" id="hod" name="hod" value="Prof.Harish Gurme" readonly>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">Reason for Exit</label>
                    <input type="text" class="form-control" id="reason" name="reason" required>
                </div>
                <button type="submit" id="submitBtn" class="btn btn-primary">Submit Request</button>
            </form>
        </div>

        <!-- View Status of Requests -->
        <div id="viewStatus" class="card p-4 d-none">
            <h4>View Status of Requests</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Reason</th>
                            <th>Teacher Guardian</th>
                            <th>Class Coordinator</th>
                            <th>HOD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $gp_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo renderStageStatus($row['tg_status'], $row['tg_approved_by']); ?></td>
                                <td><?php echo renderStageStatus($row['cc_status'], $row['cc_approved_by']); ?></td>
                                <td><?php echo renderStageStatus($row['hod_status'], $row['hod_approved_by']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Past Requests -->
        <div id="pastRequests" class="card p-4 d-none">
            <h4>Past Requests (Fully Approved)</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Reason</th><th>Teacher Guardian</th><th>Class Coordinator</th><th>HOD</th></tr></thead>
                    <tbody>
                        <?php while ($row = $past_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo renderStageStatus($row['tg_status'], $row['tg_approved_by']); ?></td>
                            <td><?php echo renderStageStatus($row['cc_status'], $row['cc_approved_by']); ?></td>
                            <td><?php echo renderStageStatus($row['hod_status'], $row['hod_approved_by']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile Settings -->
        <div id="profileSettings" class="card p-4 d-none">
            <h4>Profile Settings</h4>
            <form action="update_student_profile.php" method="POST">
                <div class="mb-3">
                    <label for="studentName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="studentName" name="name" value="<?php echo htmlspecialchars($student_data['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student_data['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
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

        document.querySelector('#applyPass form').addEventListener('submit', function() {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Submitting...';
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$gp_stmt->close();
$past_stmt->close();
$conn->close();
?>