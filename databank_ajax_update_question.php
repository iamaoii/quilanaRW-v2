<?php
include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

header('Content-Type: application/json');

try {
    // Get form data
    $question_id = $_POST['id'] ?? '';
    $topic_id = $_POST['topic_id'] ?? '';
    $question_text = trim($_POST['question_text'] ?? '');
    $question_type = $_POST['question_type'] ?? '';
    $difficulty = $_POST['difficulty'] ?? 'medium';
    $created_by = $_SESSION['login_id'];

    // Basic validation
    if (empty($question_id) || empty($topic_id) || empty($question_text) || empty($question_type)) {
        throw new Exception('Missing required fields');
    }

    // Verify the question exists and belongs to the user
    $check_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
    $check_stmt->bind_param("ii", $question_id, $created_by);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Question not found or access denied');
    }
    $check_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Update the question
    $stmt = $conn->prepare("
        UPDATE rw_bank_question 
        SET question_text = ?, question_type = ?, difficulty = ?, date_updated = NOW() 
        WHERE question_id = ?
    ");
    $stmt->bind_param("sssi", $question_text, $question_type, $difficulty, $question_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update question: ' . $stmt->error);
    }
    $stmt->close();

    // Delete existing options/answers
    $delete_options = $conn->prepare("DELETE FROM rw_bank_question_option WHERE question_id = ?");
    $delete_options->bind_param("i", $question_id);
    $delete_options->execute();
    $delete_options->close();

    $delete_answers = $conn->prepare("DELETE FROM rw_bank_question_answer WHERE question_id = ?");
    $delete_answers->bind_param("i", $question_id);
    $delete_answers->execute();
    $delete_answers->close();

    // Handle different question types
    switch ($question_type) {
        case '1': // Multiple Choice
        case '2': // Checkbox
            // Handle multiple choice and checkbox options
            if (!isset($_POST['question_opt']) || !is_array($_POST['question_opt'])) {
                throw new Exception('No options provided for multiple choice/checkbox question');
            }

            $options = $_POST['question_opt'];
            $correct_answers = [];
            
            if ($question_type === '1') { // Multiple Choice
                // For MCQ, get the single correct answer index
                $correct_index = $_POST['is_right'] ?? '';
                if ($correct_index === '') {
                    throw new Exception('Please select a correct answer for multiple choice question');
                }
                $correct_answers[] = intval($correct_index);
            } else { // Checkbox (2)
                // For checkbox, get array of correct answers
                $correct_answers = $_POST['is_right'] ?? [];
                if (empty($correct_answers)) {
                    throw new Exception('Please select at least one correct answer for checkbox question');
                }
            }

            // Insert options
            $option_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, ?, ?)
            ");

            foreach ($options as $index => $option_text) {
                $option_text = trim($option_text);
                if (empty($option_text)) continue;

                $is_correct = 0;
                if ($question_type === '1') { // Multiple Choice
                    $is_correct = ($index == $correct_index) ? 1 : 0;
                } else { // Checkbox
                    $is_correct = in_array($index, $correct_answers) ? 1 : 0;
                }

                $option_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                if (!$option_stmt->execute()) {
                    throw new Exception('Failed to save option: ' . $option_stmt->error);
                }
            }
            $option_stmt->close();
            break;

        case '3': // True or False
            // Handle true/false options
            $tf_answer = $_POST['tf_answer'] ?? '';
            if (!in_array($tf_answer, ['true', 'false'])) {
                throw new Exception('Please select true or false answer');
            }

            // Insert true option
            $true_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, 'True', ?)
            ");
            $is_true_correct = ($tf_answer === 'true') ? 1 : 0;
            $true_stmt->bind_param("ii", $question_id, $is_true_correct);
            $true_stmt->execute();
            $true_stmt->close();

            // Insert false option
            $false_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, 'False', ?)
            ");
            $is_false_correct = ($tf_answer === 'false') ? 1 : 0;
            $false_stmt->bind_param("ii", $question_id, $is_false_correct);
            $false_stmt->execute();
            $false_stmt->close();
            break;

        case '4': // Identification
        case '5': // Fill in the Blank
            // Handle identification and fill in the blank
            $answer_field = ($question_type === '4') ? 'identification_answer' : 'fill_blank_answer';
            $correct_answer = trim($_POST[$answer_field] ?? '');
            
            if (empty($correct_answer)) {
                throw new Exception('Please provide a correct answer');
            }

            // Insert answer
            $answer_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_answer (question_id, correct_answer) 
                VALUES (?, ?)
            ");
            $answer_stmt->bind_param("is", $question_id, $correct_answer);
            
            if (!$answer_stmt->execute()) {
                throw new Exception('Failed to save answer: ' . $answer_stmt->error);
            }
            $answer_stmt->close();
            break;

        default:
            throw new Exception('Invalid question type');
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Question updated successfully',
        'question_id' => $question_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>