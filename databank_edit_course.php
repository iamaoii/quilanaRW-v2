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
    $course_name = isset($_POST['course_name']) ? trim($_POST['course_name']) : '';
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : null;
    $created_by = $_SESSION['login_id'];

    if (!$course_id || !$program_id || empty($course_name)) {
        $response['success'] = false;
        $response['message'] = 'Missing required parameters';
    } else {
        $conn->begin_transaction();
        try {
            // Check if course exists and belongs to user
            $check_query = $conn->prepare("SELECT course_id FROM rw_bank_course WHERE course_id = ? AND created_by = ?");
            $check_query->bind_param("ii", $course_id, $created_by);
            $check_query->execute();
            if ($check_query->get_result()->num_rows === 0) {
                throw new Exception('Course not found or unauthorized');
            }

            // Check if course name is already used
            $name_check = $conn->prepare("SELECT course_id FROM rw_bank_course WHERE course_name = ? AND created_by = ? AND course_id != ?");
            $name_check->bind_param("sii", $course_name, $created_by, $course_id);
            $name_check->execute();
            if ($name_check->get_result()->num_rows > 0) {
                throw new Exception('Course name already exists');
            }

            // Update course
            $update_query = $conn->prepare("UPDATE rw_bank_course SET course_name = ? WHERE course_id = ?");
            $update_query->bind_param("si", $course_name, $course_id);
            if (!$update_query->execute()) {
                throw new Exception('Error updating course: ' . $conn->error);
            }

            $conn->commit();
            $response['success'] = true;
            $response['message'] = 'Course updated successfully';
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