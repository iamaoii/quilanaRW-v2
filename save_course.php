<?php
include('db_connect.php');
include('auth.php');

if(isset($_POST['program_name'])){
    $program_id = isset($_POST['program_id']) ? $_POST['program_id'] : '';
    $faculty_id = $_POST['faculty_id'];
    $program_name = $_POST['program_name'];

    if(empty($program_id)){
        // Insert new program
        $qry = $conn->query("INSERT INTO program (faculty_id, program_name) VALUES ('$faculty_id', '$program_name')");
    } else {
        // Update existing program
        $qry = $conn->query("UPDATE program SET program_name='$program_name' WHERE program_id='$program_id' AND faculty_id='$faculty_id'");
    }

    if($qry){
        echo json_encode(['status' => 1]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Failed to save program']);
    }
}
?>
