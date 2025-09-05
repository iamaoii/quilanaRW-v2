<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reviewer_id = $_POST['reviewer_id'];
    $reviewer_type = $_POST['reviewer_type']; 
    $reviewer_name = $_POST['reviewer_name'];
    $topic = $_POST['topic'];

    $query = "UPDATE rw_reviewer SET 
              reviewer_type = ?, 
              reviewer_name = ?, 
              topic = ? 
              WHERE reviewer_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $reviewer_type, $reviewer_name, $topic, $reviewer_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reviewer updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating reviewer: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>