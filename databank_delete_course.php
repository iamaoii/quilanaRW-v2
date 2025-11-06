<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : null;
    $created_by = $_SESSION['login_id'];

    if (!$course_id || !$program_id) {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    } else {
        $conn->begin_transaction();
        try {
            // Check if course exists and belongs to user
            $check_query = $conn->prepare("
                SELECT c.course_id 
                FROM rw_bank_course c 
                INNER JOIN rw_bank_program_course pc ON c.course_id = pc.course_id 
                WHERE c.course_id = ? AND c.created_by = ? AND pc.program_id = ?
            ");
            $check_query->bind_param("iii", $course_id, $created_by, $program_id);
            $check_query->execute();
            if ($check_query->get_result()->num_rows === 0) {
                throw new Exception('Course not found or unauthorized');
            }

            // Delete topics associated with the course
            $delete_topics = $conn->prepare("
                DELETE t FROM rw_bank_topic t 
                INNER JOIN rw_bank_program_course pc ON t.program_course_id = pc.program_course_id 
                WHERE pc.course_id = ? AND pc.program_id = ?
            ");
            $delete_topics->bind_param("ii", $course_id, $program_id);
            if (!$delete_topics->execute()) {
                throw new Exception('Error deleting topics: ' . $conn->error);
            }

            // Delete course from program
            $delete_program_course = $conn->prepare("
                DELETE FROM rw_bank_program_course 
                WHERE course_id = ? AND program_id = ?
            ");
            $delete_program_course->bind_param("ii", $course_id, $program_id);
            if (!$delete_program_course->execute()) {
                throw new Exception('Error unlinking course from program: ' . $conn->error);
            }

            // Delete course
            $delete_course = $conn->prepare("DELETE FROM rw_bank_course WHERE course_id = ? AND created_by = ?");
            $delete_course->bind_param("ii", $course_id, $created_by);
            if (!$delete_course->execute()) {
                throw new Exception('Error deleting course: ' . $conn->error);
            }

            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Course has been deleted.';
        } catch (Exception $e) {
            $conn->rollback();
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>