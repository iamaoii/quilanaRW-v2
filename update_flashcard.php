<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $flashcard_id = $_POST['flashcard_id'];
    $term = $_POST['term'];
    $definition = $_POST['definition'];

    // Check if flashcard_id is set, if not, create a new one
    if (!empty($flashcard_id)) {
        // Update existing flashcard
        $sql = "UPDATE rw_flashcard SET term = ?, definition = ? WHERE flashcard_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $term, $definition, $flashcard_id);
    } else {
        // Create new flashcard
        $student_id = $_SESSION['login_id']; // Assuming you need this
        $sql = "INSERT INTO rw_flashcard (student_id, term, definition) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $student_id, $term, $definition);
    }

    // Execute query and check for errors
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Flashcard saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
