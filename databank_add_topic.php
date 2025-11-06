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
    
    if (isset($_POST['topic_name']) && isset($_POST['course_id']) && isset($_POST['program_id'])) {
        $topic_name = trim($_POST['topic_name']);
        $course_id = intval($_POST['course_id']);
        $program_id = intval($_POST['program_id']);
        $created_by = $_SESSION['login_id'];
        
        if (empty($topic_name)) {
            $response['success'] = false;
            $response['message'] = 'Topic name is required';
        } else {
            $conn->begin_transaction();
            try {
                $program_course_query = $conn->prepare("SELECT program_course_id FROM rw_bank_program_course WHERE course_id = ? AND program_id = ?");
                $program_course_query->bind_param("ii", $course_id, $program_id);
                $program_course_query->execute();
                $program_course_result = $program_course_query->get_result();
                
                if ($program_course_result->num_rows == 0) {
                    throw new Exception('Invalid course for this program');
                }
                
                $program_course_row = $program_course_result->fetch_assoc();
                $program_course_id = $program_course_row['program_course_id'];
                
                $check_query = $conn->prepare("SELECT topic_id FROM rw_bank_topic WHERE topic_name = ? AND program_course_id = ?");
                $check_query->bind_param("si", $topic_name, $program_course_id);
                $check_query->execute();
                
                if ($check_query->get_result()->num_rows > 0) {
                    throw new Exception('This topic already exists in this course');
                }
                
                $insert_topic = $conn->prepare("INSERT INTO rw_bank_topic (topic_name, program_course_id, no_of_questions) VALUES (?, ?, 0)");
                $insert_topic->bind_param("si", $topic_name, $program_course_id);
                $insert_topic->execute();
                
                $conn->commit();
                
                $response['success'] = true;
                $response['message'] = 'Topic added successfully';
                $response['topic_id'] = $conn->insert_id;
                
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