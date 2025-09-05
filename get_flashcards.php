<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $reviewer_id = $_GET['reviewer_id']; 

    // Prepare and execute the SQL statement to fetch all flashcards for the reviewer
    $sql = "SELECT flashcard_id, term, definition FROM rw_flashcard WHERE reviewer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reviewer_id); 

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $flashcards = [];

        // Fetch all flashcards
        while ($row = $result->fetch_assoc()) {
            $flashcards[] = $row;
        }

        if (count($flashcards) > 0) {
            echo json_encode(['success' => true, 'data' => $flashcards]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No flashcards found.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
