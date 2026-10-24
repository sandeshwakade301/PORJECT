
<?php
require "db.php";

$username = "admin";
$newPassword = "Admin@123";

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "UPDATE admins SET password = ? WHERE username = ?"
);

$stmt->bind_param("ss", $hashedPassword, $username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "Admin Password Changed Successfully!";
    } else {
        echo "Admin not found or password is already the same.";
    }
} else {
    echo "Password change failed.";
}
?>
