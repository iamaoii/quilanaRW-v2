-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 16, 2025 at 07:27 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `updated_quilana`
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
  `date_administered` date NOT NULL DEFAULT curdate()
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

--
-- Dumping data for table `assessment`
--

INSERT INTO `assessment` (`assessment_id`, `assessment_type`, `assessment_mode`, `assessment_name`, `program_id`, `course_name`, `topic`, `time_limit`, `passing_rate`, `total_points`, `max_points`, `max_warnings`, `student_count`, `remaining_points`, `randomize_questions`, `faculty_id`, `date_updated`) VALUES
(1, 1, 1, 'Assessment 1 - DBA', 1, 'Database Administration', 'Intro to DBA', 30, 60, 0, NULL, 5, NULL, NULL, 1, 1, '2025-09-14 16:44:27');

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

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `code`, `faculty_id`, `program_id`, `course_name`, `class_name`, `date_created`, `date_updated`) VALUES
(1, 'aedd581c', 1, 1, 'Database Administration', 'BSIT 3-1', '2025-09-14 16:38:32', '2025-09-14 16:38:32');

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
(1, 'Bobby', 'Marino', '1234-12345-MN-0', 'bobbymarino@pup.edu.ph', 'bobby', '$2y$10$tUvFXnOxfPydppIL2ectxOifUZcfaFhjEDqtWs3r1h29.cOxeNPF.', 2, '2025-09-14 12:57:25');

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

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`program_id`, `program_name`, `faculty_id`) VALUES
(1, 'BSIT', 1),
(2, 'BSCS', 1);

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

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`question_id`, `question`, `assessment_id`, `order_by`, `ques_type`, `total_points`, `date_updated`, `time_limit`) VALUES
(1, 'Multiple question ito', 1, 0, 1, 2, '2025-09-14 16:44:27', NULL),
(2, 'Checkbox question ito', 1, 0, 2, 3, '2025-09-14 16:44:27', NULL),
(3, 'True or false na question ito', 1, 0, 3, 1, '2025-09-14 16:44:27', NULL),
(4, 'Identification question ito', 1, 0, 4, 4, '2025-09-14 16:44:27', NULL),
(5, 'Fill in the blank na question', 1, 0, 5, 5, '2025-09-14 16:44:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `question_identifications`
--

CREATE TABLE `question_identifications` (
  `identification_id` int(11) NOT NULL,
  `identification_answer` text NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `question_identifications`
--

INSERT INTO `question_identifications` (`identification_id`, `identification_answer`, `question_id`) VALUES
(1, 'Identification na sagot', 4),
(2, 'sagot mo', 5);

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

--
-- Dumping data for table `question_options`
--

INSERT INTO `question_options` (`option_id`, `option_txt`, `is_right`, `question_id`) VALUES
(1, 'Tama', 1, 1),
(2, 'Mali', 0, 1),
(3, 'Mali', 0, 1),
(4, 'Tama', 1, 2),
(5, 'Correct', 1, 2),
(6, 'Mali', 0, 2);

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

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_program`
--

CREATE TABLE `rw_bank_program` (
  `program_id` int(11) NOT NULL,
  `program_name` varchar(150) NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_program_course`
--

CREATE TABLE `rw_bank_program_course` (
  `program_course_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rw_bank_question_answer`
--

CREATE TABLE `rw_bank_question_answer` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `correct_answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `date_taken` date NOT NULL DEFAULT curdate()
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

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `firstname`, `lastname`, `webmail`, `student_number`, `username`, `password`, `user_type`, `date_updated`) VALUES
(1, 'Nate', 'Libao', 'natelibao@iskolarngbayan.pup.edu.ph', '1234-12345-MN-0', 'nate', '$2y$10$g.GPMiR25ndxgJC/bBBnluG2vyISV7UWj6Y.IyQUKMOMlmmysNiNq', 3, '2025-09-14 20:36:25');

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
  ADD KEY `fk_rw_aq_question` (`question_id`);

--
-- Indexes for table `rw_bank_course`
--
ALTER TABLE `rw_bank_course`
  ADD PRIMARY KEY (`course_id`),
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
  MODIFY `administer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assessment`
--
ALTER TABLE `assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assessment_uploads`
--
ALTER TABLE `assessment_uploads`
  MODIFY `upload_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dashboard_settings`
--
ALTER TABLE `dashboard_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `join_assessment`
--
ALTER TABLE `join_assessment`
  MODIFY `join_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `question_identifications`
--
ALTER TABLE `question_identifications`
  MODIFY `identification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `question_options`
--
ALTER TABLE `question_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rw_answer`
--
ALTER TABLE `rw_answer`
  MODIFY `rw_answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_assessment`
--
ALTER TABLE `rw_bank_assessment`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_assessment_question`
--
ALTER TABLE `rw_bank_assessment_question`
  MODIFY `assessment_question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_course`
--
ALTER TABLE `rw_bank_course`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_program`
--
ALTER TABLE `rw_bank_program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_program_course`
--
ALTER TABLE `rw_bank_program_course`
  MODIFY `program_course_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_question`
--
ALTER TABLE `rw_bank_question`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_question_answer`
--
ALTER TABLE `rw_bank_question_answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_question_option`
--
ALTER TABLE `rw_bank_question_option`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rw_bank_topic`
--
ALTER TABLE `rw_bank_topic`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_answer`
--
ALTER TABLE `student_answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_enrollment`
--
ALTER TABLE `student_enrollment`
  MODIFY `studentEnrollment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `results_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_submission`
--
ALTER TABLE `student_submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `fk_rw_pc_course` FOREIGN KEY (`course_id`) REFERENCES `rw_bank_course` (`course_id`),
  ADD CONSTRAINT `fk_rw_pc_program` FOREIGN KEY (`program_id`) REFERENCES `rw_bank_program` (`program_id`);

--
-- Constraints for table `rw_bank_question`
--
ALTER TABLE `rw_bank_question`
  ADD CONSTRAINT `fk_rw_q_faculty` FOREIGN KEY (`created_by`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `fk_rw_q_topic` FOREIGN KEY (`topic_id`) REFERENCES `rw_bank_topic` (`topic_id`);

--
-- Constraints for table `rw_bank_question_answer`
--
ALTER TABLE `rw_bank_question_answer`
  ADD CONSTRAINT `fk_rw_qa_question` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`);

--
-- Constraints for table `rw_bank_question_option`
--
ALTER TABLE `rw_bank_question_option`
  ADD CONSTRAINT `fk_rw_qo_question` FOREIGN KEY (`question_id`) REFERENCES `rw_bank_question` (`question_id`);

--
-- Constraints for table `rw_bank_topic`
--
ALTER TABLE `rw_bank_topic`
  ADD CONSTRAINT `fk_rw_topic_pc` FOREIGN KEY (`program_course_id`) REFERENCES `rw_bank_program_course` (`program_course_id`);

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
