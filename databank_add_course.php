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
                // Check if course exists for this specific program
                $check_query = $conn->prepare("
                    SELECT c.course_id 
                    FROM rw_bank_course c 
                    INNER JOIN rw_bank_program_course pc ON c.course_id = pc.course_id 
                    WHERE c.course_name = ? AND pc.program_id = ? AND c.created_by = ?
                ");
                // types: s = string (course_name), i = int (program_id), i = int (created_by)
                $check_query->bind_param("sii", $course_name, $program_id, $created_by);
                $check_query->execute();
                $result = $check_query->get_result();

                if ($result->num_rows > 0) {
                    throw new Exception('This course already exists in this program');
                }

                // Create a new course for this program
                $insert_course = $conn->prepare("INSERT INTO rw_bank_course (course_name, created_by, no_of_topics) VALUES (?, ?, 0)");
                $insert_course->bind_param("si", $course_name, $created_by);
                if (!$insert_course->execute()) {
                    throw new Exception('Error creating course: ' . $conn->error);
                }
                $course_id = $conn->insert_id;

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