<?php
include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    $question_id = $_GET['question_id'] ?? '';
    
    if (empty($question_id)) {
        throw new Exception('Question ID is required');
    }

    // Get question details
    $question_stmt = $conn->prepare("SELECT * FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
    $question_stmt->bind_param("ii", $question_id, $_SESSION['login_id']);
    $question_stmt->execute();
    $question_result = $question_stmt->get_result();
    
    if ($question_result->num_rows === 0) {
        throw new Exception('Question not found or access denied');
    }
    
    $question = $question_result->fetch_assoc();
    $question_stmt->close();

    $response = ['success' => true, 'question' => $question];

    // Get options or answer based on question type
    if (in_array($question['question_type'], ['1', '2', '3'])) {
        // Get options for MCQ, Checkbox, True/False
        $options_stmt = $conn->prepare("SELECT * FROM rw_bank_question_option WHERE question_id = ? ORDER BY option_id ASC");
        $options_stmt->bind_param("i", $question_id);
        $options_stmt->execute();
        $options_result = $options_stmt->get_result();
        $response['options'] = $options_result->fetch_all(MYSQLI_ASSOC);
        $options_stmt->close();
    } else {
        // Get answer for Identification/Fill blank
        $answer_stmt = $conn->prepare("SELECT * FROM rw_bank_question_answer WHERE question_id = ?");
        $answer_stmt->bind_param("i", $question_id);
        $answer_stmt->execute();
        $answer_result = $answer_stmt->get_result();
        $response['answer'] = $answer_result->fetch_assoc();
        $answer_stmt->close();
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>