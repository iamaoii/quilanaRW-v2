<?php
include('db_connect.php');
include('auth.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

if (!isset($_GET['assessment_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Assessment ID not provided.']);
    exit;
}

$assessment_id = intval($_GET['assessment_id']);
$user_id = $_SESSION['login_id'];

$assessment_query = "SELECT assessment_title FROM rw_bank_assessment WHERE assessment_id = ? AND created_by = ?";
$assessment_stmt = $conn->prepare($assessment_query);
$assessment_stmt->bind_param("ii", $assessment_id, $user_id);
$assessment_stmt->execute();
$assessment_result = $assessment_stmt->get_result();

if ($assessment_result->num_rows == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Assessment not found or access denied.']);
    exit;
}

$assessment = $assessment_result->fetch_assoc();
$exam_title = $assessment['assessment_title'];
$assessment_stmt->close();

$type_map = [
    '1' => 'Multiple Choice',
    '2' => 'Checkbox',
    '3' => 'True or False',
    '4' => 'Identification',
    '5' => 'Fill in the Blank'
];

$difficulty_map = [
    '1' => 'Easy',
    '2' => 'Medium', 
    '3' => 'Hard'
];

$optimized_query = "
    SELECT q.question_id, q.question_text, q.question_type, q.difficulty, q.total_points,
           GROUP_CONCAT(
               DISTINCT CONCAT(qo.option_id, ':::', qo.option_text, ':::', qo.is_correct)
               ORDER BY qo.option_id SEPARATOR '|||'
           ) as options_data,
           GROUP_CONCAT(DISTINCT qa.correct_answer SEPARATOR '|||') as answers_data
    FROM rw_bank_question q 
    INNER JOIN rw_bank_assessment_question aq ON q.question_id = aq.question_id
    LEFT JOIN rw_bank_question_option qo ON q.question_id = qo.question_id
    LEFT JOIN rw_bank_question_answer qa ON q.question_id = qa.question_id
    WHERE aq.assessment_id = ?
    GROUP BY q.question_id
    ORDER BY aq.date_added DESC
";

$optimized_stmt = $conn->prepare($optimized_query);
$optimized_stmt->bind_param("i", $assessment_id);
$optimized_stmt->execute();
$optimized_result = $optimized_stmt->get_result();

$questions = [];
while ($row = $optimized_result->fetch_assoc()) {
    $question = [
        'question' => $row['question_text'],
        'type' => $type_map[$row['question_type']] ?? 'Unknown',
        'points' => intval($row['total_points']),
        'correct_answer' => null
    ];

    switch ($row['question_type']) {
        case '1':
        case '2':
            $options = [];
            $correct_answers = [];
            if (!empty($row['options_data'])) {
                $options_array = explode('|||', $row['options_data']);
                foreach ($options_array as $option_str) {
                    $option_parts = explode(':::', $option_str);
                    if (count($option_parts) >= 3) {
                        $option_text = $option_parts[1];
                        $is_correct = (int)$option_parts[2];
                        $options[] = $option_text;
                        if ($is_correct) {
                            $correct_answers[] = $option_text;
                        }
                    }
                }
            }
            $question['options'] = $options;
            $question['correct_answer'] = ($row['question_type'] == '2') ? $correct_answers : ($correct_answers[0] ?? null);
            break;

        case '3':
            $correct_answer = null;
            if (!empty($row['options_data'])) {
                $options_array = explode('|||', $row['options_data']);
                foreach ($options_array as $option_str) {
                    $option_parts = explode(':::', $option_str);
                    if (count($option_parts) >= 3 && (int)$option_parts[2] == 1) {
                        $option_text = $option_parts[1];
                        if ($option_text == '1') {
                            $correct_answer = 'true';
                        } elseif ($option_text == '0') {
                            $correct_answer = 'false';
                        } else {
                            $correct_answer = strtolower($option_text);
                        }
                        break;
                    }
                }
            }
            $question['correct_answer'] = $correct_answer;
            break;

        case '4':
        case '5':
            $answers = [];
            if (!empty($row['answers_data'])) {
                $answers = explode('|||', $row['answers_data']);
            }
            $question['correct_answer'] = (count($answers) > 1) ? $answers : ($answers[0] ?? null);
            break;
    }

    $questions[] = $question;
}
$optimized_stmt->close();

$output = [
    'exam_title' => $exam_title,
    'time_limit' => 0,
    'passing_rate' => 0,
    'max_warnings' => 0,
    'questions' => $questions
];

$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam_title);
$filename = strtolower($filename);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '.json"');

echo json_encode($output, JSON_PRETTY_PRINT);
exit;
?>