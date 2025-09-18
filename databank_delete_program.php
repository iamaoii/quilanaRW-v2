<?php 
include 'db_connect.php'; 
include 'auth.php'; 

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();

    if (!isset($_POST['program_id'])) {
        $response['success'] = false;
        $response['message'] = 'Program ID is required';
    } else {
        $program_id = $_POST['program_id'];
        $created_by = $_SESSION['login_id'];
        
        // Check if program belongs to the user
        $check_query = $conn->prepare("SELECT COUNT(*) as count FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
        $check_query->bind_param("ii", $program_id, $created_by);
        $check_query->execute();
        $result = $check_query->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            // Start transaction to ensure all deletions succeed or none do
            $conn->begin_transaction();
            
            try {
                // First, delete all topics related to this program's courses
                $delete_topics_query = $conn->prepare("
                    DELETE t FROM rw_bank_topic t 
                    INNER JOIN rw_bank_program_course pc ON t.program_course_id = pc.program_course_id 
                    WHERE pc.program_id = ?
                ");
                $delete_topics_query->bind_param("i", $program_id);
                $delete_topics_query->execute();
                $delete_topics_query->close();
                
                // Then, delete all program-course relationships
                $delete_program_course_query = $conn->prepare("DELETE FROM rw_bank_program_course WHERE program_id = ?");
                $delete_program_course_query->bind_param("i", $program_id);
                $delete_program_course_query->execute();
                $delete_program_course_query->close();
                
                // Finally, delete the program itself
                $delete_program_query = $conn->prepare("DELETE FROM rw_bank_program WHERE program_id = ? AND created_by = ?");
                $delete_program_query->bind_param("ii", $program_id, $created_by);
                
                if ($delete_program_query->execute()) {
                    // Commit the transaction
                    $conn->commit();
                    $response['success'] = true;
                    $response['message'] = 'Program and all related data deleted successfully';
                } else {
                    throw new Exception('Failed to delete program');
                }
                $delete_program_query->close();
                
            } catch (Exception $e) {
                // Rollback the transaction on any error
                $conn->rollback();
                $response['success'] = false;
                $response['message'] = 'Failed to delete program: ' . $e->getMessage();
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Program not found or access denied';
        }
        $check_query->close();
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}