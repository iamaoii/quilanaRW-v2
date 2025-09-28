<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Questions | Quilana</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .reviewer {
            padding: 10px;
        }
        .reviewer-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .reviewer-details h2 {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .reviewer-details p {
            margin-bottom: 5px;
            font-size: 1em;
            color: #666;
        }
        .card-full-width {
            width: 100%;
            margin-bottom: 20px;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            border: none !important;
        }
        .card-header {
            background-color: #E0E0EC !important;
            font-weight: bold;
            border-bottom: none !important;
        }
        .card-body {
            height: 55vh;
            max-height: auto;
            overflow-y: auto;
        }
        .card-body::-webkit-scrollbar {
            display: none;
        }

        .list-group {
            gap: 15px;
        }
        .list-group-item {
            border-left: 4px solid #4A4CA6 !important;
            background-color: #f9f9f9 !important;
        }
        .list-group-item h6 {
            margin-bottom: 15px;
            font-weight: 500;
        }
        .list-group-item p {
            margin: 0;
        }
        .list-group-item .question-number {
            font-weight: bold;
            color: #4A4CA6;
            margin-bottom: 10px;
        }

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
        .btn-primary {
            background-color: #4A4CA6;
            border-color: #4A4CA6;
        }
        .btn-primary:hover {
            background-color: #3a3b8c;
            border-color: #3a3b8c;
        }

        .float-right {
            display: flex;
            gap: 8px;
        }
        .mt-3 {
            display: flex;
            gap: 10px;
        }
        .option-list {
            margin-top: 10px;
            padding-left: 20px;
        }
        .correct-answer {
            color: #28a745;
            font-weight: bold;
        }
        .option-item {
            margin-bottom: 5px;
        }
        .question-item.selected {
            background-color: #e3f2fd !important;
            border-left: 4px solid #2196F3 !important;
        }
        .question-checkbox {
            margin-right: 10px;
        }
        #add_to_btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        #confirm_add_to:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
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

    // And change the validation condition:
    if (empty($program_id) || empty($course_id) || empty($topic_id)) {
        die("Invalid parameters provided.");
    }

    // Then convert to integers after validation if needed for database queries:
    $program_id_int = intval($program_id);
    $course_id_int = intval($course_id); 
    $topic_id_int = intval($topic_id);
    
    // Validate parameters
    if ($program_id == 0 || $course_id == 0 || $topic_id == 0) {
        die("Invalid parameters provided.");
    }
    
    // Get program, course, and topic details
    $program_name = "";
    $course_name = "";
    $topic_name = "";
    
    // Get program name
    $program_query = "SELECT program_name FROM rw_bank_program WHERE program_id = ?";
    if ($stmt = $conn->prepare($program_query)) {
        $stmt->bind_param("i", $program_id_int);
        $stmt->execute();
        $stmt->bind_result($program_name);
        $stmt->fetch();
        $stmt->close();
    }
    
    // Get course name
    $course_query = "SELECT course_name FROM rw_bank_course WHERE course_id = ?";
    if ($stmt = $conn->prepare($course_query)) {
        $stmt->bind_param("i", $course_id_int);
        $stmt->execute();
        $stmt->bind_result($course_name);
        $stmt->fetch();
        $stmt->close();
    }
    
    // Get topic name
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
            // Query to get questions for this topic
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
                        
                        // Display answers/options based on question type
                        if (in_array($row['question_type'], ['multiple_choice', 'checkbox', 'true_false'])) {
                            // Get options for MCQ, Checkbox, True/False
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
                                
                                echo '<div class="option-list">';
                                echo '<strong>Options:</strong>';
                                echo '<ul>';
                                while ($option = $options_result->fetch_assoc()) {
                                    $correct_class = $option['is_correct'] ? 'correct-answer' : '';
                                    echo '<li class="option-item ' . $correct_class . '">';
                                    echo htmlspecialchars($option['option_text']);
                                    if ($option['is_correct']) {
                                        echo ' ✓';
                                    }
                                    echo '</li>';
                                }
                                echo '</ul>';
                                echo '</div>';
                                $opt_stmt->close();
                            }
                        } else {
                            // Get answer for Identification/Fill in the blank
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
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<p class="alert alert-info">No questions found for this topic. Start by adding some questions!</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="alert alert-danger">Error preparing the SQL query for questions.</p>';
            }
            ?>
        </div>     
    </div>   

    <!-- Modal for Adding/Editing Questions -->
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

                        <!-- Multiple Choice Options -->
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

                        <!-- Checkbox Options -->
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

                        <!-- True or False Options -->
                        <div id="true_false_options" class="question-type-options" style="display: none;">
                            <div class="form-group">
                                <label>Correct Answer:</label>
                                <div class="d-flex align-items-center">
                                    <label class="mr-3"><input type="radio" name="tf_answer" value="true"> True</label>
                                    <label><input type="radio" name="tf_answer" value="false"> False</label>
                                </div>
                            </div>
                        </div>

                        <!-- Identification Options -->
                        <div id="identification_options" class="question-type-options" style="display: none;">
                            <div class="form-group">
                                <label for="identification_answer">Correct Answer:</label>
                                <input type="text" id="identification_answer" name="identification_answer" class="form-control">
                            </div>
                        </div>

                        <!-- Fill in the Blank Options -->
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
                            <option value="1">Example Assessment 1</option>
                            <option value="2">Example Assessment 2</option>
                            <option value="3">Example Assessment 3</option>
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
    <script>
        $(document).ready(function() {
            // Show/hide question type options
            $('#question_type').change(function() {
                $('.question-type-options').hide();
                const selectedType = $(this).val();
                
                if (selectedType === '1') {
                    $('#multiple_choice_options').show();
                } else if (selectedType === '2') {
                    $('#checkbox_options').show();
                } else if (selectedType === '3') {
                    $('#true_false_options').show();
                } else if (selectedType === '4') {
                    $('#identification_options').show();
                } else if (selectedType === '5') {
                    $('#fill_blank_options').show();
                }
            });

            // Add multiple choice option
            $('#add_mc_option').click(function() {
                const optionCount = $('#mc_options .option-group').length;
                const newOption = `
                    <div class="option-group d-flex align-items-center mb-2">
                        <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                        <label><input type="radio" name="is_right" value="${optionCount}"> Correct</label>
                        <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                    </div>
                `;
                $('#mc_options').append(newOption);
            });

            // Add checkbox option
            $('#add_cb_option').click(function() {
                const optionCount = $('#cb_options .option-group').length;
                const newOption = `
                    <div class="option-group d-flex align-items-center mb-2">
                        <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text"></textarea>
                        <label><input type="checkbox" name="is_right[]" value="${optionCount}"> Correct</label>
                        <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                    </div>
                `;
                $('#cb_options').append(newOption);
            });

            // Remove option
            $(document).on('click', '.remove-option', function() {
                if ($('.option-group').length > 1) {
                    $(this).closest('.option-group').remove();
                } else {
                    alert('At least one option is required.');
                }
            });

            // Add question button click
            $('#add_item_btn').click(function() {
                $('#manage_question').modal('show');
                $('#question-frm')[0].reset();
                $('.question-type-options').hide();
                $('#manageQuestionLabel').text('Add New Question');
            });

            // Selection mode state
            let selectionMode = false;
            let selectedQuestions = new Set();

            // Select Question Button
            $('#select_question_btn').click(function() {
                selectionMode = !selectionMode;
                toggleSelectionMode();
            });

            // Add To Button
            $('#add_to_btn').click(function() {
                if (selectedQuestions.size > 0) {
                    $('#selected_questions_count').text(selectedQuestions.size);
                    $('#addToModal').modal('show');
                }
            });

            // Toggle selection mode
            function toggleSelectionMode() {
                if (selectionMode) {
                    $('#select_question_btn').html('<i class="fa fa-times"></i> Cancel Selection');
                    $('#select_question_btn').removeClass('btn-primary').addClass('btn-warning');
                    $('.list-group-item').addClass('selectable').css('cursor', 'pointer');
                    
                    // Add checkboxes to each question
                    $('.list-group-item').each(function() {
                        const questionId = $(this).find('.edit_question').data('id');
                        if (!$(this).find('.question-checkbox').length) {
                            $(this).prepend(`
                                <div class="form-check question-checkbox">
                                    <input class="form-check-input" type="checkbox" value="${questionId}" id="question_${questionId}">
                                </div>
                            `);
                        }
                    });
                } else {
                    $('#select_question_btn').html('<i class="fa fa-list-check"></i> Select Question');
                    $('#select_question_btn').removeClass('btn-warning').addClass('btn-primary');
                    $('.list-group-item').removeClass('selectable selected').css('cursor', 'default');
                    $('.question-checkbox').remove();
                    selectedQuestions.clear();
                    updateAddToButton();
                }
            }

            // Handle question selection
            $(document).on('change', '.question-checkbox input', function() {
                const questionId = $(this).val();
                const questionItem = $(this).closest('.list-group-item');
                
                if ($(this).is(':checked')) {
                    selectedQuestions.add(questionId);
                    questionItem.addClass('selected');
                } else {
                    selectedQuestions.delete(questionId);
                    questionItem.removeClass('selected');
                }
                
                updateAddToButton();
            });

            // Update Add To button state
            function updateAddToButton() {
                if (selectedQuestions.size > 0) {
                    $('#add_to_btn').prop('disabled', false);
                    $('#add_to_btn').html(`<i class="fa fa-folder-plus"></i> Add To... (${selectedQuestions.size})`);
                } else {
                    $('#add_to_btn').prop('disabled', true);
                    $('#add_to_btn').html('<i class="fa fa-folder-plus"></i> Add To...');
                }
            }

            // Handle click on question items in selection mode
            $(document).on('click', '.list-group-item.selectable', function(e) {
                if (!$(e.target).is('input, button, a, .btn')) {
                    const checkbox = $(this).find('.question-checkbox input');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    checkbox.trigger('change');
                }
            });

            // Assessment selection handling
            $('#existing_assessment').change(function() {
                const assessmentSelected = $(this).val() !== '';
                $('#confirm_add_to').prop('disabled', !assessmentSelected);
            });

            // New Assessment button
            $('#new_assessment_btn').click(function() {
                // For now, just enable the confirm button
                $('#confirm_add_to').prop('disabled', false);
                $('#existing_assessment').val(''); // Clear existing selection
                alert('New assessment creation functionality will be implemented later.');
            });

            // Confirm Add To
            $('#confirm_add_to').click(function() {
                const assessmentId = $('#existing_assessment').val();
                const questionIds = Array.from(selectedQuestions);
                
                console.log('Adding questions to assessment:', {
                    assessmentId: assessmentId,
                    questionIds: questionIds
                });
                
                // Show success message
                alert(`Successfully added ${questionIds.length} question(s) to assessment!`);
                
                // Close modal and reset selection
                $('#addToModal').modal('hide');
                selectionMode = false;
                toggleSelectionMode();
            });

            // Reset modal when closed
            $('#addToModal').on('hidden.bs.modal', function() {
                $('#existing_assessment').val('');
                $('#confirm_add_to').prop('disabled', true);
            });

            // Form submission
            $('#question-frm').submit(function(e) {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(this);
                const questionType = $('#question_type').val();
                const questionId = $('input[name="id"]').val();
                const url = questionId ? 'databank_ajax_update_question.php' : 'databank_ajax_save_question.php';

                // Basic validation
                if (!questionType) {
                    alert('Please select a question type');
                    return;
                }
                
                if (!$('#question_text').val().trim()) {
                    alert('Please enter question text');
                    return;
                }
                
                // Show loading state
                $('#save_question_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                
                // Send AJAX request
                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success - close modal and refresh questions
                        $('#manage_question').modal('hide');
                        alert('Question saved successfully!');
                        location.reload(); // Reload page to show new question
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save question'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error occurred');
                })
                .finally(() => {
                    $('#save_question_btn').prop('disabled', false).html('Save Question');
                });
            });

            // Delete Question Functionality
            $(document).on('click', '.remove_question', function() {
                const questionId = $(this).data('id');
                
                if (confirm('Are you sure you want to delete this question? This action cannot be undone.')) {
                    // Show loading state
                    $(this).html('<i class="fa fa-spinner fa-spin"></i>');
                    $(this).prop('disabled', true);
                    
                    fetch('databank_ajax_delete_question.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'question_id=' + questionId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Question deleted successfully!');
                            location.reload(); // Reload to reflect changes
                        } else {
                            alert('Error: ' + (data.message || 'Failed to delete question'));
                            $(this).html('<i class="fa fa-trash"></i>');
                            $(this).prop('disabled', false);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Network error occurred');
                        $(this).html('<i class="fa fa-trash"></i>');
                        $(this).prop('disabled', false);
                    });
                }
            });

            // Edit Question Functionality
            $(document).on('click', '.edit_question', function() {
                const questionId = $(this).data('id');
                
                // Fetch question data and populate the modal
                fetch('databank_ajax_get_question.php?question_id=' + questionId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate the modal with existing data
                        $('#manageQuestionLabel').text('Edit Question');
                        $('input[name="id"]').val(questionId);
                        $('#question_type').val(data.question.question_type);
                        $('#question_text').val(data.question.question_text);
                        $('#difficulty').val(data.question.difficulty);
                        
                        // Show appropriate options based on question type
                        $('#question_type').trigger('change');
                        
                        // Populate options/answers based on question type
                        if (['1', '2', '3'].includes(data.question.question_type)) {
                            // For multiple choice, checkbox, true/false - populate options
                            populateOptions(data.options, data.question.question_type);
                        } else {
                            // For identification/fill blank - populate answer
                            if (data.answer) {
                                if (data.question.question_type === '4') {
                                    $('#identification_answer').val(data.answer.correct_answer);
                                } else {
                                    $('#fill_blank_answer').val(data.answer.correct_answer);
                                }
                            }
                        }
                        
                        $('#manage_question').modal('show');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to load question data'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Network error occurred');
                });
            });

            // Function to populate options (for edit mode)
            function populateOptions(options, questionType) {
                // Clear existing options
                $('#mc_options, #cb_options').empty();
                
                if (questionType === '3') {
                    // True/False - check the correct radio button
                    const correctAnswer = options.find(opt => opt.is_correct == 1);
                    if (correctAnswer) {
                        if (correctAnswer.option_text === 'True') {
                            $('input[name="tf_answer"][value="true"]').prop('checked', true);
                        } else {
                            $('input[name="tf_answer"][value="false"]').prop('checked', true);
                        }
                    }
                } else {
                    // Multiple choice or checkbox
                    options.forEach((option, index) => {
                        const optionHtml = `
                            <div class="option-group d-flex align-items-center mb-2">
                                <textarea rows="2" name="question_opt[]" class="form-control flex-grow-1 mr-2" placeholder="Option text">${option.option_text}</textarea>
                                <label>
                                    <input type="${questionType === '1' ? 'radio' : 'checkbox'}" 
                                        name="${questionType === '1' ? 'is_right' : 'is_right[]'}" 
                                        value="${index}" 
                                        ${option.is_correct ? 'checked' : ''}>
                                    Correct
                                </label>
                                <button type="button" class="btn btn-sm btn-danger ml-2 remove-option">Remove</button>
                            </div>
                        `;
                        
                        if (questionType === '1') {
                            $('#mc_options').append(optionHtml);
                        } else {
                            $('#cb_options').append(optionHtml);
                        }
                    });
                }
            }

            // Reset modal when closed
            $('#manage_question').on('hidden.bs.modal', function() {
                $('#question-frm')[0].reset();
                $('.question-type-options').hide();
                $('#manageQuestionLabel').text('Add New Question');
                $('input[name="id"]').val('');
            });
        });
    </script>
</body>
</html>