-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql208.infinityfree.com
-- Generation Time: Jun 26, 2026 at 07:54 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42231043_gatepass_db_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `admin_id`, `action`, `created_at`) VALUES
(1, 5, 'Approved gate pass request ID 1', '2025-05-03 06:31:11'),
(2, 5, 'Rejected gate pass request ID 2', '2025-05-03 06:31:11');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_approvals`
--

CREATE TABLE `faculty_approvals` (
  `approval_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `approval_status` tinyint(1) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_approvals`
--

INSERT INTO `faculty_approvals` (`approval_id`, `request_id`, `faculty_id`, `approval_status`, `comments`, `approved_at`) VALUES
(1, 1, 1, 1, 'Approved. Take care.', '2025-05-03 06:31:11'),
(2, 2, 2, 0, 'Conference not permitted at this time.', '2025-05-03 06:31:11');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_registration`
--

CREATE TABLE `faculty_registration` (
  `faculty_id` int(11) NOT NULL,
  `faculty_fullname` varchar(255) NOT NULL,
  `faculty_email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `faculty_dept` varchar(100) NOT NULL,
  `faculty_tgbatch` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_registration`
--

INSERT INTO `faculty_registration` (`faculty_id`, `faculty_fullname`, `faculty_email`, `password`, `faculty_dept`, `faculty_tgbatch`, `created_at`) VALUES
(1, 'Prof. Mehta', 'mehta@wit.edu', 'mehta123', 'IT', 'Batch A', '2025-05-03 06:31:11'),
(2, 'Dr. Joshi', 'joshi@wit.edu', 'joshi456', 'CSE', 'Batch B', '2025-05-03 06:31:11'),
(10, 'prof. vinayak .V.palmur', 'vvpalmur@gmail.com', '$2y$10$4XeFWIUBKCM3QJqdq1SbXuWa6DBcxJua4kvZU95f/TBDfJJLAGSjS', 'cse', 'T4', '2026-06-20 14:41:33'),
(11, 'Prof.A.G.Gund', 'amrapali@gmail.com', '$2y$10$M25hM53.HjcS06trqW7bPOzAIjxde0NMmnBwWG9xfX6kQPwALcwQ.', 'cse', 'T2', '2026-06-20 15:48:27'),
(12, 'Prof.U.S.Gatkul', 'urmila@gmail.com', '$2y$10$sn/deLq/4kY84bHhrWbB9esOZNdFUWwaxuyrRuNkxgHYbD8q9i.16', 'cse', 'T4', '2026-06-20 15:52:57'),
(13, 'Prof.Harish Gurme', 'harish@gmail.com', '$2y$10$DSqrNY2nbqH48t1ystobRuZmPXhXg4WJ6aiNrZk/NDaWxurPPkpOC', 'cse', '-', '2026-06-20 15:56:23');

-- --------------------------------------------------------

--
-- Table structure for table `gate_pass_requests`
--

CREATE TABLE `gate_pass_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  `teacher` varchar(100) DEFAULT NULL,
  `class_coordinator` varchar(100) DEFAULT NULL,
  `hod` varchar(100) DEFAULT NULL,
  `comments` varchar(256) DEFAULT NULL,
  `tg_status` tinyint(1) NOT NULL DEFAULT 0,
  `cc_status` tinyint(1) NOT NULL DEFAULT 0,
  `hod_status` tinyint(1) NOT NULL DEFAULT 0,
  `tg_approved_by` varchar(100) DEFAULT NULL,
  `cc_approved_by` varchar(100) DEFAULT NULL,
  `hod_approved_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gate_pass_requests`
--

INSERT INTO `gate_pass_requests` (`request_id`, `student_id`, `reason`, `status`, `year`, `teacher`, `class_coordinator`, `hod`, `comments`, `tg_status`, `cc_status`, `hod_status`, `tg_approved_by`, `cc_approved_by`, `hod_approved_by`) VALUES
(1, 1, 'Medical emergency at home.', 1, 'Second Year', 'Prof. Mehta', 'Prof. Mehta', 'Dr. Joshi', '', 0, 0, 0, NULL, NULL, NULL),
(2, 2, 'Attending a conference.', 2, 'Third Year', 'Dr. Joshi', 'Dr. Joshi', 'Dr. Joshi', '', 0, 0, 0, NULL, NULL, NULL),
(3, 7, 'birthday', 1, 'Fourth Year', 'Prof.A.M.Gunje', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme', '', 0, 0, 0, NULL, NULL, NULL),
(9, 7, 'meetup @ GOVP', 0, 'Fourth Year', 'Prof.A.M.Gunje', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme', NULL, 0, 0, 0, NULL, NULL, NULL),
(10, 7, 'meetup @ GOVP', 2, 'Second Year', 'Prof.A.G.Gund', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme', NULL, 1, 1, 2, 'Prof.A.G.Gund', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme'),
(11, 14, 'meetup @ NBNSCOE', 2, 'Fourth Year', 'Prof.A.G.Gund', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme', NULL, 2, 0, 0, 'Prof.A.G.Gund', NULL, NULL),
(12, 15, 'Sswcoe 🪚', 1, 'Third Year', 'Prof.A.G.Gund', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme', NULL, 1, 1, 1, 'Prof.A.G.Gund', 'Prof.U.S.Gatkul', 'Prof.Harish Gurme');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `enrollment_no` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `password`, `enrollment_no`) VALUES
(1, 'nandini12', 'pass123', 'ENR2025001'),
(2, 'rahulp', 'rahul@321', 'ENR2025002');

-- --------------------------------------------------------

--
-- Table structure for table `student_registration`
--

CREATE TABLE `student_registration` (
  `enrollment_no` varchar(20) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(50) NOT NULL,
  `studying_year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_registration`
--

INSERT INTO `student_registration` (`enrollment_no`, `full_name`, `email`, `password`, `department`, `studying_year`, `created_at`, `student_id`) VALUES
('en12121212', 'Shreya v', 'shreyavijapure@gmail.com', '$2y$10$Q9t7xiPCWtZOn7KdLR7ip.5vQDz7uBdnj6yo2RlrUIKapL/tM4Uwq', 'mba', 0, '2026-06-20 12:59:26', 7),
('en12345123', 'aditya', 'aditya@gmai.com', '$2y$10$9d8c86ABYjRJ6dDDgE3VDeBUo19xccQ1uVWHw9lT6OnWS2mXFTglO', 'mech', 0, '2026-06-20 15:58:35', 14),
('en14032005', 'Harshada', 'harshada@gmail.com', '$2y$10$sV94PEzJVVSAh0Rk/hMOd.LVn7Uc4i1W9IF5bEVTBlbLjvtWeaJ/e', 'Cse', 0, '2026-06-22 12:05:02', 15),
('en24221101', 'srushti deepak vijapure', 'srushtivijapure8@gmail.com', '$2y$10$Xp/YJMDnDcR5C2RVp0GUneUj3uqFeXJN0Y7ySp4IMUEkubc/B2o5K', 'cse', 0, '2026-06-20 11:27:07', 6),
('ENR2025001', 'Nandini Sharma', 'nandini@example.com', 'pass123', 'IT', 2, '2025-05-03 06:31:11', 1),
('ENR2025002', 'Rahul Patil', 'rahul@example.com', 'rahul@321', 'CSE', 3, '2025-05-03 06:31:11', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','faculty','admin') NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `year` enum('First Year','Second Year','Third Year','Fourth Year') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `department`, `year`, `created_at`) VALUES
(1, 'Nandini Sharma', 'nandini@example.com', 'pass123', 'student', 'IT', 'Second Year', '2025-05-03 06:31:11'),
(2, 'Rahul Patil', 'rahul@example.com', 'rahul@321', 'student', 'CSE', 'Third Year', '2025-05-03 06:31:11'),
(3, 'Prof. Mehta', 'mehta@wit.edu', 'mehta123', 'faculty', 'IT', NULL, '2025-05-03 06:31:11'),
(4, 'Dr. Joshi', 'joshi@wit.edu', 'joshi456', 'faculty', 'CSE', NULL, '2025-05-03 06:31:11'),
(5, 'Admin User', 'admin@wit.edu', 'adminpass', 'admin', NULL, NULL, '2025-05-03 06:31:11'),
(6, 'srushti deepak vijapure', 'srushtivijapure8@gmail.com', '$2y$10$Xp/YJMDnDcR5C2RVp0GUneUj3uqFeXJN0Y7ySp4IMUEkubc/B2o5K', 'student', 'cse', '', '2026-06-20 11:27:07'),
(7, 'Shreya v', 'shreyavijapure@gmail.com', '$2y$10$Q9t7xiPCWtZOn7KdLR7ip.5vQDz7uBdnj6yo2RlrUIKapL/tM4Uwq', 'student', 'mba', '', '2026-06-20 12:59:26'),
(10, 'prof. vinayak .V.palmur', 'vvpalmur@gmail.com', '$2y$10$4XeFWIUBKCM3QJqdq1SbXuWa6DBcxJua4kvZU95f/TBDfJJLAGSjS', 'faculty', 'cse', NULL, '2026-06-20 14:41:33'),
(11, 'Prof.A.G.Gund', 'amrapali@gmail.com', '$2y$10$M25hM53.HjcS06trqW7bPOzAIjxde0NMmnBwWG9xfX6kQPwALcwQ.', 'faculty', 'cse', NULL, '2026-06-20 15:48:27'),
(12, 'Prof.U.S.Gatkul', 'urmila@gmail.com', '$2y$10$sn/deLq/4kY84bHhrWbB9esOZNdFUWwaxuyrRuNkxgHYbD8q9i.16', 'faculty', 'cse', NULL, '2026-06-20 15:52:57'),
(13, 'Prof.Harish Gurme', 'harish@gmail.com', '$2y$10$DSqrNY2nbqH48t1ystobRuZmPXhXg4WJ6aiNrZk/NDaWxurPPkpOC', 'faculty', 'cse', NULL, '2026-06-20 15:56:23'),
(14, 'aditya', 'aditya@gmai.com', '$2y$10$9d8c86ABYjRJ6dDDgE3VDeBUo19xccQ1uVWHw9lT6OnWS2mXFTglO', 'student', 'mech', '', '2026-06-20 15:58:35'),
(15, 'Harshada', 'harshada@gmail.com', '$2y$10$sV94PEzJVVSAh0Rk/hMOd.LVn7Uc4i1W9IF5bEVTBlbLjvtWeaJ/e', 'student', 'Cse', 'Third Year', '2026-06-22 12:05:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `faculty_approvals`
--
ALTER TABLE `faculty_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `faculty_registration`
--
ALTER TABLE `faculty_registration`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `faculty_email` (`faculty_email`);

--
-- Indexes for table `gate_pass_requests`
--
ALTER TABLE `gate_pass_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_no` (`enrollment_no`);

--
-- Indexes for table `student_registration`
--
ALTER TABLE `student_registration`
  ADD PRIMARY KEY (`enrollment_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faculty_approvals`
--
ALTER TABLE `faculty_approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `faculty_registration`
--
ALTER TABLE `faculty_registration`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `gate_pass_requests`
--
ALTER TABLE `gate_pass_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
