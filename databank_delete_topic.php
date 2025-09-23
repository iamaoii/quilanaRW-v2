<?php
include 'db_connect.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic_id = $_POST['topic_id'] ?? null;

    if ($topic_id) {
        $stmt = $conn->prepare("DELETE FROM rw_bank_topic WHERE topic_id = ?");
        $stmt->bind_param("i", $topic_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Missing topic_id"]);
    }
    exit;
}
?>
