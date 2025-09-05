<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve POST data
    $term = $conn->real_escape_string($_POST['term']);
    $definition = $conn->real_escape_string($_POST['definition']);
    $reviewer_id = $conn->real_escape_string($_POST['reviewer_id']); 
    $student_id = $_SESSION['login_id']; 

    // Prepare the SQL statement
    $sql = "INSERT INTO rw_flashcard (term, definition, reviewer_id, student_id) VALUES (?, ?, ?, ?)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param("ssis", $term, $definition, $reviewer_id, $student_id);

    // Execute query and check for errors
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Flashcard saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
