<?php
include('db_connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assessment_id = $conn->real_escape_string($_POST['assessment_id']);
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $action = $conn->real_escape_string($_POST['action']);

    if ($action === 'upload') {
        $sql = "INSERT INTO assessment_uploads (assessment_id, class_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $assessment_id, $class_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Assessment uploaded successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload assessment: ' . $conn->error]);
        }
    } elseif ($action === 'remove') {
        $sql = "DELETE FROM assessment_uploads WHERE assessment_id = ? AND class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $assessment_id, $class_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Assessment upload removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove assessment upload: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>