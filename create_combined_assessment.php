<?php
include 'db_connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['login_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $assessmentName = trim($_POST['assessment_name'] ?? '');
    $assessmentType = $_POST['assessment_type'] ?? '';
    $questionIds = json_decode($_POST['question_ids'] ?? '[]', true);
    
    if (empty($assessmentName)) {
        throw new Exception('Assessment name is required');
    }
    
    if (empty($assessmentType)) {
        throw new Exception('Assessment type is required');
    }
    
    if (empty($questionIds) || !is_array($questionIds)) {
        throw new Exception('No questions selected');
    }
    
    $createdBy = $_SESSION['login_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    // Prevent duplicate assessment titles
    $check = $conn->prepare("SELECT assessment_id FROM rw_bank_assessment WHERE assessment_title = ? AND created_by = ? LIMIT 1");
    $check->bind_param("si", $assessmentName, $createdBy);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        throw new Exception('An assessment with this title already exists. Please choose a different name.');
    }
    $check->close();

    // Create new assessment (schema: assessment_title, assessment_type, created_by, no_of_questions)
    $insertAssessment = "INSERT INTO rw_bank_assessment (assessment_title, assessment_type, created_by, no_of_questions) 
                         VALUES (?, ?, ?, 0)";
    $stmt = $conn->prepare($insertAssessment);
    $stmt->bind_param("sii", $assessmentName, $assessmentType, $createdBy);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create assessment');
    }
    
    $assessmentId = $conn->insert_id;
    $stmt->close();
    
    // Add questions to assessment (table name is singular: rw_bank_assessment_question)
    $insertQuestion = "INSERT INTO rw_bank_assessment_question (assessment_id, question_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuestion);
    
    foreach ($questionIds as $questionId) {
        $questionId = intval($questionId);
        $stmt->bind_param("ii", $assessmentId, $questionId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to add question to assessment');
        }
    }
    $stmt->close();
    
    // Update question count in assessment
    $updateCount = "UPDATE rw_bank_assessment 
                    SET no_of_questions = (
                        SELECT COUNT(*) FROM rw_bank_assessment_question 
                        WHERE assessment_id = ?
                    )
                    WHERE assessment_id = ?";
    $stmt = $conn->prepare($updateCount);
    $stmt->bind_param("ii", $assessmentId, $assessmentId);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Combined assessment created successfully',
        'assessment_id' => $assessmentId
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($conn) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

