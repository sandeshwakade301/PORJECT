
<?php
require "db.php";

$username = "admin";
$password = "Admin@123";
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "SELECT id FROM admins WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare(
        "UPDATE admins SET password = ? WHERE username = ?"
    );
    $stmt->bind_param("ss", $hash, $username);
    $stmt->execute();
} else {
    $stmt = $conn->prepare(
        "INSERT INTO admins (username, password) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $username, $hash);
    $stmt->execute();
}

echo "Admin ready. Username: admin";
?>
