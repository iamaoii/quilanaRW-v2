<?php
// databank_ajax_create_assessment.php
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

$assessment_title = isset($_POST['assessment_title']) ? trim($_POST['assessment_title']) : '';
$assessment_type = isset($_POST['assessment_type']) ? trim($_POST['assessment_type']) : '';

if ($assessment_title === '' || $assessment_type === '') {
    echo json_encode(['success' => false, 'message' => 'Please provide assessment title and type.']);
    exit;
}

// Insert
$stmt = $conn->prepare("INSERT INTO rw_bank_assessment (assessment_title, assessment_type, created_by, no_of_questions) VALUES (?, ?, ?, 0)");
$stmt->bind_param("ssi", $assessment_title, $assessment_type, $_SESSION['login_id']);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    echo json_encode(['success' => true, 'message' => 'Assessment created.', 'assessment_id' => $new_id, 'assessment_title' => $assessment_title]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create assessment.']);
}