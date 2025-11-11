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
    $topic_id = (int)($_POST['topic_id'] ?? 0);
    $question_text = trim((string)($_POST['question_text'] ?? ''));
    $question_type = (string)($_POST['question_type'] ?? '');
    $difficulty = (string)($_POST['difficulty'] ?? '1');
    $total_points = max(1, min(100, (int)($_POST['points'] ?? 1)));
    $created_by = $_SESSION['login_id'];

    if (empty($topic_id) || empty($question_text) || empty($question_type)) {
        throw new Exception('Missing required fields');
    }

    if (!in_array($question_type, ['1','2','3','4','5'], true)) {
        throw new Exception('Invalid question type');
    }
    if (!in_array($difficulty, ['1','2','3'], true)) {
        throw new Exception('Invalid difficulty value');
    }

    $topic_check = $conn->prepare("SELECT topic_id FROM rw_bank_topic WHERE topic_id = ?");
    $topic_check->bind_param("i", $topic_id);
    $topic_check->execute();
    if ($topic_check->get_result()->num_rows === 0) {
        throw new Exception('Invalid topic ID');
    }
    $topic_check->close();
    $dup_stmt = $conn->prepare("SELECT question_id FROM rw_bank_question WHERE topic_id = ? AND TRIM(LOWER(question_text)) = TRIM(LOWER(?)) AND question_type = ? LIMIT 1");
    $dup_stmt->bind_param("iss", $topic_id, $question_text, $question_type);
    $dup_stmt->execute();
    $dup_res = $dup_stmt->get_result();
    if ($dup_res && $dup_res->num_rows > 0) {
        throw new Exception('A question with the same text already exists in this topic.');
    }
    $dup_stmt->close();

    $conn->begin_transaction();
    $stmt = $conn->prepare("
        INSERT INTO rw_bank_question (topic_id, question_text, question_type, difficulty, created_by, total_points, date_created, date_updated) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->bind_param("isssii", $topic_id, $question_text, $question_type, $difficulty, $created_by, $total_points);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to save question: ' . $stmt->error);
    }
    
    $question_id = $conn->insert_id;
    $stmt->close();

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

            if (count($options) < 2) {
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

    echo json_encode([
        'success' => true, 
        'message' => 'Question saved successfully',
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