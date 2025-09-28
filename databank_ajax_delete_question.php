<?php
include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    $question_id = $_POST['question_id'] ?? '';
    
    if (empty($question_id)) {
        throw new Exception('Question ID is required');
    }

    // Start transaction
    $conn->begin_transaction();

    // First, check if question exists and belongs to user
    $check_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
    $check_stmt->bind_param("ii", $question_id, $_SESSION['login_id']);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Question not found or access denied');
    }
    $check_stmt->close();

    // Delete options/answers first (due to foreign key constraints)
    $delete_options = $conn->prepare("DELETE FROM rw_bank_question_option WHERE question_id = ?");
    $delete_options->bind_param("i", $question_id);
    $delete_options->execute();
    $delete_options->close();

    $delete_answers = $conn->prepare("DELETE FROM rw_bank_question_answer WHERE question_id = ?");
    $delete_answers->bind_param("i", $question_id);
    $delete_answers->execute();
    $delete_answers->close();

    // Delete the question
    $delete_question = $conn->prepare("DELETE FROM rw_bank_question WHERE question_id = ?");
    $delete_question->bind_param("i", $question_id);
    
    if (!$delete_question->execute()) {
        throw new Exception('Failed to delete question: ' . $delete_question->error);
    }
    $delete_question->close();

    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>