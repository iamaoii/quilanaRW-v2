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
    $question_id = (int)($_POST['id'] ?? 0);
    $topic_id = (int)($_POST['topic_id'] ?? 0);
    $question_text = trim((string)($_POST['question_text'] ?? ''));
    $question_type = (string)($_POST['question_type'] ?? '');
    $difficulty = (string)($_POST['difficulty'] ?? '1');
    $total_points = max(1, min(100, (int)($_POST['points'] ?? 1)));
    $created_by = $_SESSION['login_id'];
<<<<<<< Updated upstream
=======
    $is_admin = isset($_SESSION['login_username']) && $_SESSION['login_username'] === 'admin';
>>>>>>> Stashed changes

    if (empty($question_id) || empty($topic_id) || empty($question_text) || empty($question_type)) {
        throw new Exception('Missing required fields');
    }

    if (!in_array($question_type, ['1','2','3','4','5'], true)) {
        throw new Exception('Invalid question type');
    }
    if (!in_array($difficulty, ['1','2','3'], true)) {
        throw new Exception('Invalid difficulty value');
    }

<<<<<<< Updated upstream
    $check_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
    $check_stmt->bind_param("ii", $question_id, $created_by);
=======
    // Only allow the creator to update the question (or admin)
    if ($is_admin) {
        $check_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE question_id = ?");
        $check_stmt->bind_param("i", $question_id);
    } else {
        $check_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE question_id = ? AND created_by = ?");
        $check_stmt->bind_param("ii", $question_id, $created_by);
    }
>>>>>>> Stashed changes
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows === 0) {
        throw new Exception('Question not found or access denied');
    }
    $check_stmt->close();

    $conn->begin_transaction();
    $dup_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE topic_id = ? AND TRIM(LOWER(question_text)) = TRIM(LOWER(?)) AND question_type = ? AND question_id <> ? LIMIT 1");
    $dup_stmt->bind_param("issi", $topic_id, $question_text, $question_type, $question_id);
    $dup_stmt->execute();
    $dup_res = $dup_stmt->get_result();
    if ($dup_res && $dup_res->num_rows > 0) {
        throw new Exception('A question with the same text already exists in this topic.');
    }
    $dup_stmt->close();

    $stmt = $conn->prepare("
        UPDATE rw_bank_question 
        SET question_text = ?, question_type = ?, difficulty = ?, total_points = ?, date_updated = NOW() 
        WHERE question_id = ?
    ");
    $stmt->bind_param("sssii", $question_text, $question_type, $difficulty, $total_points, $question_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update question: ' . $stmt->error);
    }
    $stmt->close();

    $delete_options = $conn->prepare("DELETE FROM rw_bank_question_option WHERE question_id = ?");
    $delete_options->bind_param("i", $question_id);
    $delete_options->execute();
    $delete_options->close();

    $delete_answers = $conn->prepare("DELETE FROM rw_bank_question_answer WHERE question_id = ?");
    $delete_answers->bind_param("i", $question_id);
    $delete_answers->execute();
    $delete_answers->close();

    switch ($question_type) {
        case '1':
        case '2':
            if (!isset($_POST['question_opt']) || !is_array($_POST['question_opt'])) {
                throw new Exception('No options provided');
            }

            $rawOptions = $_POST['question_opt'];
            $options = [];
            $seen = [];
            foreach ($rawOptions as $opt) {
                $clean = trim(preg_replace('/\s+/', ' ', (string)$opt));
                if ($clean === '') continue;
                if (isset($seen[$clean])) continue;
                $seen[$clean] = true;
                $options[] = $clean;
            }

            if (count($options) < 2 && in_array($question_type, ['1','2'], true)) {
                throw new Exception('Please provide at least two unique options');
            }
            
            $option_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, ?, ?)
            ");

            if ($question_type === '1') {
                $correct_value = isset($_POST['is_right']) ? intval($_POST['is_right']) : -1;
                $position = 1;
                foreach ($options as $option_text) {
                    $option_text = trim($option_text);
                    if (empty($option_text)) continue;
                    
                    $is_correct = ($position == $correct_value) ? 1 : 0;
                    $option_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                    $option_stmt->execute();
                    $position++;
                }
                
            } else {
                $correct_values = isset($_POST['is_right']) && is_array($_POST['is_right']) 
                    ? array_map('intval', $_POST['is_right']) 
                    : [];
                
                if (empty($correct_values)) {
                    throw new Exception('Please select at least one correct answer');
                }
                
                $position = 1;
                foreach ($options as $option_text) {
                    $option_text = trim($option_text);
                    if (empty($option_text)) continue;
                    
                    $is_correct = in_array($position, $correct_values, true) ? 1 : 0;
                    $option_stmt->bind_param("isi", $question_id, $option_text, $is_correct);
                    $option_stmt->execute();
                    $position++;
                }
            }
            
            $option_stmt->close();
            break;

        case '3':
            $tf_answer = $_POST['tf_answer'] ?? '';
            if (!in_array($tf_answer, ['true', 'false'])) {
                throw new Exception('Please select true or false answer');
            }

            $true_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, 'True', ?)
            ");
            $is_true_correct = ($tf_answer === 'true') ? 1 : 0;
            $true_stmt->bind_param("ii", $question_id, $is_true_correct);
            $true_stmt->execute();
            $true_stmt->close();

            $false_stmt = $conn->prepare("
                INSERT INTO rw_bank_question_option (question_id, option_text, is_correct) 
                VALUES (?, 'False', ?)
            ");
            $is_false_correct = ($tf_answer === 'false') ? 1 : 0;
            $false_stmt->bind_param("ii", $question_id, $is_false_correct);
            $false_stmt->execute();
            $false_stmt->close();
            break;

        case '4':
        case '5':
            $answer_field = ($question_type === '4') ? 'identification_answer' : 'fill_blank_answer';
            $correct_answer = trim($_POST[$answer_field] ?? '');
            
            if (empty($correct_answer)) {
                throw new Exception('Please provide a correct answer');
            }

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

    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Question updated successfully',
        'question_id' => $question_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>