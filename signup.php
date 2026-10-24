
<?php
require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];

    if (
        $name == "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8
    ) {
        $message = "Valid details taka. Password kamit kami 8 characters cha hava.";

    } else {

        // Email already exists check
        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Ha Email aadhich register aahe. Dusra Email vapra.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, phone, password)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashedPassword
            );

            if ($stmt->execute()) {

                // Get newly created user ID
                $user_id = $conn->insert_id;

                // Save user session
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user_id;
                $_SESSION["user_name"] = $name;

                // Go directly to Home Page
                header("Location: index.php");
                exit;

            } else {
                $message = "Account tayar zala nahi. Punha prayatna kara.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Sujay Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="signup.css">
</head>
<body>

    <h2>CREATE ACCOUNT</h2>

    <form method="post" action="signup.php">

        <input type="text" name="name"
               placeholder="Full Name" required>
        <br><br>

        <input type="email" name="email"
               placeholder="Email" required>
        <br><br>

        <input type="text" name="phone"
               placeholder="Phone Number">
        <br><br>

        <input type="password" name="password"
               placeholder="Password (minimum 8 characters)"
               minlength="8" required>
        <br><br>

        <button type="submit">SIGN UP</button>

    </form>

    <a href="index.php" class="back-button">← BACK TO HOME</a>

    <p><?= htmlspecialchars($message) ?></p>

    <a href="login.php">Already have an account? Sign In</a>

</body>
</html>
