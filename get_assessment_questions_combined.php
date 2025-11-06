<?php
include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    // Accept JSON body { assessment_ids: [] } OR form data with assessment_id/assessment_ids
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    $assessmentIds = [];

    if (is_array($input)) {
        if (!empty($input['assessment_ids']) && is_array($input['assessment_ids'])) {
            $assessmentIds = array_map('intval', $input['assessment_ids']);
        } elseif (!empty($input['assessment_id'])) {
            $assessmentIds = [intval($input['assessment_id'])];
        }
    }

    // Fallback to standard POST (e.g., form-encoded)
    if (empty($assessmentIds)) {
        if (!empty($_POST['assessment_ids']) && is_array($_POST['assessment_ids'])) {
            $assessmentIds = array_map('intval', $_POST['assessment_ids']);
        } elseif (!empty($_POST['assessment_id'])) {
            $assessmentIds = [intval($_POST['assessment_id'])];
        }
    }
    
    if (empty($assessmentIds)) {
        throw new Exception('No assessments selected');
    }
    
    // Validate that assessments belong to current user
    $placeholders = str_repeat('?,', count($assessmentIds) - 1) . '?';
    $checkQuery = "SELECT assessment_id FROM rw_bank_assessment 
                   WHERE assessment_id IN ($placeholders) AND created_by = ?";
    
    $stmt = $conn->prepare($checkQuery);
    $types = str_repeat('i', count($assessmentIds)) . 'i';
    $params = array_merge($assessmentIds, [$_SESSION['login_id']]);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== count($assessmentIds)) {
        throw new Exception('Invalid assessment selection');
    }
    $stmt->close();
    
    // Get all questions from selected assessments with details
    $questionsQuery = "
        SELECT DISTINCT
            q.question_id,
            q.question_text,
            q.question_type,
            q.difficulty,
            q.total_points,
            a.assessment_title,
            CASE 
                WHEN q.question_type = '1' THEN 'Multiple Choice'
                WHEN q.question_type = '2' THEN 'Checkbox'
                WHEN q.question_type = '3' THEN 'True or False'
                WHEN q.question_type = '4' THEN 'Identification'
                WHEN q.question_type = '5' THEN 'Fill in the Blank'
                ELSE 'Unknown'
            END as type_name,
            CASE 
                WHEN q.difficulty = '1' THEN 'Easy'
                WHEN q.difficulty = '2' THEN 'Medium'
                WHEN q.difficulty = '3' THEN 'Hard'
                ELSE 'Unknown'
            END as difficulty_name
        FROM rw_bank_question q
        INNER JOIN rw_bank_assessment_question aq ON q.question_id = aq.question_id
        INNER JOIN rw_bank_assessment a ON aq.assessment_id = a.assessment_id
        WHERE aq.assessment_id IN ($placeholders)
        ORDER BY a.assessment_title, q.question_id
    ";
    
    $stmt = $conn->prepare($questionsQuery);
    $types = str_repeat('i', count($assessmentIds));
    $stmt->bind_param($types, ...$assessmentIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

