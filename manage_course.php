<?php
include('db_connect.php');
include('auth.php');

if(isset($_GET['program_id'])){
    $program_id = $_GET['program_id'];
    $qry = $conn->query("SELECT * FROM program WHERE program_id = ".$program_id);
    if($qry->num_rows > 0){
        $program = $qry->fetch_assoc();
        echo json_encode(['status' => 1, 'program' => $program]);
    } else {
        echo json_encode(['status' => 0, 'msg' => 'Program not found']);
    }
    exit;
}
?>
