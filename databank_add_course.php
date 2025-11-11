<?php
include 'db_connect.php';
include 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Handle course linking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'link_existing_course') {
    $response = array();
    
    if (isset($_POST['course_id']) && isset($_POST['program_id'])) {
        $course_id = $_POST['course_id'];
        $program_id = $_POST['program_id'];
        
        $conn->begin_transaction();
        try {
            // Check if course is already linked to this program (double-check)
            $program_check = $conn->prepare("SELECT * FROM rw_bank_program_course WHERE program_id = ? AND course_id = ?");
            $program_check->bind_param("ii", $program_id, $course_id);
            $program_check->execute();
            
            if ($program_check->get_result()->num_rows > 0) {
                throw new Exception('This course is already linked to this program');
            }

            // Link course to program
            $link_course = $conn->prepare("INSERT INTO rw_bank_program_course (program_id, course_id) VALUES (?, ?)");
            $link_course->bind_param("ii", $program_id, $course_id);
            if (!$link_course->execute()) {
                throw new Exception('Error linking course to program: ' . $conn->error);
            }

            $conn->commit();

            $response['success'] = true;
            $response['message'] = 'Course linked successfully';
        } catch (Exception $e) {
            $conn->rollback();
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Handle regular course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

    if (isset($_POST['course_name']) && isset($_POST['program_id'])) {
        $course_name = trim($_POST['course_name']);
        $program_id = $_POST['program_id'];
        $created_by = $_SESSION['login_id'];

        if (empty($course_name)) {
            $response['success'] = false;
            $response['message'] = 'Course name is required';
        } else {
            $conn->begin_transaction();
            try {
                // Check if course exists
                $check_query = $conn->prepare("SELECT course_id FROM rw_bank_course WHERE course_name = ?");
                $check_query->bind_param("s", $course_name);
                $check_query->execute();
                $result = $check_query->get_result();

                if ($result->num_rows > 0) {
                    $course = $result->fetch_assoc();
                    $course_id = $course['course_id'];

                    // Check if course already linked to this program
                    $program_check = $conn->prepare("SELECT * FROM rw_bank_program_course WHERE program_id = ? AND course_id = ?");
                    $program_check->bind_param("ii", $program_id, $course_id);
                    $program_check->execute();

                    if ($program_check->get_result()->num_rows > 0) {
                        throw new Exception('This course is already added to this program');
                    }

                    // Check if course exists in other programs
                    $other_programs_query = $conn->prepare("
                        SELECT p.program_id, p.program_name 
                        FROM rw_bank_program p 
                        INNER JOIN rw_bank_program_course pc ON p.program_id = pc.program_id 
                        WHERE pc.course_id = ? AND p.program_id != ?
                    ");
                    $other_programs_query->bind_param("ii", $course_id, $program_id);
                    $other_programs_query->execute();
                    $other_programs_result = $other_programs_query->get_result();

                    if ($other_programs_result->num_rows > 0) {
                        $other_programs = array();
                        while ($program = $other_programs_result->fetch_assoc()) {
                            $other_programs[] = $program;
                        }
                        
                        $response['success'] = false;
                        $response['needs_confirmation'] = true;
                        $response['course_id'] = $course_id;
                        $response['course_name'] = $course_name;
                        $response['existing_programs'] = $other_programs;
                        $response['message'] = 'This course already exists in other program(s). Do you want to link it to this program?';
                        
                        header('Content-Type: application/json');
                        echo json_encode($response);
                        exit();
                    }
                } else {
                    // Insert new course
                    $insert_course = $conn->prepare("INSERT INTO rw_bank_course (course_name, created_by, no_of_topics) VALUES (?, ?, 0)");
                    $insert_course->bind_param("si", $course_name, $created_by);
                    if (!$insert_course->execute()) {
                        throw new Exception('Error creating course: ' . $conn->error);
                    }
                    $course_id = $conn->insert_id;
                }

                // Link course to program
                $link_course = $conn->prepare("INSERT INTO rw_bank_program_course (program_id, course_id) VALUES (?, ?)");
                $link_course->bind_param("ii", $program_id, $course_id);
                if (!$link_course->execute()) {
                    throw new Exception('Error linking course to program: ' . $conn->error);
                }

                $conn->commit();

                $response['success'] = true;
                $response['message'] = 'Course added successfully';
                $response['course_id'] = $course_id;
            } catch (Exception $e) {
                $conn->rollback();
                $response['success'] = false;
                $response['message'] = $e->getMessage();
            }
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>