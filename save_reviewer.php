<?php
include('db_connect.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reviewer_type = $_POST['reviewer_type'];
    $reviewer_name = $_POST['reviewer_name'];
    $topic = $_POST['topic'];
    $student_id = $_SESSION['login_id'];

    // Prepare the SQL statement
    $sql = "INSERT INTO rw_reviewer (student_id, reviewer_name, topic, reviewer_type) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $student_id, $reviewer_name, $topic, $reviewer_type);

    // Execute query and check for errors
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Reviewer saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();
    exit();
}
?>