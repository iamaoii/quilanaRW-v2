<?php
session_start();
include 'db_connect.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;
    $assessment_title = isset($_POST['assessment_title']) ? trim($_POST['assessment_title']) : '';

    if ($assessment_id <= 0 || empty($assessment_title)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE rw_bank_assessment 
                            SET assessment_title = ? 
                            WHERE assessment_id = ? AND created_by = ?");
    $stmt->bind_param("sii", $assessment_title, $assessment_id, $_SESSION['login_id']);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Assessment updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or unauthorized']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
$conn->close();
