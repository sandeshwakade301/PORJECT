<?php
require "db.php";

$message = "";

$count = $conn->query("SELECT COUNT(*) AS total FROM admins");
$adminExists = (int)$count->fetch_assoc()["total"] > 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$adminExists) {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username == "" || strlen($password) < 8) {
        $message = "Username taka ani password kiman 8 characters cha theva.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO admins (username, password) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $username, $hash);

        if ($stmt->execute()) {
            $message = "Admin account tayar zala. Ata hi file delete kara.";
            $adminExists = true;
        } else {
            $message = "Admin account tayar zala nahi.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
</head>
<body>
    <h2>CREATE ADMIN ACCOUNT</h2>

    <?php if (!$adminExists) { ?>
        <form method="post">
            <input name="username" placeholder="Admin Username" required>
            <br><br>
            <input type="password" name="password"
                   placeholder="Admin Password" minlength="8" required>
            <br><br>
            <button type="submit">CREATE ADMIN</button>
        </form>
    <?php } else { ?>
        <p>Admin account aadhich tayar aahe kiwa setup purna zala aahe.</p>
    <?php } ?>

    <p><?= htmlspecialchars($message) ?></p>
</body>
</html>