<?php
include('db_connect.php');
include('utils.php');

if (isset($_POST['class_name']) && isset($_POST['course_name']) && isset($_POST['program_id'])) {
    $class_name = $conn->real_escape_string($_POST['class_name']);
    $course_name = $conn->real_escape_string($_POST['course_name']);
    $program_id = $conn->real_escape_string($_POST['program_id']);
    $faculty_id = $conn->real_escape_string($_POST['faculty_id']);

    $unique_code = '';
    $code_exists = true;

    while ($code_exists) {
        $unique_code = generateCode(8);
        $check_code_query = "SELECT COUNT(*) as count FROM class WHERE code = '$unique_code'";
        $result = $conn->query($check_code_query);
        $row = $result->fetch_assoc();

        if ($row['count'] == 0) {
            $code_exists = false;
        }
    }
    $sql = "INSERT INTO class (program_id, code, class_name, course_name, faculty_id) VALUES ('$program_id', '$unique_code', '$class_name', '$course_name', '$faculty_id')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 1, 'msg' => 'Class added successfully.']);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Failed to add class: ' . $conn->error]);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 0, 'msg' => 'Required fields are missing.']);
}
?>
