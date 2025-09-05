<?php
include 'db_connect.php';

if (isset($_POST['reviewer_id'])) {
    $reviewer_id = $_POST['reviewer_id'];
    $qry = $conn->query("SELECT * FROM rw_reviewer WHERE reviewer_id = '$reviewer_id'");

    if ($qry->num_rows > 0) {
        $reviewer = $qry->fetch_assoc();
        echo json_encode(['success' => true, 'reviewer' => $reviewer]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Reviewer not found.']);
    }
}
?>
