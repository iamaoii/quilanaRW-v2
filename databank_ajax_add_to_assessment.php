<?php
// databank_ajax_add_to_assessment.php
include 'db_connect.php';
include 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Retrieve POST
$assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
$question_ids = isset($_POST['question_ids']) && is_array($_POST['question_ids']) ? $_POST['question_ids'] : [];

if ($assessment_id <= 0 || empty($question_ids)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters.']);
    exit;
}

// Sanitize question ids to ints
$question_ids = array_map('intval', $question_ids);

// Verify that assessment exists and belongs to current user
$check = $conn->prepare("SELECT created_by FROM rw_bank_assessment WHERE assessment_id = ?");
$check->bind_param("i", $assessment_id);
$check->execute();
$check->bind_result($created_by);
if (!$check->fetch()) {
    $check->close();
    echo json_encode(['success' => false, 'message' => 'Assessment not found.']);
    exit;
}
$check->close();

// Optional: allow if the user is the creator only
if ($created_by != $_SESSION['login_id']) {
    echo json_encode(['success' => false, 'message' => 'You are not allowed to modify this assessment.']);
    exit;
}

$conn->begin_transaction();

try {
    // Prepare statements
    $exists_stmt = $conn->prepare("SELECT 1 FROM rw_bank_assessment_question WHERE assessment_id = ? AND question_id = ? LIMIT 1");
    $insert_stmt = $conn->prepare("INSERT INTO rw_bank_assessment_question (assessment_id, question_id) VALUES (?, ?)");

    $inserted = 0;
    foreach ($question_ids as $qid) {
        $qid = intval($qid);
        if ($qid <= 0) continue;

        // check if pair exists
        $exists_stmt->bind_param("ii", $assessment_id, $qid);
        $exists_stmt->execute();
        $exists_stmt->store_result();
        if ($exists_stmt->num_rows > 0) {
            continue; // already exists, skip
        }
        // insert
        $insert_stmt->bind_param("ii", $assessment_id, $qid);
        $insert_stmt->execute();
        if ($insert_stmt->affected_rows > 0) $inserted++;
    }

    // Update no_of_questions on rw_bank_assessment
    $update_count = $conn->prepare("UPDATE rw_bank_assessment SET no_of_questions = (SELECT COUNT(*) FROM rw_bank_assessment_question WHERE assessment_id = ?) WHERE assessment_id = ?");
    $update_count->bind_param("ii", $assessment_id, $assessment_id);
    $update_count->execute();

    $conn->commit();

    echo json_encode(['success' => true, 'message' => "Added {$inserted} new question(s) to the assessment.", 'inserted' => $inserted]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}