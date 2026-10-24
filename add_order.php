<?php
require "db.php";

// JSON response function
function reply($success, $message, $status = 200) {
    http_response_code($status);
    header("Content-Type: application/json; charset=UTF-8");

    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}

// User login check
if (!isset($_SESSION["user_id"]) || (int)$_SESSION["user_id"] <= 0) {
    reply(false, "Order karnyasathi Sign In kara.", 401);
}

// Only POST request allowed
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    reply(false, "Invalid request.", 405);
}

// Read order data
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    $data = $_POST;
}

$name = trim($data["product_name"] ?? "");
$quantity = filter_var(
    $data["quantity"] ?? null,
    FILTER_VALIDATE_INT
);
$address = trim($data["address"] ?? "");
$payment = trim($data["payment"] ?? "");

$allowedPayments = [
    "Cash on Delivery",
    "UPI Demo",
    "Card Demo"
];

// Validate order details
if (
    $name === "" ||
    $quantity === false ||
    $quantity === null ||
    $quantity < 1 ||
    $address === "" ||
    !in_array($payment, $allowedPayments, true)
) {
    reply(false, "Kripaya order chi sarv mahiti barobar bhara.", 400);
}

$userId = (int)$_SESSION["user_id"];

try {
    $conn->begin_transaction();

    // Find product and lock stock row
    $stmt = $conn->prepare(
        "SELECT id, price, stock
         FROM products
         WHERE name = ?
         FOR UPDATE"
    );

    $stmt->bind_param("s", $name);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        throw new Exception("Product database madhye sapadla nahi.");
    }

    $productId = (int)$product["id"];
    $stock = (int)$product["stock"];

    if ($quantity > $stock) {
        throw new Exception("Purasa stock available nahi.");
    }

    // Create unique order token
    $token = "SW-" . strtoupper(bin2hex(random_bytes(4)));

    // Save order
    $stmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, product_id, quantity, address, payment, token)
        VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "iiisss",
        $userId,
        $productId,
        $quantity,
        $address,
        $payment,
        $token
    );

    $stmt->execute();
    $stmt->close();

    // Reduce available stock
    $stmt = $conn->prepare(
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?"
    );

    $stmt->bind_param(
        "iii",
        $quantity,
        $productId,
        $quantity
    );

    $stmt->execute();

    if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new Exception("Stock update zala nahi. Punha prayatna kara.");
    }

    $stmt->close();

    $conn->commit();

    reply(
        true,
        "Order successfully save zala. Token: " . $token
    );

} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        // Rollback failed; original error is retained below.
    }

    error_log("Order Error: " . $e->getMessage());

    reply(
        false,
        "Order save zala nahi. Product, stock ani order details tapasaa.",
        400
    );
}
?>