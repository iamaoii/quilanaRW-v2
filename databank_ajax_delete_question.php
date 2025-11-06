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

    $conn->begin_transaction();

    $check_stmt = $conn->prepare("SELECT question_id, topic_id FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
    $check_stmt->bind_param("ii", $question_id, $_SESSION['login_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Question not found or access denied');
    }

    $row = $result->fetch_assoc();
    $topic_id = $row['topic_id'];
    $check_stmt->close();

    $delete_options = $conn->prepare("DELETE FROM rw_bank_question_option WHERE question_id = ?");
    $delete_options->bind_param("i", $question_id);
    $delete_options->execute();
    $delete_options->close();

    $delete_answers = $conn->prepare("DELETE FROM rw_bank_question_answer WHERE question_id = ?");
    $delete_answers->bind_param("i", $question_id);
    $delete_answers->execute();
    $delete_answers->close();

    $delete_question = $conn->prepare("DELETE FROM rw_bank_question WHERE question_id = ?");
    $delete_question->bind_param("i", $question_id);
    
    if (!$delete_question->execute()) {
        throw new Exception('Failed to delete question: ' . $delete_question->error);
    }
    $delete_question->close();

    // Update no_of_questions in rw_bank_topic
    $update_stmt = $conn->prepare("
        UPDATE rw_bank_topic 
        SET no_of_questions = (SELECT COUNT(*) FROM rw_bank_question WHERE topic_id = ?) 
        WHERE topic_id = ?
    ");
    $update_stmt->bind_param("ii", $topic_id, $topic_id);
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update topic question count: ' . $update_stmt->error);
    }
    $update_stmt->close();

    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Question deleted successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>