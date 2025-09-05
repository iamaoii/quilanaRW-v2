<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $todo_id = $_POST['todo_id'];

    $stmt = $conn->prepare("
        DELETE FROM rw_student_todo
        WHERE todo_id = ?
    ");
    $stmt->bind_param("i", $todo_id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $conn->close();
}
?>