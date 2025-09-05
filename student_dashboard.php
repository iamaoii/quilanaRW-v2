<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

// Fetch scheduled to-do
$today = date('Y-m-d');
$todo_query = $conn->query("
    SELECT todo_date
    FROM rw_student_todo
    WHERE todo_date >= '$today' AND student_id = '" . $_SESSION['login_id'] . "'
");
$schedules = [];
while ($row = $todo_query->fetch_assoc()) {
    $schedules[] = $row['todo_date'];
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('header.php') ?>
        <title>Dashboard | Quilana</title>
        <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
        <link rel="stylesheet" href="assets/css/calendar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
        <script src="assets/js/calendar.js" defer></script>
    </head>
    <body>
        <?php include 'nav_bar.php'; ?>
        <div class="content-wrapper dashboard-container">
            <!-- Summary -->
            <div class="dashboard-summary">
                <?php 
                    $name_query = $conn->query("
                        SELECT firstname FROM student WHERE student_id = '".$_SESSION['login_id']."'
                    ");
                    $name = $name_query->fetch_assoc();
                    $firstname = $name['firstname'];
                ?>
                <h1> Welcome, <?php echo $firstname ?> </h1>
                <h2> Summary </h2>
                <div class="cards">
                    <!-- Total Number of Classes -->
                    <div class="card" style="background-color: #FFE2E5;">
                        <img class="icons" src="image/DashboardCoursesIcon.png" alt="Classes Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalClasses 
                                                FROM class c
                                                JOIN student_enrollment s ON c.class_id = s.class_id
                                                WHERE s.student_id = '".$_SESSION['login_id']."'
                                                AND s.status = '1'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalClasses ?> </h3>
                            <label>Total Classes</label> 
                        </div>
                    </div>
                    <!-- Total Number of Quizzes -->
                    <div class="card" style="background-color: #FADEFF"> 
                        <img class="icons" src="image/DashboardClassesIcon.png" alt="Quizzes Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalQuizzes 
                                                FROM rw_reviewer rw
                                                WHERE rw.student_id = '".$_SESSION['login_id']."'
                                                AND reviewer_type = 1
                        ");
                        $resTotalQuizzes = $result->fetch_assoc();
                        $totalQuizzes = $resTotalQuizzes['totalQuizzes'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalQuizzes ?> </h3>
                            <label>Total Quizzes</label> 
                        </div>
                    </div>
                    <!-- Total Number of Flashcards -->
                    <div class="card" style="background-color: #DCE1FC;"> 
                        <img class="icons" src="image/DashboardExamsIcon.png" alt="Exams Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalFlashcards
                                                FROM rw_reviewer rw
                                                WHERE rw.student_id = '".$_SESSION['login_id']."'
                                                AND reviewer_type = 2
                        ");
                        $resTotalFlashcards = $result->fetch_assoc();
                        $totalFlashcards = $resTotalFlashcards['totalFlashcards'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalFlashcards ?> </h3>
                            <label>Total Flashcards</label> 
                        </div>
                    </div>
                    <!-- Total Number of Shared Reviewers -->
                    <div class="card" style="background-color: #C5F1C5;"> 
                        <img class="icons" src="image/DashboardSharedIcon.png" alt="Shared Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalShared 
                                            FROM user_reviewers ur 
                                            WHERE ur.student_id = '".$_SESSION['login_id']."'");
                        $resTotalShared = $result->fetch_assoc();
                        $totalShared = $resTotalShared['totalShared'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalShared ?> </h3>
                            <label>Total Shared Reviewers</label> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recents -->
            <div class="recent-assessments">
                <h1> Recents </h1>
                <div class="recent-scrollable">
                    <?php
                    $result = $conn->query("
                        SELECT r.reviewer_name, r.topic, r.reviewer_type, ss.date_taken
                        FROM rw_student_results sr
                        JOIN rw_student_submission ss ON sr.rw_submission_id = ss.rw_submission_id
                        JOIN rw_reviewer r ON sr.reviewer_id = r.reviewer_id
                        WHERE r.reviewer_type = 1 
                        AND ss.student_id = '".$_SESSION['login_id']."'
                        ORDER BY ss.date_taken DESC
                    ");

                    $currentDate = '';

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $reviewerName = htmlspecialchars($row['reviewer_name']);
                            $reviewerTopic = htmlspecialchars($row['topic']);
                            $reviewerType = htmlspecialchars($row['reviewer_type']);
                            $dateTaken = date("Y-m-d", strtotime($row['date_taken']));

                            // Divider by date_taken
                            if ($dateTaken !== $currentDate) {
                                $currentDate = $dateTaken;
                                echo "<div class='assessment-separator'>";
                                echo "<span class='date'> " . $currentDate . "</span>";
                                echo "<hr class='separator-line'>";
                                echo "</div>";
                            }

                            $bgColor = '#FADEFF'; 
                            $icon = 'DashboardClassesIcon.png'; 


                            echo "<div id='recents' class='cards'>";
                                echo "<div id='recent-card' class='card' style='background-color: {$bgColor};'>";
                                    echo "<div id='recent-data' class='card-data'>";
                                        echo "<div class='recent-icon'>";
                                            echo "<img class='icons' src='image/{$icon}' alt='Quiz Icon'>";
                                        echo "</div>";
                                        echo "<div class='recent-details'>";
                                            echo "<h3>{$reviewerName}</h3>";
                                            echo "<label>{$reviewerTopic}</label>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-assessments'>No recent test reviewers taken</p>";
                    }
                    ?>
                </div>
            </div>

            <!-- Calendar -->
            <div class="dashboard-calendar">
                <div class="wrapper">
                    <header>
                        <div class="icons">
                            <span id="prev" class="material-symbols-rounded">chevron_left</span>
                        </div>
                        <p class="current-date"></p>
                        <div class="icons">
                            <span id="next" class="material-symbols-rounded">chevron_right</span>
                        </div>
                    </header>
                    <div class="calendar">
                        <ul class="weeks">
                        <li>Sun</li>
                        <li>Mon</li>
                        <li>Tue</li>
                        <li>Wed</li>
                        <li>Thu</li>
                        <li>Fri</li>
                        <li>Sat</li>
                        </ul>
                        <ul class="days"></ul>
                    </div>
                </div>
            </div>

            <!-- To Do List -->
            <div class="dashboard-todo">
                <div class="todo-header">
                    <h1>To-Do List</h1>
                </div>
                <div class="todo-input-container">
                    <input type="text" placeholder="Add a new task" id="todo-input">
                    <button class="secondary-button" id="todo-add-btn"><i class="fas fa-add"></i> Add</button>
                </div>
                <div class="todo-list" id="todo-list">
                    <?php 
                    // Fetch all scheduled to-do tasks
                    $todo_tasks_query = $conn->query("
                        SELECT todo_id, todo_text, todo_date
                        FROM rw_student_todo
                        WHERE student_id = '" . $_SESSION['login_id'] . "' AND todo_date >= '$today'
                        ORDER BY todo_date ASC
                    ");

                    // Initialize currect date for display
                    $currentDate = '';

                    // Check if there is/are any tasks scheduled
                    if ($todo_tasks_query->num_rows > 0) {
                        // Display task details
                        while ($row = $todo_tasks_query->fetch_assoc()) {
                            if ($row['todo_date'] !== $currentDate) {
                                $currentDate = $row['todo_date'];
                                echo "<div id='schedule-separator' class='assessment-separator'>";
                                echo "<span id='date' class='date'> " . $currentDate . "</span>";
                                echo "<hr class='separator-line'>";
                                echo "</div>";
                            }

                            echo "<div class='schedule-item'>";
                            echo "<h3>" . htmlspecialchars($row['todo_text']) . "</h3>";
                            echo "<div class='btns'>";
                            echo "<button 
                                class='delete' 
                                id='delete-todo'
                                data-value='" .htmlspecialchars($row['todo_id']) . "'>
                                <i class='fas fa-trash-alt'></i> 
                                </button>";
                            echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-assessments'>You have no scheduled tasks!</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <script>
            const scheduledDates = <?php echo json_encode($schedules); ?>;

            // Function to add scheduled to-do task
            document.getElementById("todo-add-btn").onclick = function(e) {
                e.preventDefault();

                const today = new Date();

                if (!selectedDate) {
                    selectedDate = `${currYear}-${currMonth + 1}-${date.getDate()}`;
                    console.log("set the date to today's date: ", selectedDate);
                } else {
                    if (selectedDate < today) {
                        alert('Make sure to pick a date that is either today or in the upcoming days.'); // Show error message
                        return;
                    }
                }

                // Gather data needed
                const student_id = <?php echo $_SESSION['login_id']?> ;
                const todo_text = document.getElementById("todo-input").value;

                console.log("Selected Date: ", selectedDate);
                console.log("Student ID: ", student_id);
                console.log("To-Do Text: ", todo_text);

                // Send data to save_todo.php via AJAX
                $.ajax({
                    url: 'save_todo.php',
                    method: 'POST',
                    data: {
                        todo_date: selectedDate,
                        student_id: student_id,
                        todo_text: todo_text
                    },
                    success: function(response) {
                        console.log(response);
                        if (response === 'success') {
                            location.reload();
                        } else if (response === 'error') {
                            alert('Failed to add to-do task. Please try again');
                        }
                    },
                    error: function() {
                        alert('An error occurred while trying to save the to-do task. Please try again');
                    }
                })
            }

            // Function to delete task
            $(".btns").on("click", ".delete", function(e) {
                e.preventDefault();
                const todo_id = $(this).data('value');

                console.log("To-Do ID: ", todo_id);

                // Send data to save_todo.php via AJAX
                $.ajax({
                    url: 'delete_todo.php',
                    method: 'POST',
                    data: { todo_id: todo_id },
                    success: function(response) {
                        console.log(response);
                        if (response === 'success') {
                            location.reload();
                        } else if (response === 'error') {
                            alert('Failed to delete to-do task. Please try again');
                        }
                    },
                    error: function() {
                        alert('An error occurred while trying to delete the to-do task. Please try again');
                    }
                })
            });
        </script>
    </body>
</html>