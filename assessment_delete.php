<?php
include 'db_connect.php';
include 'auth.php';

header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessmentId = $_POST['assessment_id'] ?? '';

    if (empty($assessmentId)) {
        echo json_encode(["success" => false, "message" => "Missing assessment ID"]);
        exit();
    }

    try {
        // Delete child records first
        $stmt = $conn->prepare("DELETE FROM rw_bank_assessment_question WHERE assessment_id = ?");
        $stmt->bind_param("i", $assessmentId);
        $stmt->execute();
        $stmt->close();

        // Delete the assessment itself (only if user is owner)
        $stmt = $conn->prepare("DELETE FROM rw_bank_assessment WHERE assessment_id = ? AND created_by = ?");
        $stmt->bind_param("ii", $assessmentId, $_SESSION['login_id']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Assessment deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Assessment not found or not yours"]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error deleting assessment: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
