<?php
session_start();
include('connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Gate Pass Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            background-color: #eef2f3;
            overflow-x: hidden;
        }
        .navbar { background-color: #007bff; }
        .navbar-brand {
            font-size: 15px;
            white-space: normal;
            line-height: 1.3;
            max-width: 75%;
        }
        .container { max-width: 800px; }
        .card {
            margin-top: 30px;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .btn { border-radius: 6px; }

        .login-role-buttons {
            display: flex;
            gap: 10px;
        }
        .login-role-buttons .btn {
            flex: 1;
        }
        @media (max-width: 480px) {
            .login-role-buttons {
                flex-direction: column;
            }
            .login-role-buttons .btn {
                width: 100%;
            }
            .navbar-brand {
                font-size: 13px;
                max-width: 65%;
            }
            .card {
                margin-top: 15px;
                padding: 1rem !important;
            }
            h3 { font-size: 1.25rem; }
        }

        .auth-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 6px 10px;
            font-size: 0.95rem;
        }

        #contact {
            padding-bottom: 40px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand text-wrap" href="index.php">
                N B Navale College of Engineering, Solapur
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div class="container mt-3"><div class="alert alert-danger text-center">'
            . htmlspecialchars($_SESSION['error']) .
            '</div></div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['register_success'])) {
        echo '<div class="container mt-3"><div class="alert alert-success text-center">'
            . htmlspecialchars($_SESSION['register_success']) .
            '</div></div>';
        unset($_SESSION['register_success']);
    }
    if (isset($_SESSION['register_error'])) {
        echo '<div class="container mt-3"><div class="alert alert-danger text-center">'
            . htmlspecialchars($_SESSION['register_error']) .
            '</div></div>';
        unset($_SESSION['register_error']);
    }
    if (isset($_SESSION['reset_message'])) {
        echo '<div class="container mt-3"><div class="alert alert-' . htmlspecialchars($_SESSION['reset_message_type'] ?? 'info') . ' text-center">'
            . htmlspecialchars($_SESSION['reset_message']) .
            '</div></div>';
        unset($_SESSION['reset_message']);
        unset($_SESSION['reset_message_type']);
    }
    $show_form = $_SESSION['show_form'] ?? null;
    unset($_SESSION['show_form']);
    ?>

    <!-- Login Page -->
    <div class="container d-flex justify-content-center" id="loginSection">
        <div class="card p-4 w-100" style="max-width: 400px;">
            <h3 class="text-center mb-3">Login</h3>
            <form id="loginForm" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <input type="hidden" name="user_type" id="userType" value="student">
                <div class="login-role-buttons mb-3">
                    <button type="submit" class="btn btn-primary" onclick="UserType('student')">Login as Student</button>
                    <button type="submit" class="btn btn-secondary" onclick="UserType('faculty')">Login as Faculty</button>
                </div>
                <div class="auth-links">
                    <a href="#" onclick="showForgotPassword(); return false;" class="text-decoration-none">Forgot Password?</a>
                    <span>|</span>
                    <a href="#" onclick="showRegisterOptions(); return false;" class="text-decoration-none">Register</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Options -->
    <div class="container d-flex justify-content-center mt-4 d-none" id="registerOptions">
        <div class="card p-4 text-center w-100" style="max-width: 400px;">
            <h3>Select Registration Type</h3>
            <button class="btn btn-primary w-100 mt-3" onclick="showRegisterForm('student')">Register as Student</button>
            <button class="btn btn-secondary w-100 mt-3" onclick="showRegisterForm('faculty')">Register as Faculty</button>
            <button class="btn btn-link w-100 mt-2" onclick="backToLogin()">Back to Login</button>
        </div>
    </div>

    <!-- Student Register Page -->
    <div class="container d-flex justify-content-center mt-4 d-none" id="studentRegister">
        <div class="card p-4 w-100" style="max-width: 400px;">
            <h3 class="text-center">Student Registration</h3>
            <form action="student_registration.php" method="POST">
                <div class="mb-3">
                    <label for="enrollment_no" class="form-label">Enrollment No</label>
                    <input type="text" class="form-control" id="enrollment_no" name="enrollment_no" required>
                </div>
                <div class="mb-3">
                    <label for="studentName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="studentName" name="studentname" required>
                </div>
                <div class="mb-3">
                    <label for="studentEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="studentEmail" name="studentemail" required>
                </div>
                <div class="mb-3">
                    <label for="studentPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="studentPassword" name="studentpassword" minlength="6" required>
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="studentDept" class="form-label">Department</label>
                    <input type="text" class="form-control" id="studentDept" name="dept" required>
                </div>
                <div class="mb-3">
                    <label for="studentYear" class="form-label">Studying Year</label>
                    <select class="form-control" id="studentYear" name="year" required>
                        <option value="" selected disabled>Select Year</option>
                        <option value="First Year">First Year</option>
                        <option value="Second Year">Second Year</option>
                        <option value="Third Year">Third Year</option>
                        <option value="Fourth Year">Fourth Year</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100" name="submit">Register</button>
                <button type="button" class="btn btn-link w-100 mt-2" onclick="showRegisterOptions()">Back</button>
            </form>
        </div>
    </div>

    <!-- Faculty Register Page -->
    <div class="container d-flex justify-content-center mt-4 d-none" id="facultyRegister">
        <div class="card p-4 w-100" style="max-width: 400px;">
            <h3 class="text-center">Faculty Registration</h3>
            <form action="faculty_registration.php" method="POST">
                <div class="mb-3">
                    <label for="facultyName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="facultyName" name="fullname" required>
                </div>
                <div class="mb-3">
                    <label for="facultyEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="facultyEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="facultyPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="facultyPassword" name="password" minlength="6" required>
                    <div class="form-text">At least 6 characters.</div>
                </div>
                <div class="mb-3">
                    <label for="facultyDept" class="form-label">Department</label>
                    <input type="text" class="form-control" id="facultyDept" name="dept" required>
                </div>
                <div class="mb-3">
                    <label for="facultyTGBatch" class="form-label">TG Batch</label>
                    <input type="text" class="form-control" id="facultyTGBatch" name="tgbatch" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Register</button>
                <button type="button" class="btn btn-link w-100 mt-2" onclick="showRegisterOptions()">Back</button>
            </form>
        </div>
    </div>

    <!-- Forgot Password: Step 1 - verify identity -->
    <div class="container d-flex justify-content-center mt-4 d-none" id="forgotPasswordStep1">
        <div class="card p-4 w-100" style="max-width: 400px;">
            <h3 class="text-center">Forgot Password</h3>
            <p class="text-muted text-center" style="font-size: 0.9rem;">Enter your registered email and department to verify your account.</p>
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="action" value="verify">
                <div class="mb-3">
                    <label for="fpEmail" class="form-label">Registered Email</label>
                    <input type="email" class="form-control" id="fpEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="fpDept" class="form-label">Department</label>
                    <input type="text" class="form-control" id="fpDept" name="department" required>
                    <div class="form-text">Must match the department on your account.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify &amp; Continue</button>
                <button type="button" class="btn btn-link w-100 mt-2" onclick="backToLogin()">Back to Login</button>
            </form>
        </div>
    </div>

    <!-- Forgot Password: Step 2 - set new password (shown after verification) -->
    <?php if (isset($_SESSION['reset_verified_user_id'])): ?>
    <div class="container d-flex justify-content-center mt-4" id="forgotPasswordStep2">
        <div class="card p-4 w-100" style="max-width: 400px;">
            <h3 class="text-center">Set New Password</h3>
            <p class="text-muted text-center" style="font-size: 0.9rem;">Identity verified. Choose a new password.</p>
            <form action="forgot_password.php" method="POST">
                <input type="hidden" name="action" value="reset">
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" minlength="6" required>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" minlength="6" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Update Password</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="container mt-5" id="contact">
        <h2>Contact Us</h2>
        <p>If you have any queries, reach out to us at:</p>
        <p>Email: support@college.com</p>
        <p>Phone: +91 9876543210</p>
    </div>

    <script>
        function hideAllSections() {
            document.getElementById('loginSection').classList.add('d-none');
            document.getElementById('registerOptions').classList.add('d-none');
            document.getElementById('studentRegister').classList.add('d-none');
            document.getElementById('facultyRegister').classList.add('d-none');
            document.getElementById('forgotPasswordStep1').classList.add('d-none');
        }

        function backToLogin() {
            hideAllSections();
            document.getElementById('loginSection').classList.remove('d-none');
        }

        function showRegisterOptions() {
            hideAllSections();
            document.getElementById('registerOptions').classList.remove('d-none');
        }

        function showRegisterForm(type) {
            hideAllSections();
            if (type === 'student') {
                document.getElementById('studentRegister').classList.remove('d-none');
            } else {
                document.getElementById('facultyRegister').classList.remove('d-none');
            }
        }

        function showForgotPassword() {
            hideAllSections();
            document.getElementById('forgotPasswordStep1').classList.remove('d-none');
        }

        function UserType(type) {
            document.getElementById("userType").value = type;
            document.getElementById("loginForm").action = type === "student" ? "student_login.php" : "faculty_login.php";
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById("loginForm").action = "student_login.php";

            const showForm = <?php echo json_encode($show_form); ?>;
            if (showForm === 'student') {
                showRegisterForm('student');
            } else if (showForm === 'faculty') {
                showRegisterForm('faculty');
            }
            <?php if (isset($_SESSION['reset_verified_user_id'])): ?>
            hideAllSections();
            const step2 = document.getElementById('forgotPasswordStep2');
            if (step2) { step2.scrollIntoView({behavior: 'smooth'}); }
            <?php endif; ?>
        });
    </script>
</body>
</html>
