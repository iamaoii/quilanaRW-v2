<?php
include('header.php');
include('auth.php');
include('db_connect.php');

if (!isset($_GET['reviewer_id'])) {
    echo "<p>Reviewer ID not provided.</p>";
    exit();
}

$reviewer_id = intval($_GET['reviewer_id']);
$student_id = $_SESSION['login_id'];

// Fetch reviewer details
$query = "SELECT * FROM user_reviewers WHERE reviewer_id = ? AND student_id = ?";
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("ii", $reviewer_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reviewer = $result->fetch_assoc();
        $reviewer_name = htmlspecialchars($reviewer['reviewer_name']);
        $topic = htmlspecialchars($reviewer['topic']);
        $reviewer_type = $reviewer['reviewer_type'];
    } else {
        echo "<p>No reviewer found with the provided ID.</p>";
        exit();
    }

    $stmt->close();
} else {
    echo "<p>Error preparing the SQL query.</p>";
    exit();
}

// Fetch questions for the reviewer
$questions_query = $conn->query("SELECT * FROM rw_questions WHERE reviewer_id = '$reviewer_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Take Reviewer | Quilana</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/take-reviewer.css">
</head>
<body>
    <?php include('nav_bar.php'); ?>

    <div id="confirmation-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closePopup('confirmation-popup')">&times;</button>
            <h2 class="popup-title">Are you sure you want to submit your answers?</h2>
            <p class="popup-message">THIS ACTION CANNOT BE UNDONE</p>
            <div class="popup-buttons">
                <button id="cancel" class="secondary-button" onclick="closePopup('confirmation-popup')">Cancel</button>
                <button id="confirm" class="secondary-button" onclick="handleSubmit()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Results Popup for showing results -->
    <div id="results-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closePopup('results-popup')">&times;</button>
            <h2 class="popup-title">Quiz Results</h2>
            <div id="results-content">
                <!-- Results will be dynamically put here -->
            </div>
            <div class="popup-buttons">
                <button id="retake" class="secondary-button" onclick="retakeQuiz()">Retake Quiz</button>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
    <div class="reviewer-container">
        <?php if ($reviewer_type == 1): // Quiz ?>
            <form id="quiz-form" action="submit_quiz.php" method="POST">
                <div class="header-container">
                    <a href="shared.php?class_id=<?php echo htmlspecialchars($_GET['reviewer_id']); ?>&show_modal=true">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                    <button type="button" onclick="showPopup('confirmation-popup')" id="submit" class="secondary-button">Submit</button>
                </div>

                <!-- Reviewer name and topic -->
                <div class="tabs-container">
                    <ul class="tabs">
                        <li class="tab-link active" data-tab="reviewer-tab"><?php echo $reviewer_name . ' | ' . $topic; ?></li>
                    </ul>
                </div>

                <div class="questions-container">
                    <?php
                    $question_number = 1;
                    while ($question = $questions_query->fetch_assoc()) {
                        echo "<div class='question'>";
                        echo "<p><strong>$question_number. " . htmlspecialchars($question['question']) . "</strong></p>";

                        $question_type = $question['question_type'];

                        // Single choice (radio buttons)
                        if ($question_type == 1) {
                            echo "<input type='hidden' name='answers[" . $question['rw_question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM rw_question_opt WHERE rw_question_id = '" . $question['rw_question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input id='option_" . htmlspecialchars($choice['rw_option_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['rw_question_id'] . "]' value='" . htmlspecialchars($choice['option_text']) . "' required>";
                                echo "<label for='option_" . htmlspecialchars($choice['rw_option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_text']) . "</label>";
                                echo "</div>";
                            }
                        // Multiple choice (checkboxes)
                        } elseif ($question_type == 2) {
                            echo "<input type='hidden' name='answers[" . $question['rw_question_id'] . "]' value=''>";

                            $choices_query = $conn->query("SELECT * FROM rw_question_opt WHERE rw_question_id = '" . $question['rw_question_id'] . "'");
                            while ($choice = $choices_query->fetch_assoc()) {
                                echo "<div class='form-check'>";
                                echo "<input id='option_" . htmlspecialchars($choice['rw_option_id']) . "' class='form-check-input' type='checkbox' name='answers[" . $question['rw_question_id'] . "][]' value='" . htmlspecialchars($choice['option_text']) . "'>";
                                echo "<label for='option_" . htmlspecialchars($choice['rw_option_id']) . "' class='form-check-label'>" . htmlspecialchars($choice['option_text']) . "</label>";
                                echo "</div>";
                            }
                        // True/False (radio buttons)
                        } elseif ($question_type == 3) {
                            echo "<input type='hidden' name='answers[" . $question['rw_question_id'] . "]' value=''>";
                            
                            echo "<div class='form-check'>";
                            echo "<input id='true_" . htmlspecialchars($question['rw_question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['rw_question_id'] . "]' value='true' required>";
                            echo "<label for='true_" . htmlspecialchars($question['rw_question_id']) . "' class='form-check-label'>True</label>";
                            echo "</div>";
                            echo "<div class='form-check'>";
                            echo "<input id='false_" . htmlspecialchars($question['rw_question_id']) . "' class='form-check-input' type='radio' name='answers[" . $question['rw_question_id'] . "]' value='false' required>";
                            echo "<label for='false_" . htmlspecialchars($question['rw_question_id']) . "' class='form-check-label'>False</label>";
                            echo "</div>";
                        // Fill in the blank and identification (text input)
                        } elseif ($question_type == 4 || $question_type == 5) {
                            echo "<div class='form-check-group'>";
                            echo "<input type='text' id='answer_" . htmlspecialchars($question['rw_question_id']) . "' class='form-control' name='answers[" . $question['rw_question_id'] . "]' placeholder='Type your answer here' required>";
                            echo "</div>";
                        }
                        echo "</div>";
                        $question_number++;
                    }
                    ?>
                </div>
                <input type="hidden" name="reviewer_id" value="<?php echo $reviewer_id; ?>">
            </form>
        <?php else: // Flashcards ?>
            <div id="flashcard-container">
                <!-- Flashcards will be loaded here -->
            </div>
            <div class="flashcard-counter">
                <span id="current-flashcard">1</span> / <span id="total-flashcards">0</span>
            </div>
            <div class="buttons">
                <button id="prev-flashcard" class="btn btn-secondary">Previous</button>
                <button id="flip-flashcard" class="btn btn-info">Flip</button>
                <button id="next-flashcard" class="btn btn-primary">Next</button>
            </div>
        <?php endif; ?>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        const reviewerId = <?php echo $reviewer_id; ?>;
        const reviewerType = <?php echo $reviewer_type; ?>;
        
        if (reviewerType !== 1) {
            let flashcards = []; 
            let currentFlashcardIndex = 0;

            // Load flashcards
            $.ajax({
                url: 'get_flashcards.php',
                type: 'GET',
                data: { reviewer_id: reviewerId },
                dataType: 'json',
                success: function(data) {
                    if (data.success) { 
                        flashcards = data.data; 
                        $('#total-flashcards').text(flashcards.length);
                        displayFlashcards(flashcards);
                        showFlashcard(currentFlashcardIndex);
                        updateFlashcardCounter();
                    } else {
                        $('#flashcard-container').html('<p>' + data.message + '</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading flashcards:", error);
                    $('#flashcard-container').html('<p>Error loading flashcards. Please try again.</p>');
                    $('#total-flashcards').text('0');
                }
            });

            $('#prev-flashcard').click(function() {
                if (currentFlashcardIndex > 0) {
                    currentFlashcardIndex--;
                    showFlashcard(currentFlashcardIndex);
                    updateFlashcardCounter();
                }
            });

            $('#next-flashcard').click(function() {
                if (currentFlashcardIndex < flashcards.length - 1) {
                    currentFlashcardIndex++;
                    showFlashcard(currentFlashcardIndex);
                    updateFlashcardCounter();
                }
            });

            $('#flip-flashcard').click(function() {
                $('.flashcard .front, .flashcard .back').toggle();
            });

            function updateFlashcardCounter() {
                $('#current-flashcard').text(currentFlashcardIndex + 1);
        }
    }

         function displayFlashcards(flashcards) {
        let html = '';
        flashcards.forEach((flashcard, index) => {
            html += `
                <div class="flashcard" style="display: ${index === 0 ? 'block' : 'none'};">
                    <div class="front">
                        <p>${flashcard.term}</p>
                    </div>
                    <div class="back">
                        <p>${flashcard.definition}</p>
                    </div>
                </div>
            `;
        });
        $('#flashcard-container').html(html); 
        updateFlashcardCounter();
    }

    function showFlashcard(index) {
        $('.flashcard').hide();
        $('.flashcard').eq(index).show();
        $('.flashcard .front').show(); 
        $('.flashcard .back').hide(); 
    }
    });
    function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function handleSubmit() {
            const formData = new FormData(document.getElementById('quiz-form'));
            fetch('submit_quiz.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('results-content').innerHTML = `
                    <p>Your score: ${data.score} out of ${data.total}</p>
                    <p>${data.message}</p>
                `;
                closePopup('confirmation-popup');
                showPopup('results-popup');
            })
            .catch(error => console.error('Error submitting quiz:', error));
        }

        function retakeQuiz() {
            location.reload(); // Reload the page to retake the quiz
        }
    </script>
</body>
</html>