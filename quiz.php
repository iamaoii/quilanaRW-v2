
<?php
include('db_connect.php');
include('auth.php');

if (!isset($_GET['assessment_id'])) {
    header('location: quiz.php');
    exit();
}

$assessment_id = $conn->real_escape_string($_GET['assessment_id']);

$assessment_query = $conn->query("SELECT * FROM assessment WHERE assessment_id = '$assessment_id'");
$assessment = $assessment_query->fetch_assoc();

$questions_query = $conn->query("
    SELECT q.*, 
           GROUP_CONCAT(
               CONCAT(qo.option_id, ':::', qo.option_txt, ':::', qo.is_right) 
               ORDER BY qo.option_id SEPARATOR '|||'
           ) as options_data
    FROM questions q
    LEFT JOIN question_options qo ON q.question_id = qo.question_id
    WHERE q.assessment_id = '$assessment_id'
    GROUP BY q.question_id
    ORDER BY q.order_by
");

$time_limit = $assessment['time_limit'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['assessment_name']); ?> | Quilana</title>
    <?php include('header.php') ?>
</head>
<body>
    <?php include('nav_bar.php') ?>
    <!-- Confirmation Popup -->
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

    <!-- Success Popup -->
    <div id="success-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <button class="popup-close" onclick="closeSuccessPopup('success-popup')">&times;</button>
            <h2 class="popup-title">Your answers have been submitted and recorded successfully!</h2>
            <div class="popup-buttons">
                <button id="result" class="secondary-button" onclick="viewResult()">View Result</button>
            </div>
        </div>
    </div>

    <!-- Timer Run Out Popup -->
    <div id="timer-runout-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">The timer ran out! You must submit your answers now!</p>
            <button id="submit-answers" class="secondary-button" onclick="handleSubmit()">Submit</button>
        </div>
    </div>

    <!-- Error Popup -->
    <div id="error-popup" class="popup-overlay" style="display: none;">
        <div class="popup-content">
            <h2 class="popup-title">An error occurred while submitting the form. Please try again.</h2>
            <div class="popup-buttons">
                <button id="error" class="secondary-button" onclick="closeErrorPopup('error-popup')">Try Again</button>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <form id="quiz-form" action="submit_quiz.php" method="POST">
            <!-- Header with submit button and timer -->
                <div class="header-container">
                    <p>Time Left: <span id="timer" class="timer"><?php echo htmlspecialchars($time_limit); ?>:00</span></p>
                    <button type="button" onclick="showPopup('confirmation-popup')" id="submit" class="secondary-button">Submit</button>
                </div>

            <!-- Quiz form will appear here if the student hasn't already taken the assessment -->
            <div class="tabs-container">
                <ul class="tabs">
                    <li class="tab-link active" data-tab="assessment-tab"><?php echo htmlspecialchars($assessment['assessment_name']); ?></li>
                </ul>
            </div>

            <div class="questions-container">
                <?php
                $question_number = 1;
                while ($question = $questions_query->fetch_assoc()) {
                    echo "<div class='question'>";
                    echo "<p><strong>$question_number. " . htmlspecialchars($question['question']) . "</strong></p>";

                    $question_type = $question['ques_type'];

                    if ($question_type == 1 || $question_type == 2) {
                        if (!empty($question['options_data'])) {
                            $options = explode('|||', $question['options_data']);
                            foreach ($options as $option_str) {
                                $option_parts = explode(':::', $option_str);
                                if (count($option_parts) >= 2) {
                                    $option_txt = htmlspecialchars($option_parts[1]);
                                    $input_type = ($question_type == 1) ? 'radio' : 'checkbox';
                                    $name_attr = ($question_type == 1) ? 'answers[' . $question['question_id'] . ']' : 'answers[' . $question['question_id'] . '][]';
                                    $required = ($question_type == 1) ? ' required' : '';
                                    
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='$input_type' name='$name_attr' value='$option_txt'$required>";
                                    echo "<label class='form-check-label'>$option_txt</label>";
                                    echo "</div>";
                                }
                            }
                        }
                    } elseif ($question_type == 3) {
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='true' required>";
                        echo "<label class='form-check-label'>True</label>";
                        echo "</div>";
                        echo "<div class='form-check'>";
                        echo "<input class='form-check-input' type='radio' name='answers[" . $question['question_id'] . "]' value='false' required>";
                        echo "<label class='form-check-label'>False</label>";
                        echo "</div>";
                    } elseif ($question_type == 4 || $question_type == 5) {
                        echo "<div class='form-check-group'>";
                        echo "<input type='text' class='form-control' name='answers[" . $question['question_id'] . "]' placeholder='Type your answer here' required>";
                        echo "</div>";
                    }
                    echo "</div>";
                    $question_number++;
                }
                ?>
                <input type="hidden" name="assessment_id" value="<?php echo $assessment_id; ?>">
                <input type="hidden" name="time_limit" value="<?php echo $time_limit; ?>">
            </div>
        </form>
    </div>

    <script>
        var timerInterval;
        var timerExpired = false;

        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;

            var storedEndTime = localStorage.getItem('endTime');
            if (storedEndTime) {
                var now = Date.now();
                timer = Math.max(0, Math.floor((storedEndTime - now) / 1000));
            } else {
                var endTime = Date.now() + (timer * 1000);
                localStorage.setItem('endTime', endTime);
            }

            updateDisplay(timer, display);

            timerInterval = setInterval(function () {
                var now = Date.now();
                var remainingTime = Math.max(0, Math.floor((localStorage.getItem('endTime') - now) / 1000));

                if (remainingTime <= 0) {
                    clearInterval(timerInterval);
                    timerExpired = true;
                    showPopup('timer-runout-popup');
                    localStorage.removeItem('endTime');
                } else {
                    updateDisplay(remainingTime, display);
                    localStorage.setItem('remainingTime', remainingTime);
                }
            }, 1000);
        }

        function updateDisplay(remainingTime, display) {
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            display.textContent = minutes + ":" + seconds;
        }

        window.onload = function () {
            var timeLimit = parseInt(document.querySelector('input[name="time_limit"]').value, 10) * 60,
                display = document.querySelector('#timer');

            startTimer(timeLimit, display);
        };

        function showPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }
        function closeSuccessPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            window.location.href = 'enroll.php#assessments-tab';
        }
        function closeErrorPopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
            handleSubmit();
        }

        function handleSubmit() {
            if (timerExpired) {
                closePopup('timer-runout-popup');
                submitForm();
            } else {
                closePopup('confirmation-popup');
                submitForm();
            }
        }

        function submitForm() {
            var formData = new FormData(document.getElementById('quiz-form'));
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'submit_quiz.php', true);

            xhr.onload = function () {
                if (xhr.status === 200) {
                    localStorage.removeItem('endTime');
                    localStorage.removeItem('remainingTime');
                    clearInterval(timerInterval);
                    showPopup('success-popup');
                } else {
                    showPopup('error-popup');
                }
            };
            xhr.send(formData);
        }

        function viewResult() {
            window.location.href = 'results.php';
        }
    </script>
</body>
</html>