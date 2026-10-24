
<?php
require "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: orders.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$orderId = (int) ($_POST["order_id"] ?? 0);

if ($orderId <= 0) {
    die("Invalid Order ID!");
}

$conn->begin_transaction();

try {
    // User chi order ani status check kara
    $stmt = $conn->prepare(
        "SELECT product_id, quantity, status
         FROM orders
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );

    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();

    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order sapadli nahi.");
    }

    if ($order["status"] === "Cancelled") {
        throw new Exception("Hi order aadhich cancel zali aahe.");
    }

    // Order cancel kara
    $stmt = $conn->prepare(
        "UPDATE orders
         SET status = 'Cancelled'
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $stmt->close();

    // Stock parat vadhva
    $productId = (int) $order["product_id"];
    $quantity = (int) $order["quantity"];

    $stmt = $conn->prepare(
        "UPDATE products
         SET stock = stock + ?
         WHERE id = ?"
    );

    $stmt->bind_param("ii", $quantity, $productId);
    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception("Product stock update zala nahi.");
    }

    $stmt->close();

    $conn->commit();

    header("Location: orders.php");
    exit;

} catch (Throwable $e) {
    $conn->rollback();

    echo "<h3>Order cancel zali nahi!</h3>";
    echo htmlspecialchars($e->getMessage());
    echo '<br><a href="orders.php">Back to My Orders</a>';
}
?>
