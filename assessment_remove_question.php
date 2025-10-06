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

// Verify the assessment belongs to the current user
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

// Fetch questions linked to this assessment
$query = "
    SELECT q.question_id, q.question_text, q.question_type, q.difficulty 
    FROM rw_bank_question q 
    INNER JOIN rw_bank_assessment_question aq ON q.question_id = aq.question_id 
    WHERE aq.assessment_id = ? 
    ORDER BY aq.date_added ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$type_map = [
    '1' => 'Multiple Choice',
    '2' => 'Checkbox',
    '3' => 'True or False',
    '4' => 'Identification',
    '5' => 'Fill in the Blank'
];

$questions = [];
while ($row = $result->fetch_assoc()) {
    $question = [
        'question' => $row['question_text'],
        'type' => $type_map[$row['question_type']] ?? 'Unknown',
        'points' => 1, // Default points since your import expects this field
        'correct_answer' => null,
        'options' => [] // Always include options array for compatibility
    ];

    switch ($row['question_type']) {
        case '1': // Multiple Choice
        case '2': // Checkbox
            $options = [];
            $correct_answers = [];
            $opt_query = "SELECT option_text, is_correct FROM rw_bank_question_option WHERE question_id = ?";
            $opt_stmt = $conn->prepare($opt_query);
            $opt_stmt->bind_param("i", $row['question_id']);
            $opt_stmt->execute();
            $opt_result = $opt_stmt->get_result();
            while ($opt_row = $opt_result->fetch_assoc()) {
                $options[] = $opt_row['option_text'];
                if ($opt_row['is_correct']) {
                    $correct_answers[] = $opt_row['option_text'];
                }
            }
            $opt_stmt->close();

            $question['options'] = $options;
            $question['correct_answer'] = ($row['question_type'] == '2') ? $correct_answers : ($correct_answers[0] ?? null);
            break;

        case '3': // True or False
            $options = ['True', 'False']; // Explicitly set True/False options
            $correct_answers = [];
            $opt_query = "SELECT option_text, is_correct FROM rw_bank_question_option WHERE question_id = ?";
            $opt_stmt = $conn->prepare($opt_query);
            $opt_stmt->bind_param("i", $row['question_id']);
            $opt_stmt->execute();
            $opt_result = $opt_stmt->get_result();
            while ($opt_row = $opt_result->fetch_assoc()) {
                if ($opt_row['is_correct']) {
                    $correct_answers[] = $opt_row['option_text'];
                }
            }
            $opt_stmt->close();

            $question['options'] = $options;
            $question['correct_answer'] = $correct_answers[0] ?? null;
            break;

        case '4': // Identification
        case '5': // Fill in the Blank
            $ans_query = "SELECT correct_answer FROM rw_bank_question_answer WHERE question_id = ?";
            $ans_stmt = $conn->prepare($ans_query);
            $ans_stmt->bind_param("i", $row['question_id']);
            $ans_stmt->execute();
            $ans_result = $ans_stmt->get_result();
            $answers = [];
            while ($ans_row = $ans_result->fetch_assoc()) {
                $answers[] = $ans_row['correct_answer'];
            }
            $ans_stmt->close();
            $question['correct_answer'] = (count($answers) > 1) ? $answers : ($answers[0] ?? null);
            break;
    }

    $questions[] = $question;
}

$output = [
    'exam_title' => $exam_title,
    'time_limit' => 0,
    'passing_rate' => 0,
    'max_warnings' => 0,
    'questions' => $questions
];

// Sanitize exam title for filename
$filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam_title);
$filename = strtolower($filename);

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="' . $filename . '.json"');

echo json_encode($output, JSON_PRETTY_PRINT);
exit;
?>