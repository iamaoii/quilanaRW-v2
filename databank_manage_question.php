<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Questions | Quilana</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="databank_manage_question.css">
</head>
<body>
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
    return $difficulties[$difficulty_id] ?? 'Unknown';
}

include('nav_bar.php');

$program_id = isset($_GET['program_id']) ? $_GET['program_id'] : '';
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
$topic_id = isset($_GET['topic_id']) ? $_GET['topic_id'] : '';

if (empty($program_id) || empty($course_id) || empty($topic_id)) {
    die("Invalid parameters provided.");
}

$program_id_int = intval($program_id);
$course_id_int = intval($course_id);
$topic_id_int = intval($topic_id);

if ($program_id == 0 || $course_id == 0 || $topic_id == 0) {
    die("Invalid parameters provided.");
}

// Fetch Program, Course, and Topic details
$program_name = "";
$course_name = "";
$topic_name = "";

// Program
$program_query = "SELECT program_name FROM rw_bank_program WHERE program_id = ?";
if ($stmt = $conn->prepare($program_query)) {
    $stmt->bind_param("i", $program_id_int);
    $stmt->execute();
    $stmt->bind_result($program_name);
    $stmt->fetch();
    $stmt->close();
}

// Course
$course_query = "SELECT course_name FROM rw_bank_course WHERE course_id = ?";
if ($stmt = $conn->prepare($course_query)) {
    $stmt->bind_param("i", $course_id_int);
    $stmt->execute();
    $stmt->bind_result($course_name);
    $stmt->fetch();
    $stmt->close();
}

// Topic
$topic_query = "SELECT topic_name FROM rw_bank_topic WHERE topic_id = ?";
if ($stmt = $conn->prepare($topic_query)) {
    $stmt->bind_param("i", $topic_id_int);
    $stmt->execute();
    $stmt->bind_result($topic_name);
    $stmt->fetch();
    $stmt->close();
}
?>

<div class="content-wrapper">
    <div class="back-arrow">
        <a href="javascript:history.back()">
            <i class="fa fa-arrow-left"></i>
        </a>
    </div>

    <div class="reviewer">
        <div class="reviewer-details">
            <h2><?php echo htmlspecialchars($topic_name); ?></h2>
            <p><strong>Program:</strong> <?php echo htmlspecialchars($program_name); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($course_name); ?></p>
            <button class="btn btn-primary mt-3" id="add_item_btn">
                <i class="fa fa-plus"></i> Add Question
            </button>
            <button class="btn btn-primary mt-3" id="select_question_btn">
                <i class="fa fa-list-check"></i> Select Question
            </button>
            <button class="btn btn-primary mt-3" id="add_to_btn" disabled>
                <i class="fa fa-folder-plus"></i> Add To...
            </button>
        </div>

        <?php
        // Get questions under this topic
        $questions_query = "
            SELECT q.* 
            FROM rw_bank_question q 
            WHERE q.topic_id = ? 
            ORDER BY q.date_created DESC
        ";
        $question_number = 1;

        if ($stmt = $conn->prepare($questions_query)) {
            $stmt->bind_param("i", $topic_id_int);
            $stmt->execute();
            $questions_result = $stmt->get_result();

            if ($questions_result->num_rows > 0) {
                echo '<div class="card card-full-width">';
                echo '<div class="card-header">Questions</div>';
                echo '<div class="card-body">';
                echo '<ul class="list-group">';

                while ($row = $questions_result->fetch_assoc()) {
                    echo '<li class="list-group-item">';
                    echo '<div class="question-number">Question ' . $question_number . ':</div>';
                    echo '<h6>' . htmlspecialchars($row['question_text']) . '</h6>';
                    echo '<p><strong>Type:</strong> ' . htmlspecialchars(getQuestionTypeName($row['question_type'])) . '</p>';
                    echo '<p><strong>Difficulty:</strong> ' . htmlspecialchars(getDifficultyName($row['difficulty'])) . '</p>';
                    echo '<p><strong>Created:</strong> ' . htmlspecialchars($row['date_created']) . '</p>';

                    // Show options or answers
                    if (in_array($row['question_type'], ['multiple_choice', 'checkbox', 'true_false'])) {
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

                    echo '<div class="float-right">';
                    echo '<button class="btn btn-sm btn-outline-primary edit_question me-2" data-id="' . htmlspecialchars($row['question_id']) . '"><i class="fa fa-edit"></i></button>';
                    echo '<button class="btn btn-sm btn-outline-danger remove_question" data-id="' . htmlspecialchars($row['question_id']) . '"><i class="fa fa-trash"></i></button>';
                    echo '</div>';
                    echo '</li>';

                    $question_number++;
                }
                echo '</ul></div></div>';
            } else {
                echo '<p class="alert alert-info">No questions found for this topic. Start by adding some questions!</p>';
            }
            $stmt->close();
        }
        ?>
    </div>
</div>

<!-- Add/Edit Question Modal (still intact) -->
<div class="modal fade" id="manage_question" tabindex="-1" aria-labelledby="manageQuestionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="manageQuestionLabel">Add New Question</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="question-frm">
                <div class="modal-body">
                    <div id="msg"></div>
                    <input type="hidden" name="topic_id" value="<?php echo $topic_id_int; ?>" />
                    <input type="hidden" name="program_id" value="<?php echo $program_id_int; ?>" />
                    <input type="hidden" name="course_id" value="<?php echo $course_id_int; ?>" />
                    <input type="hidden" name="id" />

                    <div class="form-group">
                        <label for="question_type">Question Type:</label>
                        <select name="question_type" id="question_type" class="form-control" required>
                            <option value="">Select Question Type</option>
                            <option value="1">Multiple Choice</option>
                            <option value="2">Checkbox (Multiple Select)</option>
                            <option value="3">True or False</option>
                            <option value="4">Identification</option>
                            <option value="5">Fill in the Blank</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="question_text">Question Text</label>
                        <textarea id="question_text" rows="3" name="question_text" required class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Difficulty:</label>
                        <select name="difficulty" id="difficulty" class="form-control" required>
                            <option value="1">Easy</option>
                            <option value="2">Medium</option>
                            <option value="3">Hard</option>
                        </select>
                    </div>

                    <!-- Options per type ... (unchanged from OG) -->
                    <div id="multiple_choice_options" class="question-type-options" style="display: none;">
                        <label>Options:</label>
                        <div class="form-group" id="mc_options">
                            <div class="option-group d-flex align-items-center mb-2">
                                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                                <label><input type="radio" name="is_right" value="0"> Correct</label>
                                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" id="add_mc_option">Add Option</button>
                    </div>

                    <div id="checkbox_options" class="question-type-options" style="display: none;">
                        <label>Options:</label>
                        <div class="form-group" id="cb_options">
                            <div class="option-group d-flex align-items-center mb-2">
                                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                                <label><input type="checkbox" name="is_right[]" value="0"> Correct</label>
                                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" id="add_cb_option">Add Option</button>
                    </div>

                    <div id="true_false_options" class="question-type-options" style="display: none;">
                        <div class="form-group">
                            <label>Correct Answer:</label>
                            <div class="d-flex align-items-center">
                                <label class="mr-3"><input type="radio" name="tf_answer" value="true"> True</label>
                                <label><input type="radio" name="tf_answer" value="false"> False</label>
                            </div>
                        </div>
                    </div>

                    <div id="identification_options" class="question-type-options" style="display: none;">
                        <div class="form-group">
                            <label for="identification_answer">Correct Answer:</label>
                            <input type="text" id="identification_answer" name="identification_answer" class="form-control">
                        </div>
                    </div>

                    <div id="fill_blank_options" class="question-type-options" style="display: none;">
                        <div class="form-group">
                            <label for="fill_blank_answer">Correct Answer:</label>
                            <input type="text" id="fill_blank_answer" name="fill_blank_answer" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button id="save_question_btn" type="submit" class="btn btn-primary">Save Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add To Modal -->
<div class="modal fade" id="addToModal" tabindex="-1" aria-labelledby="addToModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addToModalLabel">Add Selected Questions To</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Assessment:</label>
                    <select class="form-control" id="existing_assessment">
                        <option value="">Choose existing assessment...</option>
                        <?php 
                        $assessments = $conn->query("SELECT assessment_id, assessment_title FROM rw_bank_assessment WHERE created_by = '".$_SESSION['login_id']."' ORDER BY assessment_title ASC");
                        if ($assessments && $assessments->num_rows > 0) {
                            while ($ass = $assessments->fetch_assoc()) {
                                echo '<option value="'.htmlspecialchars($ass['assessment_id']).'">'.htmlspecialchars($ass['assessment_title']).'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="text-center mb-3">- OR -</div>
                <div class="d-grid">
                    <button type="button" class="btn btn-success" id="new_assessment_btn">
                        <i class="fa fa-plus-circle"></i> Create New Assessment
                    </button>
                </div>
                <div class="mt-3 p-3 bg-light rounded">
                    <small class="text-muted">
                        <strong>Selected Questions:</strong>
                        <span id="selected_questions_count">0</span> question(s) selected
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm_add_to" disabled>
                    <i class="fa fa-save"></i> Add to Assessment
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="databank_manage_question.js"></script>
</body>
</html>