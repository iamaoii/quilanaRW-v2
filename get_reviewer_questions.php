<?php
include('db_connect.php'); 

if (!isset($_GET['reviewer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Reviewer ID not provided.']);
    exit();
}

$reviewer_id = intval($_GET['reviewer_id']);

// Prepare the query to fetch questions
$query = "SELECT * FROM rw_questions WHERE reviewer_id = ? ORDER BY `order_by`";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $reviewer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];

    while ($row = $result->fetch_assoc()) {
        $question_id = $row['rw_question_id'];

        // Fetch options for question
        $options_query = "SELECT * FROM rw_question_opt WHERE rw_question_id = ?";
        if ($options_stmt = $conn->prepare($options_query)) {
            $options_stmt->bind_param("i", $question_id);
            $options_stmt->execute();
            $options_result = $options_stmt->get_result();

            $options = [];
            while ($option_row = $options_result->fetch_assoc()) {
                $options[] = [
                    'option_txt' => $option_row['option_text'],
                    'is_right' => $option_row['is_right']
                ];
            }
            $options_stmt->close();
        }

        // Add question along with options
        $questions[] = [
            'rw_question_id' => $row['rw_question_id'],
            'question' => $row['question'],
            'question_type' => $row['question_type'], 
            'options' => $options
        ];
    }

    $stmt->close();
    echo json_encode(['success' => true, 'data' => $questions]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error preparing the SQL query.']);
}
?>
