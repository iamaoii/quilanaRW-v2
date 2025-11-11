<?php
include('db_connect.php');
include('utils.php');

if (isset($_POST['class_id'])) {
    $class_id = $conn->real_escape_string($_POST['class_id']);
    $check_sql = "SELECT class_name, course_name, code FROM class WHERE class_id = '$class_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();

        if (!empty($row['code'])) {
            echo json_encode([
                'success' => true,
                'class_name' => $row['class_name'],
                'course_name' => $row['course_name'],
                'code' => $row['code']
            ]);
        } else {
            $new_code = generateCode();
            while ($conn->query("SELECT * FROM class WHERE code = '$new_code'")->num_rows > 0) {
                $new_code = generateCode();
            }
            $update_sql = "UPDATE class SET code = '$new_code' WHERE class_id = '$class_id'";
            if ($conn->query($update_sql) === TRUE) {
                echo json_encode([
                    'success' => true,
                    'class_name' => $row['class_name'],
                    'course_name' => $row['course_name'],
                    'code' => $new_code
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save the code.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Class not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Class ID is missing.']);
}

$conn->close();
?>
