<?php
$conn = new mysqli('localhost', 'admin', 'Admin12345!', 'quilana');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
