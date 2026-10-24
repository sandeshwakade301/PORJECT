
<?php
require "db.php";

$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

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
    $stmt->bind_param("ss", $password, $username);
    $stmt->execute();

    echo "Admin Password Updated Successfully!";
} else {
    $stmt = $conn->prepare(
        "INSERT INTO admins (username, password) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    echo "Admin Created Successfully!";
}
?>
