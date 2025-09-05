<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $todo_date = $_POST['todo_date'];
    $student_id = $_POST['student_id'];
    $todo_text = $_POST['todo_text'];

    $stmt = $conn->prepare("
        INSERT INTO rw_student_todo (student_id, todo_text, todo_date) 
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $student_id, $todo_text, $todo_date);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>