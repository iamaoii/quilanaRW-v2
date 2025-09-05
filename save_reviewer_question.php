<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $question_id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $reviewer_id = intval($_POST['reviewer_id']);
    $question_text = $_POST['question'];
    $question_type = $_POST['question_type'];
    $total_points = intval($_POST['points']);
    
    // Map question type to numeric value
    $ques_type_map = [
        'multiple_choice' => 1,
        'checkbox' => 2,
        'true_false' => 3,
        'identification' => 4,
        'fill_blank' => 5
    ];
    $ques_type = $ques_type_map[$question_type] ?? 0;

    // Validate inputs
    if (empty($question_text) || empty($reviewer_id) || empty($ques_type) || empty($total_points)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill out all required fields.']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($question_id) {
            // Update existing question
            $query = "UPDATE rw_questions SET question = ?, question_type = ?, total_points = ? WHERE rw_question_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siii", $question_text, $ques_type, $total_points, $question_id);
            $stmt->execute();

            // Delete existing options
            $stmt = $conn->prepare("DELETE FROM rw_question_opt WHERE rw_question_id = ?");
            $stmt->bind_param("i", $question_id);
            $stmt->execute();

            // Also delete from rw_question_identifications if applicable
            if ($ques_type === 4 || $ques_type === 5) {
                $stmt = $conn->prepare("DELETE FROM rw_question_identifications WHERE rw_question_id = ?");
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
            }
        } else {
            // Insert new question
            $query = "INSERT INTO rw_questions (question, reviewer_id, question_type, total_points) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siii", $question_text, $reviewer_id, $ques_type, $total_points);
            $stmt->execute();
            $question_id = $stmt->insert_id;
        }

        // Handle options based on question type
        switch ($question_type) {
            case 'multiple_choice':
            case 'checkbox':
                    $options = $_POST['question_opt'] ?? [];
                    $is_right = isset($_POST['is_right']) ? (array)$_POST['is_right'] : [];
    
                    // Delete existing options
                    $delete_options_query = "DELETE FROM rw_question_opt WHERE rw_question_id = ?";
                    $delete_stmt = $conn->prepare($delete_options_query);
                    $delete_stmt->bind_param("i", $question_id);
                    $delete_stmt->execute();
    
                    // Insert new options
                    $insert_option_query = "INSERT INTO rw_question_opt (option_text, is_right, rw_question_id) VALUES (?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_option_query);
    
                    foreach ($options as $index => $option_text) {
                        $option_text = trim($option_text);
                        if (!empty($option_text)) {
                            $is_correct = in_array((string)$index, $is_right) ? 1 : 0;
                            $insert_stmt->bind_param("sii", $option_text, $is_correct, $question_id);
                            $insert_stmt->execute();
                        }
                    }
                    break;
    
            case 'true_false':
                $correct_option = $_POST['tf_answer'] ?? '';
                $options = ['true', 'false'];

                foreach($options as $option) {
                    $is_correct = ($option === $correct_option) ? 1 : 0;

                    $options_query = "INSERT INTO rw_question_opt (option_text, is_right, rw_question_id) VALUES (?, ?, ?)";
                    $option_stmt = $conn->prepare($options_query);
                    $option_stmt->bind_param("sii", $option, $is_correct, $question_id);
                    $option_stmt->execute();    
                }
                break;

            case 'identification':
            case 'fill_blank':
                $answer_text = $_POST[$question_type . '_answer'] ?? '';

                if (!empty($answer_text)) {
                    $identification_query = "INSERT INTO rw_question_identifications (identification_answer, rw_question_id) VALUES (?, ?)";
                    $identification_stmt = $conn->prepare($identification_query);
                    $identification_stmt->bind_param("si", $answer_text, $question_id);
                    $identification_stmt->execute();
                } else {
                    throw new Exception(ucfirst($question_type) . ' answer is required.');
                }
                break;
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question saved successfully.']);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    $conn->close();
}
?>
