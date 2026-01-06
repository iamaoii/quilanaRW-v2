<?php
include 'db_connect.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is faculty/admin
if (!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$request_id = intval($_POST['request_id'] ?? 0);

if (empty($action) || empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    if ($action === 'reject') {
        // Check if request exists and is still pending
        $check_stmt = $conn->prepare("SELECT request_id, status FROM password_reset_requests WHERE request_id = ?");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $check_stmt->close();
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            exit;
        }
        
        $request_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($request_data['status'] != 'pending') {
            echo json_encode(['success' => false, 'message' => 'This request has already been processed. Current status: ' . ucfirst($request_data['status'])]);
            exit;
        }
        
        // Reject the request
        $stmt = $conn->prepare("UPDATE password_reset_requests SET status = 'rejected', date_processed = NOW() WHERE request_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $request_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject request. It may have been processed already.']);
        }
        $stmt->close();
        
    } elseif ($action === 'approve') {
        // Check if request exists and is still pending
        $check_stmt = $conn->prepare("SELECT request_id, status, user_type, user_id FROM password_reset_requests WHERE request_id = ?");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $check_stmt->close();
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            exit;
        }
        
        $request_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($request_data['status'] != 'pending') {
            echo json_encode(['success' => false, 'message' => 'This request has already been processed. Current status: ' . ucfirst($request_data['status'])]);
            exit;
        }
        
        // Check if user already has an approved request
        $check_approved_stmt = $conn->prepare("SELECT request_id FROM password_reset_requests WHERE user_type = ? AND user_id = ? AND status = 'approved' AND request_id != ? LIMIT 1");
        $check_approved_stmt->bind_param("iii", $request_data['user_type'], $request_data['user_id'], $request_id);
        $check_approved_stmt->execute();
        $check_approved_result = $check_approved_stmt->get_result();
        
        if ($check_approved_result->num_rows > 0) {
            $check_approved_stmt->close();
            echo json_encode(['success' => false, 'message' => 'This user already has an approved password reset request. Please wait for them to complete it first.']);
            exit;
        }
        $check_approved_stmt->close();
        
        // Just approve the request - user will set their own password
        $update_request_stmt = $conn->prepare("UPDATE password_reset_requests SET status = 'approved', date_approved = NOW(), date_processed = NOW() WHERE request_id = ? AND status = 'pending'");
        $update_request_stmt->bind_param("i", $request_id);
        
        if ($update_request_stmt->execute() && $update_request_stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Password reset request approved successfully! User can now set their new password.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve request. It may have been processed already.']);
        }
        $update_request_stmt->close();
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>

