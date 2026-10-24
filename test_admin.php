<?php
require "db.php";

$username = "admin";
$password = "sujay@123";

$stmt = $conn->prepare(
    "SELECT password FROM admins WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();

$admin = $stmt->get_result()->fetch_assoc();

if ($admin && password_verify($password, $admin["password"])) {
    echo "NEW PASSWORD WORKS";
} else {
    echo "PASSWORD DOES NOT MATCH";
}
?>