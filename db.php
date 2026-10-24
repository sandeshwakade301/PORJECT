
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "sujay_shop");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
