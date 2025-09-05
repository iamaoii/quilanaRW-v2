<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('db_connect.php');

// Check database connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

// Check if the user is logged in
if (!isset($_SESSION['login_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit;
}

// Check if the code is set and sanitize input
if (isset($_POST['get_code']) && !empty(trim($_POST['get_code']))) {
    $code = trim($_POST['get_code']);

    // Log POST data (for debugging purposes)
    error_log(print_r($_POST, true));

    // Query the reviewer using the provided code
    $query = $conn->prepare("SELECT reviewer_id, reviewer_name, topic, reviewer_type FROM rw_reviewer WHERE reviewer_code = ? LIMIT 1");
    $query->bind_param("s", $code);
    
    if (!$query->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'SQL execution error: ' . $query->error]);
        exit;
    }

    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $reviewer = $result->fetch_assoc();

        // Check if the user is the owner of the reviewer
        $student_id = $_SESSION['login_id']; // User's ID from the session
        
        $ownerCheckQuery = $conn->prepare("SELECT * FROM rw_reviewer WHERE reviewer_id = ? AND student_id = ?");
        $ownerCheckQuery->bind_param("ii", $reviewer['reviewer_id'], $student_id);
        $ownerCheckQuery->execute();
        $ownerCheckResult = $ownerCheckQuery->get_result();

        if ($ownerCheckResult->num_rows > 0) {
            // User is the owner of the reviewer
            $response = [
                'status' => 'error',
                'message' => 'You cannot access your own reviewer.'
            ];
        } else {
            // Check if the user already has this reviewer in their user_reviewers list
            $checkQuery = $conn->prepare("SELECT * FROM user_reviewers WHERE reviewer_id = ? AND student_id = ?");
            $checkQuery->bind_param("ii", $reviewer['reviewer_id'], $student_id);
            $checkQuery->execute();
            $checkResult = $checkQuery->get_result();

            if ($checkResult->num_rows > 0) {
                // User already has this reviewer in their user_reviewers list
                $response = [
                    'status' => 'error',
                    'message' => 'You already have this reviewer in your list.'
                ];
            } else {
                // Prepare to copy the reviewer into the user_reviewers table
                $insertQuery = $conn->prepare("INSERT INTO user_reviewers (reviewer_id, reviewer_name, topic, reviewer_type, student_id) VALUES (?, ?, ?, ?, ?)");
                $insertQuery->bind_param("issii", $reviewer['reviewer_id'], $reviewer['reviewer_name'], $reviewer['topic'], $reviewer['reviewer_type'], $student_id);

                if ($insertQuery->execute()) {
                    $response = [
                        'status' => 'success',
                        'reviewer_id' => $reviewer['reviewer_id'],
                        'reviewer_name' => $reviewer['reviewer_name'],
                        'topic' => $reviewer['topic'],
                        'reviewer_type' => $reviewer['reviewer_type'],
                        'message' => 'The shared reviewer is now available for you.'
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Insert error: ' . $insertQuery->error
                    ];
                }
                $insertQuery->close();
            }

            $checkQuery->close();
        }
        
        $ownerCheckQuery->close();
    } else {
        $response = ['status' => 'error', 'message' => 'No reviewer found with the given code.'];
    }

    $query->close();
    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No valid code provided.']);
}
?>
