<?php
include 'db_connect.php';
include 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

if ($assessment_id == 0) {
    die("Invalid assessment ID.");
}

// Verify the assessment belongs to the current user
$assessment_query = "SELECT assessment_title, no_of_questions FROM rw_bank_assessment WHERE assessment_id = ? AND created_by = ?";
$stmt = $conn->prepare($assessment_query);
$stmt->bind_param("ii", $assessment_id, $_SESSION['login_id']);
$stmt->execute();
$assessment_result = $stmt->get_result();

if ($assessment_result->num_rows == 0) {
    die("Assessment not found or access denied.");
}

$assessment = $assessment_result->fetch_assoc();
$assessment_title = $assessment['assessment_title'];
$stmt->close();

function getQuestionTypeName($type_id) {
    $types = [
        '1' => 'Multiple Choice',
        '2' => 'Checkbox',
        '3' => 'True or False',
        '4' => 'Identification',
        '5' => 'Fill in the Blank'
    ];
    return $types[$type_id] ?? 'Unknown';
}

function getDifficultyName($difficulty_id) {
    $difficulties = [
        '1' => 'Easy',
        '2' => 'Medium',
        '3' => 'Hard'
    ];
    return $difficulties[$difficulty_id] ?? 'Unknown'; // Fixed typo: $type_id to $difficulty_id
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Assessment Questions | Quilana</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="databank_manage_question.css">
    <style>
        .controls-container {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .btn-export {
            background-color: #4a4ca6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-export:hover {
            background-color: #3a3b8c;
            color: white;
        }
        .no-questions {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-questions i {
            margin-bottom: 15px;
            color: #6c757d;
        }
        .question-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="content-wrapper">
    <div class="back-arrow">
        <a href="assessments.php">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>

    <div class="reviewer">
        <div class="reviewer-details">
            <h2><?php echo htmlspecialchars($assessment_title); ?></h2>
            <p><strong>No. of Questions:</strong> <?php echo htmlspecialchars($assessment['no_of_questions']); ?></p>
            
            <div class="controls-container">
                <button class="btn-export" id="export_questions_btn">
                    <i class="fas fa-download"></i> Export Questions
                </button>
            </div>
        </div>

        <?php
        // Get questions linked to this assessment
        $questions_query = "
            SELECT q.*, aq.assessment_question_id, aq.date_added 
            FROM rw_bank_question q 
            INNER JOIN rw_bank_assessment_question aq ON q.question_id = aq.question_id 
            WHERE aq.assessment_id = ? 
            ORDER BY aq.date_added DESC
        ";
        $question_number = 1;

        if ($stmt = $conn->prepare($questions_query)) {
            $stmt->bind_param("i", $assessment_id);
            $stmt->execute();
            $questions_result = $stmt->get_result();

            if ($questions_result->num_rows > 0) {
                echo '<div class="card card-full-width">';
                echo '<div class="card-header">Questions in Assessment</div>';
                echo '<div class="card-body">';
                echo '<ul class="list-group">';

                while ($row = $questions_result->fetch_assoc()) {
                    echo '<li class="list-group-item">';
                    echo '<div class="question-number">Question ' . $question_number . ':</div>';
                    echo '<h6>' . htmlspecialchars($row['question_text']) . '</h6>';
                    echo '<p><strong>Type:</strong> ' . htmlspecialchars(getQuestionTypeName($row['question_type'])) . '</p>';
                    echo '<p><strong>Difficulty:</strong> ' . htmlspecialchars(getDifficultyName($row['difficulty'])) . '</p>';
                    echo '<p><strong>Added to Assessment:</strong> ' . htmlspecialchars($row['date_added']) . '</p>';

                    // Show options or answers
                    if (in_array($row['question_type'], ['1', '2', '3'])) {
                        $options_query = "
                            SELECT option_text, is_correct 
                            FROM rw_bank_question_option 
                            WHERE question_id = ? 
                            ORDER BY option_id ASC
                        ";
                        if ($opt_stmt = $conn->prepare($options_query)) {
                            $opt_stmt->bind_param("i", $row['question_id']);
                            $opt_stmt->execute();
                            $options_result = $opt_stmt->get_result();

                            echo '<div class="option-list"><strong>Options:</strong><ul>';
                            while ($option = $options_result->fetch_assoc()) {
                                $correct_class = $option['is_correct'] ? 'correct-answer' : '';
                                echo '<li class="option-item ' . $correct_class . '">';
                                echo htmlspecialchars($option['option_text']);
                                if ($option['is_correct']) echo ' ✓';
                                echo '</li>';
                            }
                            echo '</ul></div>';
                            $opt_stmt->close();
                        }
                    } else {
                        $answer_query = "
                            SELECT correct_answer 
                            FROM rw_bank_question_answer 
                            WHERE question_id = ? 
                            LIMIT 1
                        ";
                        if ($ans_stmt = $conn->prepare($answer_query)) {
                            $ans_stmt->bind_param("i", $row['question_id']);
                            $ans_stmt->execute();
                            $ans_stmt->bind_result($correct_answer);
                            if ($ans_stmt->fetch()) {
                                echo '<p><strong>Correct Answer:</strong> <span class="correct-answer">' . htmlspecialchars($correct_answer) . '</span></p>';
                            }
                            $ans_stmt->close();
                        }
                    }

                    echo '<div class="question-actions">';
                    echo '<button class="btn btn-sm btn-outline-danger remove-from-assessment" 
                              data-assessment-question-id="' . htmlspecialchars($row['assessment_question_id']) . '"
                              data-question-id="' . htmlspecialchars($row['question_id']) . '">
                              <i class="fa fa-trash"></i> Remove from Assessment
                          </button>';
                    echo '</div>';
                    echo '</li>';

                    $question_number++;
                }
                echo '</ul></div></div>';
            } else {
                echo '<div class="no-questions">';
                echo '<i class="fas fa-inbox fa-3x mb-3"></i>';
                echo '<h4>No Questions in This Assessment</h4>';
                echo '<p>This assessment doesn\'t contain any questions yet.</p>';
                echo '</div>';
            }
            $stmt->close();
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Remove question from assessment
    $('.remove-from-assessment').on('click', function() {
        const assessmentQuestionId = $(this).data('assessment-question-id');
        const questionId = $(this).data('question-id');
        const button = $(this);

        Swal.fire({
            title: 'Remove Question?',
            text: 'This will remove the question from the assessment, but not delete the question itself.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'assessment_remove_question.php',
                    method: 'POST',
                    data: {
                        assessment_question_id: assessmentQuestionId,
                        question_id: questionId,
                        assessment_id: <?php echo $assessment_id; ?>
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Remove the question item from the list
                                button.closest('.list-group-item').remove();
                                
                                // Reload if no questions left, or update numbers
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to remove question'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to remove question'
                        });
                    }
                });
            }
        });
    });

    // Export questions
    $('#export_questions_btn').on('click', function() {
        // Direct download without modal
        window.location.href = 'assessment_export.php?assessment_id=<?php echo $assessment_id; ?>';
    });
});
</script>
</body>
</html>