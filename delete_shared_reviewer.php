<?php
include('db_connect.php');

if (isset($_POST['shared_id'])) {
    $shared_id = $_POST['shared_id'];

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM user_reviewers WHERE shared_id = ? ");
    $stmt->bind_param("i", $shared_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Reviewer deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete reviewer.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>
