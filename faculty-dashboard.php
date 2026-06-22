<?php
session_start();
if (!isset($_SESSION['faculty_id'])) {
   //header("Location: loginpage.php");
    //exit();
}
include('connection.php');

$faculty_id = intval($_SESSION['faculty_id']);
$query = "SELECT faculty_fullname AS name, faculty_email AS email FROM faculty_registration WHERE faculty_id = $faculty_id";
$result = mysqli_query($conn, $query);
$faculty = mysqli_fetch_assoc($result);
$faculty_name = trim($faculty['name'] ?? '');
$faculty_name_esc = mysqli_real_escape_string($conn, $faculty_name);

function renderFacultyStage($status, $approved_by) {
    if ($status == 0) {
        return '<span class="badge bg-warning text-dark">Pending</span>';
    } elseif ($status == 1) {
        return '<span class="badge bg-success">Approved by ' . htmlspecialchars($approved_by ?? '') . '</span>';
    } elseif ($status == 2) {
        return '<span class="badge bg-danger">Rejected by ' . htmlspecialchars($approved_by ?? '') . '</span>';
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
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { height: 100vh; width: 250px; background-color: #007bff; color: white; padding: 20px; position: fixed; top: 0; left: 0; overflow-y: auto; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; }
        .sidebar a:hover { background-color: rgba(255, 255, 255, 0.2); }
        .main-content { margin-left: 270px; padding: 40px 20px; }
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
            table { font-size: 0.85rem; }
        }
    </style>
</head>
<body>
    <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="sidebar">
        <h4>Faculty Dashboard</h4>
        <a href="#pendingRequests" onclick="showSection('pendingRequests')">View Pending Requests</a>
        <a href="#approvedRequests" onclick="showSection('approvedRequests')">Approved Requests</a>
        <a href="#rejectedRequests" onclick="showSection('rejectedRequests')">Rejected Requests</a>
        <a href="#profile" onclick="showSection('profile')">Profile</a>
        <a href="index.php" onclick="logout()">Logout</a>
    </div>

    <div class="main-content">
        <h2>Welcome, <?php echo htmlspecialchars($faculty_name ?: 'Faculty Member'); ?></h2>
        <div id="messageBox" class="alert d-none"></div>

        <!-- Pending Requests -->
        <div id="pendingRequests" class="card p-4">
            <h4>Pending Requests (Your Action Needed)</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Student Name</th><th>Year</th><th>Reason</th><th>Your Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php
                        $query = "SELECT gp.request_id AS id, s.full_name, gp.year, gp.reason,
                                         gp.teacher, gp.class_coordinator, gp.hod,
                                         gp.tg_status, gp.cc_status, gp.hod_status
                                  FROM gate_pass_requests gp
                                  JOIN student_registration s ON gp.student_id = s.student_id
                                  WHERE gp.tg_status != 2 AND gp.cc_status != 2 AND gp.hod_status != 2";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            if ($row['tg_status'] == 0) {
                                $current_stage = 'tg';
                                $responsible_name = trim($row['teacher']);
                            } elseif ($row['cc_status'] == 0) {
                                $current_stage = 'cc';
                                $responsible_name = trim($row['class_coordinator']);
                            } elseif ($row['hod_status'] == 0) {
                                $current_stage = 'hod';
                                $responsible_name = trim($row['hod']);
                            } else {
                                continue;
                            }

                            if ($responsible_name !== $faculty_name) {
                                continue;
                            }

                            $stage_label = strtoupper($current_stage);
                            echo "<tr>
                                    <td>{$row['full_name']}</td>
                                    <td>{$row['year']}</td>
                                    <td>{$row['reason']}</td>
                                    <td>{$stage_label}</td>
                                    <td>
                                        <div class='d-flex flex-wrap gap-1'>
                                            <button class='btn btn-success btn-sm' onclick='updateRequest({$row['id']}, \"approved\", \"$current_stage\")'>Approve</button>
                                            <button class='btn btn-danger btn-sm' onclick='updateRequest({$row['id']}, \"rejected\", \"$current_stage\")'>Reject</button>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Approved Requests -->
        <div id="approvedRequests" class="card p-4 d-none">
            <h4>Approved Requests</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Student Name</th><th>Year</th><th>Reason</th><th>TG</th><th>CC</th><th>HOD</th></tr></thead>
                    <tbody>
                        <?php
                        $query = "SELECT gp.request_id AS id, s.full_name, gp.year, gp.reason,
                                         gp.tg_status, gp.cc_status, gp.hod_status,
                                         gp.tg_approved_by, gp.cc_approved_by, gp.hod_approved_by
                                  FROM gate_pass_requests gp
                                  JOIN student_registration s ON gp.student_id = s.student_id
                                  WHERE (gp.teacher = '$faculty_name_esc' AND gp.tg_status = 1)
                                     OR (gp.class_coordinator = '$faculty_name_esc' AND gp.cc_status = 1)
                                     OR (gp.hod = '$faculty_name_esc' AND gp.status = 1)";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $tg = renderFacultyStage($row['tg_status'], $row['tg_approved_by']);
                            $cc = renderFacultyStage($row['cc_status'], $row['cc_approved_by']);
                            $hod = renderFacultyStage($row['hod_status'], $row['hod_approved_by']);
                            echo "<tr>
                                    <td>{$row['full_name']}</td>
                                    <td>{$row['year']}</td>
                                    <td>{$row['reason']}</td>
                                    <td>$tg</td>
                                    <td>$cc</td>
                                    <td>$hod</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rejected Requests -->
        <div id="rejectedRequests" class="card p-4 d-none">
            <h4>Rejected Requests</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Student Name</th><th>Year</th><th>Reason</th><th>TG</th><th>CC</th><th>HOD</th></tr></thead>
                    <tbody>
                        <?php
                        $query = "SELECT gp.request_id AS id, s.full_name, gp.year, gp.reason,
                                         gp.tg_status, gp.cc_status, gp.hod_status,
                                         gp.tg_approved_by, gp.cc_approved_by, gp.hod_approved_by
                                  FROM gate_pass_requests gp
                                  JOIN student_registration s ON gp.student_id = s.student_id
                                  WHERE (gp.teacher = '$faculty_name_esc' AND gp.tg_status = 2)
                                     OR (gp.class_coordinator = '$faculty_name_esc' AND gp.cc_status = 2)
                                     OR (gp.hod = '$faculty_name_esc' AND gp.status = 2)";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $tg = renderFacultyStage($row['tg_status'], $row['tg_approved_by']);
                            $cc = renderFacultyStage($row['cc_status'], $row['cc_approved_by']);
                            $hod = renderFacultyStage($row['hod_status'], $row['hod_approved_by']);
                            echo "<tr>
                                    <td>{$row['full_name']}</td>
                                    <td>{$row['year']}</td>
                                    <td>{$row['reason']}</td>
                                    <td>$tg</td>
                                    <td>$cc</td>
                                    <td>$hod</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile -->
        <div id="profile" class="card p-4 d-none">
            <h4>Profile</h4>
            <form action="update_profile.php" method="POST">
                <div class="mb-3">
                    <label for="facultyName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="facultyName" name="name" value="<?php echo htmlspecialchars($faculty['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($faculty['email'] ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <?php mysqli_close($conn); ?>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.card').forEach(card => card.classList.add('d-none'));
            const section = document.getElementById(sectionId);
            if (section) {
                section.classList.remove('d-none');
            }
            document.querySelectorAll('.sidebar a').forEach(link => link.classList.remove('active'));
            const activeLink = document.querySelector(`.sidebar a[href="#${sectionId}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
            document.querySelector('.sidebar').classList.remove('active');
            document.querySelector('.sidebar-overlay').classList.remove('active');
        }

        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }

        function updateRequest(requestId, status, stage) {
            let formData = new FormData();
            formData.append("request_id", requestId);
            formData.append("status", status);
            formData.append("stage", stage);

            fetch("faculty_approve.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "success") {
                    showMessage("Request " + status + " successfully!", "success");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage("Error: " + data, "danger");
                }
            })
            .catch(error => {
                showMessage("Error: " + error, "danger");
            });
        }

        function logout() {
            window.location.href = "index.php";
        }

        function showMessage(msg, type) {
            const box = document.getElementById('messageBox');
            box.className = `alert alert-${type}`;
            box.textContent = msg;
            box.classList.remove('d-none');
            setTimeout(() => box.classList.add('d-none'), 3000);
        }

        window.addEventListener('hashchange', () => {
            const section = window.location.hash.substring(1);
            showSection(section);
        });

        window.addEventListener('DOMContentLoaded', () => {
            const section = window.location.hash.substring(1) || 'pendingRequests';
            showSection(section);
        });
    </script>
</body>
</html>