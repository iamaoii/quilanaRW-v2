<?php
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_type = $_POST['user_type'] ?? '';
$username = trim($_POST['username'] ?? '');
$webmail = trim($_POST['webmail'] ?? '');
$faculty_number = trim($_POST['faculty_number'] ?? '');
$student_number = trim($_POST['student_number'] ?? '');

// Validate inputs
if (empty($user_type) || empty($username) || empty($webmail)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

try {
    // Check if user exists
    if ($user_type == '2') {
        if (empty($faculty_number)) {
            echo json_encode(['success' => false, 'message' => 'Faculty number is required']);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT faculty_id FROM faculty WHERE username = ? AND webmail = ? AND faculty_number = ?");
        $stmt->bind_param("sss", $username, $webmail, $faculty_number);
    } elseif ($user_type == '3') {
        if (empty($student_number)) {
            echo json_encode(['success' => false, 'message' => 'Student number is required']);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT student_id FROM student WHERE username = ? AND webmail = ? AND student_number = ?");
        $stmt->bind_param("sss", $username, $webmail, $student_number);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No account found with the provided information. Please verify your credentials.']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $user_id = $user['faculty_id'] ?? $user['student_id'];
    $stmt->close();
    
    // Check if there's already a pending or approved request
    $check_stmt = $conn->prepare("SELECT request_id, status FROM password_reset_requests WHERE user_type = ? AND user_id = ? AND status IN ('pending', 'approved') ORDER BY date_requested DESC LIMIT 1");
    $check_stmt->bind_param("ii", $user_type, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing_request = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($existing_request['status'] == 'pending') {
            echo json_encode(['success' => false, 'message' => 'You already have a pending password reset request. Please wait for admin approval.']);
        } elseif ($existing_request['status'] == 'approved') {
            echo json_encode(['success' => false, 'message' => 'You already have an approved password reset request. Please set your new password first before submitting another request.']);
        }
        exit;
    }
    $check_stmt->close();
    
    // Create password reset request
    $insert_stmt = $conn->prepare("INSERT INTO password_reset_requests (user_type, user_id, username, webmail, status, date_requested) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $insert_stmt->bind_param("iiss", $user_type, $user_id, $username, $webmail);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password reset request submitted successfully! An administrator will process your request. You can check back later to view your new password.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit request. Please try again.']);
    }
    
    $insert_stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>

