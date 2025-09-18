<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = $_POST['program_id'];

    if ($program_id) {
        $stmt = $conn->prepare("SELECT DISTINCT course_name FROM class WHERE program_id = ?");
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $options = '<option value="">Select Course Name</option>';
        while ($row = $result->fetch_assoc()) {
            $options .= "<option value='".$row['course_name']."'>".$row['course_name']."</option>";
        }
        echo $options;
        $stmt->close();
    }
    $conn->close();
}
