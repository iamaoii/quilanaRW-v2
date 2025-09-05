<?php
include 'db_connect.php';
include 'auth.php';

// Check if user is logged in and redirect if not
if (!isset($_SESSION['login_user_type'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include('header.php') ?>
        <title>Dashboard | Quilana</title>
        <link rel="stylesheet" href="assets/css/faculty-dashboard.css">
        <link rel="stylesheet" href="assets/css/calendar.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
        <script src="assets/js/calendar.js" defer></script>
    </head>
    <body>
        <?php include('nav_bar.php') ?>
        <div class="content-wrapper dashboard-container">
            <!-- Summary -->
            <div class="dashboard-summary">
                <?php 
                    $name_query = $conn->query("
                        SELECT firstname FROM faculty WHERE faculty_id = '".$_SESSION['login_id']."'
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
                                                WHERE c.faculty_id = '".$_SESSION['login_id']."'");
                        $resTotalClasses = $result->fetch_assoc();
                        $totalClasses = $resTotalClasses['totalClasses'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalClasses ?> </h3>
                            <label>Total Classes</label> 
                        </div>
                    </div>
                    <!-- Total Number of Uploads -->
                    <div class="card" style="background-color: #C5F1C5"> 
                        <img class="icons" src="image/DashboardSharedIcon.png" alt="Shared Icon">
                        <?php
                        $result = $conn->query("SELECT COUNT(*) as totalUploads 
                                                FROM assessment_uploads au
                                                JOIN class c ON au.class_id = c.class_id
                                                WHERE c.faculty_id = '".$_SESSION['login_id']."'
                                            ");
                        $resTotalUploads = $result->fetch_assoc();
                        $totalUploads = $resTotalUploads['totalUploads'];
                        ?>
                        <div class="card-data">
                            <h3> <?php echo $totalUploads ?> </h3>
                            <label>Total Uploads</label> 
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="recent-assessments">
                <h1> Recent Uploads </h1>
                <div class="recent-scrollable">
                    <?php
                    $result = $conn->query("
                        SELECT a.assessment_name, c.class_name, au.upload_date
                        FROM assessment_uploads au
                        JOIN assessment a ON au.assessment_id = a.assessment_id
                        JOIN class c ON au.class_id = c.class_id
                        WHERE c.faculty_id = '".$_SESSION['login_id']."'
                        ORDER BY au.upload_date DESC
                        LIMIT 10
                    ");

                    $currentDate = '';

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $assessmentName = htmlspecialchars($row['assessment_name']);
                            $className = htmlspecialchars($row['class_name']);
                            $uploadDate = date("Y-m-d", strtotime($row['upload_date']));

                            // Divider by upload_date
                            if ($uploadDate !== $currentDate) {
                                $currentDate = $uploadDate;
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
                                            echo "<img class='icons' src='image/{$icon}' alt='Upload Icon'>";
                                        echo "</div>";
                                        echo "<div class='recent-details'>";
                                            echo "<h3>{$assessmentName}</h3>";
                                            echo "<label>{$className}</label>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='no-assessments'>No recent uploads.</p>";
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
        </div>
    </body>
</html>