-- Databank System Tables
-- These tables are needed for the databank functionality

-- Programs table
CREATE TABLE rw_bank_program (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES faculty(faculty_id)
);

-- Courses table
CREATE TABLE rw_bank_course (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    no_of_topics INT DEFAULT 0,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES faculty(faculty_id)
);

-- Junction table linking programs to courses
CREATE TABLE rw_bank_program_course (
    program_course_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    course_id INT NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES rw_bank_program(program_id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES rw_bank_course(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_program_course (program_id, course_id)
);

-- Topics table
CREATE TABLE rw_bank_topics (
    topic_id INT AUTO_INCREMENT PRIMARY KEY,
    topic_name VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    created_by INT NOT NULL,
    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES rw_bank_course(course_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES faculty(faculty_id)
);
