
<?php
require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin["password"])) {

        session_regenerate_id(true);

        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: admin.php");
        exit();

    } else {
        $message = "Admin username kiwa password chukicha aahe.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css">
</head>
<body>

<div class="login-box">

    <a href="index.php" class="back-btn">← Back</a>

    <h2>ADMIN LOGIN</h2>

    <form method="POST" action="admin_login.php">
        <input
            type="text"
            name="username"
            placeholder="Admin Username"
            autocomplete="username"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Admin Password"
            autocomplete="current-password"
            required
        >

        <button type="submit">LOGIN</button>
    </form>

    <p class="message">
        <?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?>
    </p>

</div>

</body>
</html>
