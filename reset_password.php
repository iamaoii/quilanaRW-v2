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
$new_password = $_POST['new_password'] ?? '';
$faculty_number = trim($_POST['faculty_number'] ?? '');
$student_number = trim($_POST['student_number'] ?? '');

// Validate inputs
if (empty($user_type) || empty($username) || empty($webmail) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

// Validate password strength
$password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/';
if (!preg_match($password_regex, $new_password)) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters']);
    exit;
}

try {
    // Verify user exists and get user_id
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
    
    // Check if there's an approved password reset request
    $request_stmt = $conn->prepare("SELECT request_id FROM password_reset_requests WHERE user_type = ? AND user_id = ? AND status = 'approved' ORDER BY date_approved DESC LIMIT 1");
    $request_stmt->bind_param("ii", $user_type, $user_id);
    $request_stmt->execute();
    $request_result = $request_stmt->get_result();
    
    if ($request_result->num_rows > 0) {
        $request = $request_result->fetch_assoc();
        $request_id = $request['request_id'];
        
        // Check if this request is already completed
        $check_completed_stmt = $conn->prepare("SELECT request_id FROM password_reset_requests WHERE request_id = ? AND status = 'completed'");
        $check_completed_stmt->bind_param("i", $request_id);
        $check_completed_stmt->execute();
        $check_completed_result = $check_completed_stmt->get_result();
        
        if ($check_completed_result->num_rows > 0) {
            $check_completed_stmt->close();
            $request_stmt->close();
            echo json_encode(['success' => false, 'message' => 'This password reset request has already been completed. Please submit a new request if needed.']);
            exit;
        }
        $check_completed_stmt->close();
        $request_stmt->close();
        
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update user password
            if ($user_type == 2) {
                $update_stmt = $conn->prepare("UPDATE faculty SET password = ? WHERE faculty_id = ?");
            } elseif ($user_type == 3) {
                $update_stmt = $conn->prepare("UPDATE student SET password = ? WHERE student_id = ?");
            } else {
                throw new Exception('Invalid user type');
            }
            
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if (!$update_stmt->execute()) {
                throw new Exception('Failed to update password');
            }
            $update_stmt->close();
            
            // Mark request as completed (only if still approved)
            $update_request_stmt = $conn->prepare("UPDATE password_reset_requests SET status = 'completed', date_processed = NOW() WHERE request_id = ? AND status = 'approved'");
            $update_request_stmt->bind_param("i", $request_id);
            $update_request_stmt->execute();
            
            if ($update_request_stmt->affected_rows === 0) {
                throw new Exception('Request has already been processed or is no longer approved');
            }
            $update_request_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Password set successfully! You can now sign in with your new password.'
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No approved password reset found. Please wait for admin approval or submit a new request.']);
        $request_stmt->close();
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>
