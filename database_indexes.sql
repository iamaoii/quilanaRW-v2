-- Performance Optimization: Add indexes for frequently queried columns
-- Run this on your database to improve query performance

-- Indexes for rw_bank tables (most frequently used)
ALTER TABLE `rw_bank_question` ADD INDEX `idx_topic_id` (`topic_id`);
ALTER TABLE `rw_bank_question` ADD INDEX `idx_created_by` (`created_by`);
ALTER TABLE `rw_bank_question_option` ADD INDEX `idx_question_id` (`question_id`);
ALTER TABLE `rw_bank_question_answer` ADD INDEX `idx_question_id` (`question_id`);
ALTER TABLE `rw_bank_assessment_question` ADD INDEX `idx_assessment_id` (`assessment_id`);
ALTER TABLE `rw_bank_assessment_question` ADD INDEX `idx_question_id` (`question_id`);

-- Indexes for assessment tables
ALTER TABLE `questions` ADD INDEX `idx_assessment_id` (`assessment_id`);
ALTER TABLE `question_options` ADD INDEX `idx_question_id` (`question_id`);
ALTER TABLE `question_identifications` ADD INDEX `idx_question_id` (`question_id`);

-- Indexes for student results and submissions
ALTER TABLE `student_results` ADD INDEX `idx_student_assessment` (`student_id`, `assessment_id`);

-- Indexes for enrollment and classes
ALTER TABLE `student_enrollment` ADD INDEX `idx_class_student` (`class_id`, `student_id`);
ALTER TABLE `administer_assessment` ADD INDEX `idx_class_assessment` (`class_id`, `assessment_id`);

-- Composite indexes for common query patterns
ALTER TABLE `rw_bank_topic` ADD INDEX `idx_program_course` (`program_course_id`);
ALTER TABLE `assessment` ADD INDEX `idx_program_faculty` (`program_id`, `faculty_id`);

