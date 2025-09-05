<?php
include 'db_connect.php';

if (isset($_POST['reviewer_id'])) {
    $reviewer_id = $_POST['reviewer_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Disable foreign key checks if necessary
        $conn->query("SET FOREIGN_KEY_CHECKS=0");

        // Step 1: Get all question IDs related to the reviewer
        $qry = $conn->query("SELECT rw_question_id FROM rw_questions WHERE reviewer_id = '$reviewer_id'");
        $question_ids = [];
        while ($row = $qry->fetch_assoc()) {
            $question_ids[] = $row['rw_question_id'];
        }

        if (!empty($question_ids)) {
            $question_ids_str = implode(',', $question_ids);

            // Step 2: Delete all options related to the questions
            $conn->query("DELETE FROM rw_question_opt WHERE rw_question_id IN ($question_ids_str)");

            // Step 3: Delete all identifications related to the questions
            $conn->query("DELETE FROM rw_question_identifications WHERE rw_question_id IN ($question_ids_str)");

            // Step 4: Delete all questions related to the reviewer
            $conn->query("DELETE FROM rw_questions WHERE rw_question_id IN ($question_ids_str)");
        }

        // Step 5. Delete student results associated with the reviewer
        $delete_result_query = "DELETE FROM rw_student_results WHERE reviewer_id = ?";
        $delete_result_stmt = $conn->prepare($delete_result_query);
        $delete_result_stmt->bind_param("i", $reviewer_id);
        $delete_result_stmt->execute();
        $delete_result_stmt->close();
                
        // Step 6. Delete student submissions
        $delete_submission_query = "DELETE FROM rw_student_submission WHERE reviewer_id = ?";
        $delete_submission_stmt = $conn->prepare($delete_submission_query);
        $delete_submission_stmt->bind_param("i", $reviewer_id);
        $delete_submission_stmt->execute();
        $delete_submission_stmt->close();
        
        // Step 7. Delete the shared reviewer
        $delete_shared_query = "DELETE FROM user_reviewers WHERE reviewer_id = ?";
        $delete_shared_stmt = $conn->prepare($delete_shared_query);
        $delete_shared_stmt->bind_param("i", $reviewer_id);
        $delete_shared_stmt->execute();
        $delete_shared_stmt->close();

        // Step 5: Delete the reviewer itself
        $delete_reviewer = $conn->query("DELETE FROM rw_reviewer WHERE reviewer_id = '$reviewer_id'");

        if ($delete_reviewer) {

            // Re-enable foreign key checks
             $conn->query("SET FOREIGN_KEY_CHECKS=1");

            // Commit the transaction if everything was successful
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Error deleting reviewer.');
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
