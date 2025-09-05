<?php
include('header.php');
include('auth.php');
include('db_connect.php');

// Retrieve assessment ID from query parameters
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($assessment_id > 0) {
    // Fetch the assessment details and time limits based on mode
    $assessment_query = "
        SELECT a.assessment_name, a.topic, a.assessment_mode, 
               q.question, q.ques_type, qo.option_txt, qo.is_right, qi.identification_answer
        FROM assessment a
        LEFT JOIN questions q ON a.assessment_id = q.assessment_id
        LEFT JOIN question_options qo ON q.question_id = qo.question_id
        LEFT JOIN question_identifications qi ON q.question_id = qi.question_id
        WHERE a.assessment_id = ?
        ORDER BY q.order_by ASC";
    
    $stmt = $conn->prepare($assessment_query);
    
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $assessment_id);
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $assessment_details = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "<p>No assessment found for the given ID.</p>";
        exit;
    }
} else {
    echo "<p>Invalid assessment ID.</p>";
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assessment | Quilana</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .back-arrow {
            font-size: 24px; 
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .back-arrow a {
            color: #4A4CA6; 
            text-decoration: none;
        }
        .back-arrow a:hover {
            color: #0056b3; 
        }
        .assessment-details {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .assessment-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }
        .assessment-details p {
            margin-bottom: 0.5px;
            font-size: 16px;
            color: #555;
        }
        .question-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-height: 70vh;
            overflow-y: auto;
            max-width: 100%;
            margin: 0 !important;
        }
        .question {
            margin-bottom: 25px;
            padding: 15px;
            border-left: 4px solid #4A4CA6;
            background-color: #f9f9f9;
        }
        .question p {
            margin-bottom: 16px !important;
        }
        .question-number {
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }
        .option {
            margin-left: 20px;
            margin-bottom: 8px;
        }
        .option label {
            display: flex;
            align-items: center;
        }
        .option input[type="radio"],
        .option input[type="checkbox"] {
            margin-right: 10px;
            accent-color: #4A4CA6;
        }
        .checked {
            background-color: #e6e6fa;
            padding: 5px;
            border-radius: 4px;
            margin-left: -5px;
        }
    </style>
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div class="content-wrapper">
        <div class="back-arrow">
            <a href="class_enrolled.php">
                <i class="fa fa-arrow-left"></i>
            </a>
        </div>

        <div class="assessment-details">
            <h2><?php echo htmlspecialchars($assessment_details[0]['assessment_name']); ?></h2>
            <p><strong>Topic:</strong> <?php echo htmlspecialchars($assessment_details[0]['topic']); ?></p>
        </div>

        <div class="question-container">
            <?php
            $current_question = null;
            $question_number = 1;
            foreach ($assessment_details as $detail) {
                if ($current_question !== $detail['question']) {
                    if ($current_question !== null) {
                        echo '</div>'; // Close previous question
                    }
                    $current_question = $detail['question'];
                    echo '<div class="question">';
                    echo '<div class="question-number">Question ' . $question_number . ':</div>';
                    echo '<p>' . htmlspecialchars($current_question) . '</p>';
                        
                    $question_number++;
                }

                switch ($detail['ques_type']) {
                    case 1:  // Multiple Choice
                    case 2:  // Multiple Select (Checkbox)
                    case 3:  // True/False
                        $input_type = $detail['ques_type'] == 2 ? 'checkbox' : 'radio';
                        $checked_attr = $detail['is_right'] ? 'checked' : '';
                        $checked_class = $detail['is_right'] ? 'checked' : '';
                        echo '<div class="option">';
                        echo '<label class="' . $checked_class . '">';
                        echo '<input type="' . $input_type . '" class="non-interactive" name="question_' . ($question_number - 1) . '" ' . $checked_attr . '>' . htmlspecialchars($detail['option_txt']);
                        echo '</label>';
                        echo '</div>';
                        break;

                    case 4:  // Identification
                    case 5:  // Fill in the Blank
                        echo '<div class="option"><strong>Answer:</strong> <span>' . htmlspecialchars($detail['identification_answer']) . '</span></div>';
                        break;

                    default:
                        break;
                }
            }
            if ($current_question !== null) {
                echo '</div>'; // Close last question
            }
            ?>
        </div>
    </div>
    <script>
        $('.non-interactive').on('click', function(event) {
            event.preventDefault(); // Prevent default action
        });
    </script>
</body>
</html>