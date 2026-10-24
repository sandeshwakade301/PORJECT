<?php
require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, name, password FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];

        header("Location: index.php");
        exit;
    } else {
        $message = "Email kiwa password chukicha aahe.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In - Sujay Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
     <link rel="stylesheet" href="style.css">

     <link rel="stylesheet" href="login.css">
</head>
<body>

<h2>USER SIGN IN</h2>

<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <br><br>

    <input type="password" name="password" placeholder="Password" required>
    <br><br>

    <button type="submit">SIGN IN</button>
</form>
 <a href="index.php" class="back-button">← BACK TO HOME</a>
<p><?= htmlspecialchars($message) ?></p>

<a href="signup.php">Create New Account</a>

</body>
</html>