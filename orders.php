
<?php
require "db.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION["user_id"];

$sql = "SELECT
            o.id,
            o.quantity,
            o.token,
            o.payment,
            o.order_date,
            o.status,
            p.name AS product_name,
            p.price,
            p.ml,
            p.image
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ?
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Orders - Sujay Beer & Wine Shop</title>

<style>
body {
    margin: 0;
    padding: 20px;
    background: #111;
    color: white;
    font-family: Arial, sans-serif;
}

h1 {
    text-align: center;
    color: #d4af37;
}

.container {
    max-width: 850px;
    margin: 25px auto;
}

.order-card {
    display: flex;
    gap: 20px;
    align-items: center;
    background: #222;
    border: 1px solid #d4af37;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.product-image {
    width: 140px;
    height: 150px;
    object-fit: contain;
    background: white;
    border-radius: 8px;
}

.details {
    flex: 1;
    min-width: 200px;
}

.details h2 {
    color: #d4af37;
    margin-top: 0;
}

.details p {
    margin: 9px 0;
    overflow-wrap: anywhere;
}

.status {
    font-weight: bold;
}

.confirmed {
    color: #00e676;
}

.cancelled {
    color: #ff5252;
}

.pending {
    color: #d4af37;
}

.cancel-btn, .back-btn {
    display: inline-block;
    border: none;
    padding: 10px 16px;
    margin-top: 10px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    cursor: pointer;
}

.cancel-btn {
    background: #d32f2f;
    color: white;
}

.back-btn {
    background: #d4af37;
    color: black;
}

.empty {
    text-align: center;
    padding: 25px;
    background: #222;
    border-radius: 8px;
}

@media (max-width: 500px) {
    .order-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .product-image {
        width: 100%;
        height: 180px;
    }
}
</style>
</head>

<body>

<h1>MY ORDERS</h1>

<div class="container">

<?php if ($result->num_rows > 0) { ?>

    <?php while ($order = $result->fetch_assoc()) { ?>

        <?php
        $status = $order["status"] ?? "Confirmed";
        $statusClass = "pending";

        if ($status === "Confirmed") {
            $statusClass = "confirmed";
        } elseif ($status === "Cancelled") {
            $statusClass = "cancelled";
        }
        ?>

        <div class="order-card">

            <?php if (!empty($order["image"])) { ?>
                <img
                    class="product-image"
                    src="<?= htmlspecialchars($order["image"]) ?>"
                    alt="<?= htmlspecialchars($order["product_name"] ?? "Product") ?>"
                    onerror="this.style.display='none';"
                >
            <?php } ?>

            <div class="details">

                <h2>
                    <?= htmlspecialchars($order["product_name"] ?? "Product Not Found") ?>
                </h2>

                <p>
                    <b>Quantity:</b>
                    <?= (int)$order["quantity"] ?>
                </p>

                <p>
                    <b>Price:</b>
                    ₹<?= number_format((float)($order["price"] ?? 0), 2) ?>
                </p>

                <p>
                    <b>Total:</b>
                    ₹<?= number_format(
                        (float)($order["price"] ?? 0) * (int)$order["quantity"],
                        2
                    ) ?>
                </p>

                <p>
                    <b>Order Token:</b>
                    <?= htmlspecialchars($order["token"] ?? "N/A") ?>
                </p>

                <p>
                    <b>Payment:</b>
                    <?= htmlspecialchars($order["payment"] ?? "N/A") ?>
                </p>

                <p>
                    <b>Order Date:</b>
                    <?= htmlspecialchars($order["order_date"] ?? "N/A") ?>
                </p>

                <p>
                    <b>Order Status:</b>
                    <span class="status <?= $statusClass ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </p>

                <?php if ($status !== "Cancelled") { ?>
                    <form method="post" action="cancel_order.php"
                          onsubmit="return confirm('Tula hi order cancel karaychi aahe ka?');">

                        <input type="hidden" name="order_id"
                               value="<?= (int)$order["id"] ?>">

                        <button type="submit" class="cancel-btn">
                            Cancel Order
                        </button>
                    </form>
                <?php } ?>

            </div>
        </div>

    <?php } ?>

<?php } else { ?>

    <div class="empty">
        <h2>ajun koni hi order nahi</h2>
        <p>tu kele li order ethe.</p>
    </div>

<?php } ?>

<center>
    <a href="index.php" class="back-btn">← Back to Shop</a>
</center>

</div>
</body>
</html>
