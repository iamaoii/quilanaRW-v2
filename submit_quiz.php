<?php
session_start();
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['login_id'];  
    $reviewer_id = intval($_POST['reviewer_id']); 
    $answers = $_POST['answers'];
    $total_score = 0;
    $total_points = 0;

    $conn->begin_transaction();

    try {
        $submission_stmt = $conn->prepare("INSERT INTO rw_student_submission (student_id, reviewer_id, student_score, date_taken) VALUES (?, ?, 0, NOW())");
        $submission_stmt->bind_param("ii", $student_id, $reviewer_id);
        $submission_stmt->execute();
        $rw_submission_id = $conn->insert_id; 

        foreach ($answers as $question_id => $user_answer) {
            $question_stmt = $conn->prepare("SELECT question_type, total_points FROM rw_questions WHERE rw_question_id = ?");
            $question_stmt->bind_param("i", $question_id);
            $question_stmt->execute();
            $question_result = $question_stmt->get_result();

            if ($question_result->num_rows > 0) {
                $question = $question_result->fetch_assoc();
                $question_type = $question['question_type'];
                $question_points = $question['total_points'];
                $total_points += $question_points; 

                // Handle different question types
                if ($question_type == 1 || $question_type == 3) {
                    $opt_query = $conn->prepare("SELECT rw_option_id, option_text FROM rw_question_opt WHERE rw_question_id = ? AND LOWER(TRIM(option_text)) = LOWER(TRIM(?))");
                    $opt_query->bind_param("is", $question_id, $user_answer);
                    $opt_query->execute();
                    $opt_result = $opt_query->get_result();

                    if ($opt_result->num_rows > 0) {
                        $option = $opt_result->fetch_assoc();
                        $option_id = $option['rw_option_id'];
                        $option_value = $option['option_text'];

                        $correct_query = $conn->prepare("SELECT option_text FROM rw_question_opt WHERE rw_question_id = ? AND is_right = 1");
                        $correct_query->bind_param("i", $question_id);
                        $correct_query->execute();
                        $correct_result = $correct_query->get_result();

                        if ($correct_result->num_rows > 0) {
                            $correct_option = $correct_result->fetch_assoc();
                            $correct_option_txt = strtolower(trim($correct_option['option_text']));
                            $is_right = ($option_value === $correct_option_txt) ? 1 : 0;
                        }                       
                    } else {
                        $option_id = NULL;
                        $option_value = 'NO ANSWER';
                    }

                    $total_score += $is_right ? $question_points : 0;

                    // Insert into rw_answer
                    $insert_answer_stmt = $conn->prepare("INSERT INTO rw_answer (student_id, rw_submission_id, rw_question_id, rw_option_id, answer_text, is_right) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_answer_stmt->bind_param("iiiisi", $student_id, $rw_submission_id, $question_id, $option_id, $option_value, $is_right);
                    $insert_answer_stmt->execute();

                } elseif ($question_type == 2) { 
                    // Multiple Selection
                    $selected_answers = is_array($user_answer) ? array_map('strtolower', array_map('trim', $user_answer)) : [strtolower(trim($user_answer))];
                    $correct_answers_query = $conn->prepare("SELECT option_text FROM rw_question_opt WHERE rw_question_id = ? AND is_right = 1");
                    $correct_answers_query->bind_param("i", $question_id);
                    $correct_answers_query->execute();
                    $correct_answers_result = $correct_answers_query->get_result();

                    $correct_answers = [];
                    while ($row = $correct_answers_result->fetch_assoc()) {
                        $correct_answers[] = strtolower(trim($row['option_text']));
                    }

                    // Compare answers
                    $is_correct = (count($selected_answers) == count($correct_answers) && !array_diff($selected_answers, $correct_answers));
                    
                    // Insert each selected answer into rw_answer
                    foreach ($selected_answers as $choice) {
                        $opt_query = $conn->prepare("SELECT rw_option_id FROM rw_question_opt WHERE rw_question_id = ? AND LOWER(TRIM(option_text)) = LOWER(TRIM(?))");
                        $opt_query->bind_param("is", $question_id, $choice);
                        $opt_query->execute();
                        $option_data = $opt_query->get_result()->fetch_assoc();
                        $option_id = $option_data['rw_option_id'] ?? NULL;

                        $is_right = in_array($choice, $correct_answers) ? 1 : 0;

                        $insert_answer_stmt = $conn->prepare("INSERT INTO rw_answer (student_id, rw_submission_id, rw_question_id, rw_option_id, answer_text, is_right) VALUES (?, ?, ?, ?, ?, ?)");
                        $insert_answer_stmt->bind_param("iiiisi", $student_id, $rw_submission_id, $question_id, $option_id, $choice, $is_right);
                        $insert_answer_stmt->execute();
                    }

                    if ($is_correct) {
                        $total_score += $question_points; // All answers were correct
                    }

                } elseif ($question_type == 4 || $question_type == 5) {
                    // Fill in the blank (Type 4) or Identification (Type 5)
                    $ident_query = $conn->prepare("SELECT identification_answer FROM rw_question_identifications WHERE rw_question_id = ? AND LOWER(TRIM(identification_answer)) = ?");
                    $ident_query->bind_param("is", $question_id, $user_answer);
                    $ident_query->execute();
                    $ident_result = $ident_query->get_result();
                    $user_answer = trim(strtolower($user_answer));
                    if ($ident_result->num_rows > 0) {
                        $text_value = strtolower(trim($ident_result->fetch_assoc()['identification_answer']));
                        $is_right = 1;
                    } else {
                        if ($user_answer == '') {
                            $text_value = 'NO ANSWER'; // Set the text value as no answer when the answer is empty
                        } else {
                            $text_value = strtolower(trim($user_answer)); // Set the text value as the student's answer if it is wrong
                        }
                        $is_right = 0;
                    }

                    // Calculate score
                    $total_score += $is_right ? $question_points : 0;

                    // Insert answer into rw_answer
                    $insert_answer_stmt = $conn->prepare("INSERT INTO rw_answer (student_id, rw_submission_id, rw_question_id, answer_text, is_right) VALUES (?, ?, ?, ?, ?)");
                    $insert_answer_stmt->bind_param("iiisi", $student_id, $rw_submission_id, $question_id, $text_value, $is_right);
                    $insert_answer_stmt->execute();
                }
            }
        }

        // Update the student's submission score
        $update_score_stmt = $conn->prepare("UPDATE rw_student_submission SET student_score = ? WHERE rw_submission_id = ?");
        $update_score_stmt->bind_param("ii", $total_score, $rw_submission_id);
        $update_score_stmt->execute();

        // Insert the result into the rw_student_results table
        $result_stmt = $conn->prepare("INSERT INTO rw_student_results (student_id, reviewer_id, rw_submission_id, student_score, date_taken) VALUES (?, ?, ?, ?, CURDATE())");
        $result_stmt->bind_param("iiii", $student_id, $reviewer_id, $rw_submission_id, $total_score);
        $result_stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Prepare the response
        $response = [
            'score' => $total_score,
            'total' => $total_points,
            'message' => ($total_score >= ($total_points / 2)) ? 'Great job!' : 'Better luck next time!'
        ];

        echo json_encode($response);
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        echo json_encode(['error' => 'An error occurred while processing the quiz. Please try again.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>
