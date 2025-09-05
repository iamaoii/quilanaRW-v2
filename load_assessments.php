<?php
include('db_connect.php');
include('auth.php');

if (isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $student_id = $_SESSION['login_id'];

    // Query to get all uploaded assessments for the class
    $uploaded_assessments_query = $conn->query("
        SELECT DISTINCT a.assessment_id, a.assessment_name, a.topic, au.upload_id AS upload_id
        FROM assessment a
        JOIN administer_assessment aa ON a.assessment_id = aa.assessment_id
        JOIN assessment_uploads au ON a.assessment_id = au.assessment_id AND aa.class_id = au.class_id
        WHERE aa.class_id = '$class_id'
    ");

    // Count uploaded assessments
    $uploaded_assessments = $uploaded_assessments_query->num_rows;

    if ($uploaded_assessments == 0) {
        echo '<p class="no-assessments">No uploaded assessments available for this class.</p>';
    } else {
        echo '<div class="class-container">';
        // Display uploaded assessment details
        while ($row = $uploaded_assessments_query->fetch_assoc()) {
            echo '<div class="class-card">';
            echo '<div class="class-card-title">' . htmlspecialchars($row['assessment_name']) . '</div>';
            echo '<div class="class-card-text">Topic: ' . htmlspecialchars($row['topic']) . '</div>';

            echo '<div class="class-actions">';
            echo '<a href="view_uploaded.php?id=' . htmlspecialchars($row['assessment_id']) . '&class_id=' . htmlspecialchars($class_id) . '" class="view-assessment-link">';
            echo '<button id="viewAssessment_' . $row['assessment_id'] . '" class="main-button">View Assessment</button>';
            echo '</a>';
            echo '</div>'; 

            echo '</div>'; 
        }
        echo '</div>'; 
    }
}
?>
