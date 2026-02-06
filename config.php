<?php
$host = 'localhost';
$user = 'root'; 
$pass = '';
$db = 'placement_system';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed");
}

mysqli_set_charset($conn, "utf8mb4");
?>