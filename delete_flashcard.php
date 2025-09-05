<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $flashcard_id = $_POST['flashcard_id'];

    // Prepare and execute the SQL statement
    $sql = "DELETE FROM rw_flashcard WHERE flashcard_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $flashcard_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Flashcard deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
