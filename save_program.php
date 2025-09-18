<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in
if (!isset($_SESSION['login_user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_name = trim($_POST['program_name']);
    $faculty_id = $_SESSION['login_id'];

    // Validate input
    if (empty($program_name)) {
        echo json_encode(['success' => false, 'message' => 'Program name is required']);
        exit();
    }

    // Check if program name already exists for this faculty
    $check_query = $conn->prepare("SELECT COUNT(*) as count FROM program WHERE program_name = ? AND faculty_id = ?");
    $check_query->bind_param("si", $program_name, $faculty_id);
    $check_query->execute();
    $result = $check_query->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'A program with this name already exists']);
        exit();
    }

    // Insert new program
    $stmt = $conn->prepare("INSERT INTO program (program_name, faculty_id) VALUES (?, ?)");
    $stmt->bind_param("si", $program_name, $faculty_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save program']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}