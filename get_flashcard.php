<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $flashcard_id = $_GET['flashcard_id'];

    // Prepare and execute the SQL statement
    $sql = "SELECT flashcard_id, term, definition FROM rw_flashcard WHERE flashcard_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $flashcard_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $flashcard = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $flashcard]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Flashcard not found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
