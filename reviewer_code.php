<?php
include('db_connect.php');
include('utils.php');

if (isset($_POST['reviewer_id'])) {
    $reviewer_id = $conn->real_escape_string($_POST['reviewer_id']);
    $check_sql = "SELECT reviewer_name, topic, reviewer_code FROM rw_reviewer WHERE reviewer_id = '$reviewer_id'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();

        if (!empty($row['reviewer_code'])) {
            echo json_encode([
                'success' => true,
                'reviewer_name' => $row['reviewer_name'],
                'topic' => $row['topic'],
                'code' => $row['reviewer_code']
            ]);
        } else {
            $new_code = generateCode();
            while ($conn->query("SELECT * FROM rw_reviewer WHERE reviewer_code = '$new_code'")->num_rows > 0) {
                $new_code = generateCode();
            }
            $update_sql = "UPDATE rw_reviewer SET reviewer_code = '$new_code' WHERE reviewer_id = '$reviewer_id'";
            if ($conn->query($update_sql) === TRUE) {
                echo json_encode([
                    'success' => true,
                    'reviewer_name' => $row['reviewer_name'],
                    'topic' => $row['topic'],
                    'code' => $new_code
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to save the code.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Reviewer not found.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Reviewer ID is missing.']);
}

$conn->close();
?>
