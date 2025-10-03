<?php
include 'db_connect.php';
include 'auth.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

$query = $_GET['q'] ?? '';
$userId = $_SESSION['login_id'];

try {
    $stmt = $conn->prepare("
        SELECT assessment_id, assessment_title 
        FROM rw_bank_assessment 
        WHERE created_by = ? AND assessment_title LIKE CONCAT('%', ?, '%') 
        ORDER BY assessment_title ASC
    ");
    $stmt->bind_param("is", $userId, $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $assessments = [];
    while ($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }

    echo json_encode(["success" => true, "data" => $assessments]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
