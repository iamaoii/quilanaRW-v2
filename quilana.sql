-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 06, 2026 at 11:37 AM
-- Server version: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quilana`
--

-- --------------------------------------------------------

--
-- Table structure for table `administer_assessment`
--

CREATE TABLE `administer_assessment` (
  `administer_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `ranks_status` tinyint(1) NOT NULL,
  `date_administered` date NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment`
--

CREATE TABLE `assessment` (
  `assessment_id` int(11) NOT NULL,
  `assessment_type` int(11) NOT NULL,
  `assessment_mode` tinyint(1) NOT NULL,
  `assessment_name` varchar(150) NOT NULL,
  `program_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `topic` varchar(200) NOT NULL,
  `time_limit` int(11) DEFAULT NULL,
  `passing_rate` int(11) DEFAULT NULL,
  `total_points` int(11) NOT NULL,
  `max_points` int(11) DEFAULT NULL,
  `max_warnings` int(3) NOT NULL DEFAULT 3,
  `student_count` int(11) DEFAULT NULL,
  `remaining_points` int(11) DEFAULT NULL,
  `randomize_questions` tinyint(1) NOT NULL DEFAULT 1,
  `faculty_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assessment_uploads`
--

CREATE TABLE `assessment_uploads` (
  `upload_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `code` varchar(8) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dashboard_settings`
--

CREATE TABLE `dashboard_settings` (
  `setting_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` tinyint(1) NOT NULL,
  `summary` tinyint(1) NOT NULL DEFAULT 1,
  `recent` tinyint(1) NOT NULL DEFAULT 1,
  `request` tinyint(1) NOT NULL DEFAULT 1,
  `report` tinyint(1) NOT NULL DEFAULT 0,
  `calendar` tinyint(1) NOT NULL DEFAULT 1,
  `upcoming` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `faculty_number` varchar(15) NOT NULL,
  `webmail` varchar(150) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT 2,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `firstname`, `lastname`, `faculty_number`, `webmail`, `username`, `password`, `user_type`, `date_updated`) VALUES
(5, 'admin', 'admin', '1234-12345-MN-0', 'admin@pup.edu.ph', 'admin', '$2y$10$W32yy3fUHxPJCTfs8aQmxOLt5qiTis.ROg1Huc21Ln6vbSd152imS', 2, '2025-11-11 13:01:46'),
(6, 'Monina', 'Barretto', '2013-13175-MN-0', 'mdbarretto@pup.edu.ph', 'nina', '$2y$10$JTCc2DSCB5t9xcHElab.V.dx.nfihZw6iRhdJv6pEovnhmmLsh4PC', 2, '2025-11-11 13:10:49'),
(7, 'Charles', 'Leclerc', '2025-00016-MN-0', 'cl16@pup.edu.ph', 'cl16', '$2y$10$UsrR.dwq2kbkF5wXwdRD9ebVNohyQ9PenSMn4MVKQu/7x.aB9NOWm', 2, '2025-11-11 15:16:36'),
(8, 'ninski', 'barretto', '2013-13174-MN-0', 'mdbarrett@pup.edu.ph', 'ninski', '$2y$10$a0z/I43f6qrm/bvpALqgBe8F..UTPihnaSpGe7dLDFdYpdp/s.3qu', 2, '2026-01-02 12:23:29');

-- --------------------------------------------------------

--
-- Table structure for table `join_assessment`
--

CREATE TABLE `join_assessment` (
  `join_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `administer_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `attempts` int(1) NOT NULL DEFAULT 0,
  `suspicious_act` int(2) DEFAULT 0,
  `if_display` tinyint(1) NOT NULL DEFAULT 0,
  `method` varchar(150) NOT NULL,
  `time_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(150) NOT NULL,
  `faculty_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `question_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `order_by` int(11) NOT NULL,
  `ques_type` tinyint(1) NOT NULL,
  `total_points` int(3) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `time_limit` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_identifications`
--

CREATE TABLE `question_identifications` (
  `identification_id` int(11) NOT NULL,
  `identification_answer` text NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_options`
--

CREATE TABLE `question_options` (
  `option_id` int(11) NOT NULL,
  `option_txt` text NOT NULL,
  `is_right` tinyint(1) NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_answer`
--

CREATE TABLE `rw_answer` (
  `rw_answer_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `rw_submission_id` int(11) NOT NULL,
  `rw_question_id` int(11) NOT NULL,
  `rw_option_id` int(11) DEFAULT NULL,
  `is_right` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_assessment`
--

CREATE TABLE `rw_bank_assessment` (
  `assessment_id` int(11) NOT NULL,
  `assessment_title` varchar(200) NOT NULL,
  `assessment_type` char(1) NOT NULL,
  `created_by` int(11) NOT NULL,
  `no_of_questions` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_assessment`
--

INSERT INTO `rw_bank_assessment` (`assessment_id`, `assessment_title`, `assessment_type`, `created_by`, `no_of_questions`) VALUES
(7, 'Assessment 1-DGL', '1', 6, 10),
(8, 'Assessment 1', '1', 8, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_assessment_question`
--

CREATE TABLE `rw_bank_assessment_question` (
  `assessment_question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_assessment_question`
--

INSERT INTO `rw_bank_assessment_question` (`assessment_question_id`, `assessment_id`, `question_id`, `date_added`) VALUES
(18, 7, 27, '2025-11-11 14:35:24'),
(19, 7, 26, '2025-11-11 14:35:24'),
(20, 7, 25, '2025-11-11 14:35:24'),
(21, 7, 24, '2025-11-11 14:35:24'),
(22, 7, 23, '2025-11-11 14:35:24'),
(23, 7, 22, '2025-11-11 14:35:24'),
(24, 7, 21, '2025-11-11 14:35:24'),
(25, 7, 20, '2025-11-11 14:35:24'),
(26, 7, 19, '2025-11-11 14:35:24'),
(27, 7, 18, '2025-11-11 14:35:24');

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_course`
--

CREATE TABLE `rw_bank_course` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `created_by` int(11) NOT NULL,
  `no_of_topics` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_course`
--

INSERT INTO `rw_bank_course` (`course_id`, `course_name`, `created_by`, `no_of_topics`) VALUES
(26, 'COMP 001', 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_program`
--

CREATE TABLE `rw_bank_program` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(150) NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_program`
--

INSERT INTO `rw_bank_program` (`program_id`, `program_name`, `created_by`) VALUES
(13, 'BSIT', 6);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_program_course`
--

CREATE TABLE `rw_bank_program_course` (
  `program_course_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_program_course`
--

INSERT INTO `rw_bank_program_course` (`program_course_id`, `program_id`, `course_id`) VALUES
(30, 13, 26);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_question`
--

CREATE TABLE `rw_bank_question` (
  `question_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` char(1) NOT NULL,
  `difficulty` char(1) NOT NULL,
  `created_by` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_points` int(3) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_question`
--

INSERT INTO `rw_bank_question` (`question_id`, `topic_id`, `question_text`, `question_type`, `difficulty`, `created_by`, `date_created`, `date_updated`, `total_points`) VALUES
(18, 25, 'He first implemented the boolean algebra for switching circuts', '1', '1', 6, '2025-11-11 13:56:59', '2025-11-11 13:56:59', 1),
(19, 25, 'Which are the basic gates', '1', '1', 6, '2025-11-11 13:58:11', '2025-11-11 13:58:11', 1),
(20, 25, 'What is the equivalent gate of this equation:   X + Y = Z', '1', '1', 6, '2025-11-11 14:00:39', '2025-11-11 14:00:39', 1),
(21, 25, 'Which are true of boolean algebra', '2', '1', 6, '2025-11-11 14:08:46', '2025-11-11 14:08:46', 1),
(22, 25, 'An early thinker known to be the Father of Logic.', '4', '1', 6, '2025-11-11 14:10:20', '2025-11-11 14:10:20', 1),
(23, 25, 'Boolean algebra deals only with 0 and 1 elements', '3', '1', 6, '2025-11-11 14:10:52', '2025-11-11 14:10:52', 1),
(24, 25, 'This equation:   AB + CD = E   is', '2', '1', 6, '2025-11-11 14:13:14', '2025-11-11 14:13:14', 1),
(25, 25, 'Given this equation, AB + CD = E\r\nHow many times will the value of E be equal to 1', '1', '1', 6, '2025-11-11 14:26:24', '2025-11-11 14:26:24', 1),
(26, 25, 'What gate will give a high output if and only if one of its 2 values is 1 or if its 2 inputs have different values?', '4', '1', 6, '2025-11-11 14:31:31', '2025-11-11 14:31:31', 1),
(27, 25, 'Given the equation AB + CD = E\r\nWhat will be the value of E if its inputs has the following values\r\n1010 for A, B, C, and D respectively.', '3', '1', 6, '2025-11-11 14:33:37', '2025-11-11 14:33:37', 1),
(29, 26, 'IT professional who code, tests, debug programs', '4', '1', 8, '2026-01-06 02:47:05', '2026-01-06 02:47:05', 1),
(30, 26, 'MIS manager are involved only in the planning stage', '3', '1', 8, '2026-01-06 02:47:40', '2026-01-06 02:47:40', 1),
(31, 26, 'IT professionals who develop via the web', '4', '1', 8, '2026-01-06 02:50:59', '2026-01-06 02:50:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_question_answer`
--

CREATE TABLE `rw_bank_question_answer` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `correct_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_question_answer`
--

INSERT INTO `rw_bank_question_answer` (`answer_id`, `question_id`, `correct_answer`) VALUES
(28, 22, 'Aristotle'),
(29, 26, 'XOR'),
(30, 29, 'software engineer'),
(31, 31, 'web developer');

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_question_option`
--

CREATE TABLE `rw_bank_question_option` (
  `option_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_question_option`
--

INSERT INTO `rw_bank_question_option` (`option_id`, `question_id`, `option_text`, `is_correct`) VALUES
(55, 18, 'Aristotle', 0),
(56, 18, 'Shannon', 1),
(57, 18, 'Boole', 0),
(58, 19, 'AND, NOT, OR', 1),
(59, 19, 'NAND, NOR', 0),
(60, 19, 'XOR, XNOR', 0),
(61, 20, 'XOR', 0),
(62, 20, 'AND', 0),
(63, 20, 'OR', 1),
(64, 20, 'XNOR', 0),
(65, 21, 'It uses a set of real numbers', 0),
(66, 21, 'It has the concept of complementations', 1),
(67, 21, 'It has addition and multiplication', 1),
(68, 21, 'It has the inverser of addition and multiplication', 0),
(69, 23, 'True', 1),
(70, 23, 'False', 0),
(71, 24, 'product of sums', 0),
(72, 24, 'sum of products', 1),
(73, 24, 'and-or network', 1),
(74, 24, 'or-and network', 0),
(75, 25, '1', 0),
(76, 25, '2', 0),
(77, 25, '3', 0),
(78, 25, '4', 1),
(79, 27, 'True', 0),
(80, 27, 'False', 1),
(81, 30, 'True', 0),
(82, 30, 'False', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_topic`
--

CREATE TABLE `rw_bank_topic` (
  `topic_id` int(11) NOT NULL,
  `program_course_id` int(11) NOT NULL,
  `topic_name` varchar(200) NOT NULL,
  `no_of_questions` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rw_bank_topic`
--

INSERT INTO `rw_bank_topic` (`topic_id`, `program_course_id`, `topic_name`, `no_of_questions`) VALUES
(25, 30, 'Digital Logic System', 10),
(26, 30, 'Peopleware', 3);

-- --------------------------------------------------------

--
-- Table structure for table `rw_flashcard`
--

CREATE TABLE `rw_flashcard` (
  `flashcard_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `term` varchar(255) NOT NULL,
  `definition` varchar(255) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_questions`
--

CREATE TABLE `rw_questions` (
  `rw_question_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `order_by` int(11) NOT NULL,
  `question_type` tinyint(1) NOT NULL,
  `total_points` int(11) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_question_identifications`
--

CREATE TABLE `rw_question_identifications` (
  `rw_identification_id` int(11) NOT NULL,
  `rw_question_id` int(11) NOT NULL,
  `identification_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_question_opt`
--

CREATE TABLE `rw_question_opt` (
  `rw_option_id` int(11) NOT NULL,
  `option_text` text NOT NULL,
  `is_right` tinyint(1) NOT NULL,
  `rw_question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_reviewer`
--

CREATE TABLE `rw_reviewer` (
  `reviewer_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reviewer_code` varchar(25) DEFAULT NULL,
  `reviewer_name` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `reviewer_type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_student_results`
--

CREATE TABLE `rw_student_results` (
  `rw_results_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `rw_submission_id` int(11) NOT NULL,
  `student_score` int(11) NOT NULL,
  `date_taken` date NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_student_submission`
--

CREATE TABLE `rw_student_submission` (
  `rw_submission_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `student_score` int(11) NOT NULL,
  `date_taken` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_student_todo`
--

CREATE TABLE `rw_student_todo` (
  `todo_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `todo_text` varchar(100) NOT NULL,
  `todo_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedule_assessments`
--

CREATE TABLE `schedule_assessments` (
  `schedule_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `date_scheduled` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `firstname` varchar(150) NOT NULL,
  `lastname` varchar(150) NOT NULL,
  `webmail` varchar(150) NOT NULL,
  `student_number` varchar(15) NOT NULL,
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` tinyint(1) NOT NULL DEFAULT 3,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
  `request_id` int(11) NOT NULL,
  `user_type` tinyint(1) NOT NULL COMMENT '2 = Faculty, 3 = Student',
  `user_id` int(11) NOT NULL COMMENT 'faculty_id or student_id',
  `username` varchar(150) NOT NULL,
  `webmail` varchar(150) NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `date_requested` datetime NOT NULL DEFAULT current_timestamp(),
  `date_approved` datetime DEFAULT NULL,
  `date_processed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_answer`
--

CREATE TABLE `student_answer` (
  `answer_id` int(11) NOT NULL,
  `answer_value` varchar(150) NOT NULL,
  `answer_type` text NOT NULL,
  `identification_id` int(11) DEFAULT NULL,
  `submission_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `time_elapsed` int(20) DEFAULT NULL,
  `answer_rank` int(11) DEFAULT NULL,
  `is_right` tinyint(1) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_enrollment`
--

CREATE TABLE `student_enrollment` (
  `studentEnrollment_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `reason` text DEFAULT NULL,
  `if_display` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `results_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `total_score` int(3) NOT NULL,
  `score` int(3) NOT NULL,
  `remarks` text DEFAULT NULL,
  `rank` int(3) DEFAULT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_submission`
--

CREATE TABLE `student_submission` (
  `submission_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `date_taken` datetime NOT NULL,
  `administer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_reviewers`
--

CREATE TABLE `user_reviewers` (
  `shared_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewer_name` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `reviewer_type` varchar(100) NOT NULL,
  `student_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  ADD PRIMARY KEY (`administer_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `administer_assessment_ibfk_3` (`class_id`);

--
-- Indexes for table `assessment`
--
ALTER TABLE `assessment`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `assessment_uploads`
--
ALTER TABLE `assessment_uploads`
  ADD PRIMARY KEY (`upload_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `faculty_id` (`faculty_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`);

--
-- Indexes for table `join_assessment`
--
ALTER TABLE `join_assessment`
  ADD PRIMARY KEY (`join_id`),
  ADD KEY `join_assessment_ibfk_1` (`student_id`),
  ADD KEY `join_assessment_ibfk_2` (`administer_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `question_identifications`
--
ALTER TABLE `question_identifications`
  ADD PRIMARY KEY (`identification_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `question_options`
--
ALTER TABLE `question_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `rw_answer`
--
ALTER TABLE `rw_answer`
  ADD PRIMARY KEY (`rw_answer_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `rw_submission_id` (`rw_submission_id`),
  ADD KEY `rw_question_id` (`rw_question_id`),
  ADD KEY `rw_option_id` (`rw_option_id`);

--
-- Indexes for table `rw_bank_assessment`
--
ALTER TABLE `rw_bank_assessment`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `fk_rw_assessment_faculty` (`created_by`);

--
-- Indexes for table `rw_bank_assessment_question`
--
ALTER TABLE `rw_bank_assessment_question`
  ADD PRIMARY KEY (`assessment_question_id`),
  ADD KEY `fk_rw_aq_assessment` (`assessment_id`),
  ADD KEY `fk_question_id` (`question_id`);

--
-- Indexes for table `rw_bank_course`
--
ALTER TABLE `rw_bank_course`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `uq_course_name` (`course_name`),
  ADD KEY `fk_rw_course_faculty` (`created_by`);

--
-- Indexes for table `rw_bank_program`
--
ALTER TABLE `rw_bank_program`
  ADD PRIMARY KEY (`program_id`),
  ADD KEY `fk_rw_program_faculty` (`created_by`);

--
-- Indexes for table `rw_bank_program_course`
--
ALTER TABLE `rw_bank_program_course`
  ADD PRIMARY KEY (`program_course_id`),
  ADD KEY `fk_rw_pc_program` (`program_id`),
  ADD KEY `fk_rw_pc_course` (`course_id`);

--
-- Indexes for table `rw_bank_question`
--
ALTER TABLE `rw_bank_question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `fk_rw_q_topic` (`topic_id`),
  ADD KEY `fk_rw_q_faculty` (`created_by`);

--
-- Indexes for table `rw_bank_question_answer`
--
ALTER TABLE `rw_bank_question_answer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `fk_rw_qa_question` (`question_id`);

--
-- Indexes for table `rw_bank_question_option`
--
ALTER TABLE `rw_bank_question_option`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `fk_rw_qo_question` (`question_id`);

--
-- Indexes for table `rw_bank_topic`
--
ALTER TABLE `rw_bank_topic`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `fk_rw_topic_pc` (`program_course_id`);

--
-- Indexes for table `rw_flashcard`
--
ALTER TABLE `rw_flashcard`
  ADD PRIMARY KEY (`flashcard_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `rw_questions`
--
ALTER TABLE `rw_questions`
  ADD PRIMARY KEY (`rw_question_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `rw_question_identifications`
--
ALTER TABLE `rw_question_identifications`
  ADD PRIMARY KEY (`rw_identification_id`),
  ADD KEY `rw_question_id` (`rw_question_id`);

--
-- Indexes for table `rw_question_opt`
--
ALTER TABLE `rw_question_opt`
  ADD PRIMARY KEY (`rw_option_id`),
  ADD KEY `rw_question_id` (`rw_question_id`);

--
-- Indexes for table `rw_reviewer`
--
ALTER TABLE `rw_reviewer`
  ADD PRIMARY KEY (`reviewer_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `rw_student_results`
--
ALTER TABLE `rw_student_results`
  ADD PRIMARY KEY (`rw_results_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `rw_submission_id` (`rw_submission_id`);

--
-- Indexes for table `rw_student_submission`
--
ALTER TABLE `rw_student_submission`
  ADD PRIMARY KEY (`rw_submission_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `rw_student_todo`
--
ALTER TABLE `rw_student_todo`
  ADD PRIMARY KEY (`todo_id`);

--
-- Indexes for table `schedule_assessments`
--
ALTER TABLE `schedule_assessments`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `idx_user` (`user_type`,`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_requested` (`date_requested`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`);

--
-- Indexes for table `student_answer`
--
ALTER TABLE `student_answer`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `student_answer_ibfk_3` (`option_id`),
  ADD KEY `student_answer_ibfk_4` (`identification_id`);

--
-- Indexes for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  ADD PRIMARY KEY (`studentEnrollment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `student_enrollment_ibfk_1` (`class_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`results_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `student_submission`
--
ALTER TABLE `student_submission`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `administer_id` (`administer_id`);

--
-- Indexes for table `user_reviewers`
--
ALTER TABLE `user_reviewers`
  ADD PRIMARY KEY (`shared_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  MODIFY `administer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assessment_uploads`
--
ALTER TABLE `assessment_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `join_assessment`
--
ALTER TABLE `join_assessment`
  MODIFY `join_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `question_identifications`
--
ALTER TABLE `question_identifications`
  MODIFY `identification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `rw_answer`
--
ALTER TABLE `rw_answer`
  MODIFY `rw_answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_assessment`
--
ALTER TABLE `rw_bank_assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rw_bank_assessment_question`
--
ALTER TABLE `rw_bank_assessment_question`
  MODIFY `assessment_question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `rw_bank_course`
--
ALTER TABLE `rw_bank_course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `rw_bank_program`
--
ALTER TABLE `rw_bank_program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `rw_bank_program_course`
--
ALTER TABLE `rw_bank_program_course`
  MODIFY `program_course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `rw_bank_question`
--
ALTER TABLE `rw_bank_question`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `rw_bank_question_answer`
--
ALTER TABLE `rw_bank_question_answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `rw_bank_question_option`
--
ALTER TABLE `rw_bank_question_option`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `rw_bank_topic`
--
ALTER TABLE `rw_bank_topic`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `rw_flashcard`
--
ALTER TABLE `rw_flashcard`
  MODIFY `flashcard_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_questions`
--
ALTER TABLE `rw_questions`
  MODIFY `rw_question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_question_identifications`
--
ALTER TABLE `rw_question_identifications`
  MODIFY `rw_identification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_question_opt`
--
ALTER TABLE `rw_question_opt`
  MODIFY `rw_option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_reviewer`
--
ALTER TABLE `rw_reviewer`
  MODIFY `reviewer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_student_results`
--
ALTER TABLE `rw_student_results`
  MODIFY `rw_results_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_student_submission`
--
ALTER TABLE `rw_student_submission`
  MODIFY `rw_submission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_student_todo`
--
ALTER TABLE `rw_student_todo`
  MODIFY `todo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedule_assessments`
--
ALTER TABLE `schedule_assessments`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_requests`
--
ALTER TABLE `password_reset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_answer`
--
ALTER TABLE `student_answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  MODIFY `studentEnrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `results_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_submission`
--
ALTER TABLE `student_submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_reviewers`
--
ALTER TABLE `user_reviewers`
  MODIFY `shared_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administer_assessment`
--
ALTER TABLE `administer_assessment`
  ADD CONSTRAINT `administer_assessment_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`),
  ADD CONSTRAINT `administer_assessment_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`),
  ADD CONSTRAINT `administer_assessment_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `assessment`
--
ALTER TABLE `assessment`
  ADD CONSTRAINT `assessment_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`),
  ADD CONSTRAINT `assessment_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `assessment_uploads`
--
ALTER TABLE `assessment_uploads`
  ADD CONSTRAINT `assessment_uploads_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`),
  ADD CONSTRAINT `assessment_uploads_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`);

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `class_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `class_ibfk_3` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`);

--
-- Constraints for table `join_assessment`
--
ALTER TABLE `join_assessment`
  ADD CONSTRAINT `join_assessment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `join_assessment_ibfk_2` FOREIGN KEY (`administer_id`) REFERENCES `administer_assessment` (`administer_id`);

--
-- Constraints for table `program`
--
ALTER TABLE `program`
  ADD CONSTRAINT `program_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessment` (`assessment_id`);

--
-- Constraints for table `question_identifications`
--
ALTER TABLE `question_identifications`
  ADD CONSTRAINT `question_identifications_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`);

--
-- Constraints for table `question_options`
--
ALTER TABLE `question_options`
  ADD CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`);

--
-- Constraints for table `rw_bank_assessment`
--
ALTER TABLE `rw_bank_assessment`
  ADD CONSTRAINT `fk_rw_assessment_faculty` FOREIGN KEY (`created_by`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `rw_bank_assessment_question`
--
ALTER TABLE `rw_bank_assessment_question`
  ADD CONSTRAINT `fk_question_id` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rw_aq_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `rw_bank_assessment` (`assessment_id`),
  ADD CONSTRAINT `fk_rw_aq_question` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`);

--
-- Constraints for table `rw_bank_course`
--
ALTER TABLE `rw_bank_course`
  ADD CONSTRAINT `fk_rw_course_faculty` FOREIGN KEY (`created_by`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `rw_bank_program`
--
ALTER TABLE `rw_bank_program`
  ADD CONSTRAINT `fk_rw_program_faculty` FOREIGN KEY (`created_by`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `rw_bank_program_course`
--
ALTER TABLE `rw_bank_program_course`
  ADD CONSTRAINT `fk_rw_pc_course_fix` FOREIGN KEY (`course_id`) REFERENCES `rw_bank_course` (`course_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rw_pc_program_fix` FOREIGN KEY (`program_id`) REFERENCES `rw_bank_program` (`program_id`) ON DELETE CASCADE;

--
-- Constraints for table `rw_bank_question`
--
ALTER TABLE `rw_bank_question`
  ADD CONSTRAINT `fk_rw_q_faculty` FOREIGN KEY (`created_by`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `fk_rw_q_topic_fix` FOREIGN KEY (`topic_id`) REFERENCES `rw_bank_topic` (`topic_id`) ON DELETE CASCADE;

--
-- Constraints for table `rw_bank_question_answer`
--
ALTER TABLE `rw_bank_question_answer`
  ADD CONSTRAINT `fk_rw_qa_question` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `rw_bank_question_option`
--
ALTER TABLE `rw_bank_question_option`
  ADD CONSTRAINT `fk_rw_qo_question` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `rw_bank_topic`
--
ALTER TABLE `rw_bank_topic`
  ADD CONSTRAINT `fk_rw_topic_pc_fix` FOREIGN KEY (`program_course_id`) REFERENCES `rw_bank_program_course` (`program_course_id`) ON DELETE CASCADE;

--
-- Constraints for table `rw_flashcard`
--
ALTER TABLE `rw_flashcard`
  ADD CONSTRAINT `rw_flashcard_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  ADD CONSTRAINT `rw_flashcard_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `rw_reviewer` (`reviewer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
