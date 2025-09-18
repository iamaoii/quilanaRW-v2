<?php
include('db_connect.php');

if (isset($_POST['assessment_id'])) {
    $assessment_id = $_POST['assessment_id'];
    $assessment_name = $_POST['assessment_name'];
    $assessment_type = $_POST['assessment_type'];
    $assessment_mode = $_POST['assessment_mode'];
    $program_id = $_POST['program_id'];
    $course_name = $_POST['course_name'];
    $topic = $_POST['topic'];

    $update = $conn->query("UPDATE assessment 
                        SET assessment_name = '$assessment_name', 
                            assessment_type = '$assessment_type', 
                            assessment_mode = '$assessment_mode', 
                            program_id = '$program_id', 
                            course_name = '$course_name', 
                            topic = '$topic' 
                        WHERE assessment_id = '$assessment_id'");

    if ($update) {
        echo 1;
    } else {
        echo 0;
    }
}
?>
