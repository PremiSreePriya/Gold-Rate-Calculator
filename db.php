<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'gold_rate'; // <-- This must match the DB you created

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
