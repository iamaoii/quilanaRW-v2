<?php
include 'db_connect.php';
include 'auth.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); 

if (!isset($_SESSION['login_user_type'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [];

    if (isset($_POST['assessment_name'], $_POST['assessment_type'])) {
        $assessment_name = trim($_POST['assessment_name']);
        $assessment_type = intval($_POST['assessment_type']);
        $created_by = $_SESSION['login_id'];
        $no_of_questions = 0;

        if ($assessment_name === '') {
            $response = ['success' => false, 'message' => 'Assessment name is required'];
        } else {
            $conn->begin_transaction();
            try {
                // Error handling (duplicates)
                $check = $conn->prepare("
                    SELECT assessment_id 
                    FROM rw_bank_assessment 
                    WHERE assessment_title = ?
                ");
                $check->bind_param("s", $assessment_name);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $conn->rollback();
                    $response = [
                        'success' => false,
                        'message' => 'An assessment with this title already exists.'
                    ];
                } else {
                    // Insert new record
                    $insert = $conn->prepare("
                        INSERT INTO rw_bank_assessment 
                            (assessment_title, assessment_type, created_by, no_of_questions) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $insert->bind_param("siii", $assessment_name, $assessment_type, $created_by, $no_of_questions);
                    $insert->execute();
                    $conn->commit();

                    $response = [
                        'success' => true,
                        'message' => 'Assessment added successfully',
                        'assessment_id' => $conn->insert_id
                    ];
                }
            } catch (Exception $e) {
                $conn->rollback();
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }
    } else {
        $response = ['success' => false, 'message' => 'Missing required parameters'];
    }

    echo json_encode($response);
    exit();
}
