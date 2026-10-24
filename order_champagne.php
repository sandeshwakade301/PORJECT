
<?php
require "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$name = trim(
    $_GET["name"]
    ?? $_GET["product"]
    ?? $_GET["product_name"]
    ?? ""
);

if ($name === "") {
    die("Product name milale nahi. Wine kiwa Whisky page varun order kara.");
}

$stmt = $conn->prepare(
    "SELECT id, name, price, ml, stock
     FROM products
     WHERE TRIM(name) = ?
     LIMIT 1"
);

$stmt->bind_param("s", $name);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product sapadla nahi: " .
        htmlspecialchars($name, ENT_QUOTES, "UTF-8"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Confirm Order | Sujay Beer & Wine Shop</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 20px;
    font-family: Arial, sans-serif;
    background: #222;
    color: #222;
}

.order-box {
    max-width: 460px;
    width: 100%;
    margin: 25px auto;
    padding: 28px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    color: #d4af37;
    margin-top: 0;
    margin-bottom: 25px;
}

.product-name {
    font-size: 22px;
    font-weight: bold;
    margin-bottom: 10px;
}

.price {
    font-size: 25px;
    font-weight: bold;
    color: #d4af37;
}

.product-info {
    background: #f7f7f7;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 20px;
}

label {
    display: block;
    margin-top: 16px;
    font-weight: bold;
}

input, textarea, select {
    width: 100%;
    padding: 12px;
    margin-top: 7px;
    border: 1px solid #ccc;
    border-radius: 7px;
    font-size: 15px;
    background: white;
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #d4af37;
}

button, .back {
    display: block;
    width: 100%;
    padding: 13px;
    margin-top: 20px;
    border: none;
    border-radius: 7px;
    text-align: center;
    text-decoration: none;
    font-size: 15px;
    cursor: pointer;
}

button {
    background: #d4af37;
    color: white;
    font-weight: bold;
}

button:hover {
    background: #d4af37;
}

button:disabled {
    background: #777;
    cursor: not-allowed;
}

.back {
    background: #e5e5e5;
    color: #222;
}

.back:hover {
    background: #d2d2d2;
}

.out-stock {
    color: #c62828;
    font-weight: bold;
    text-align: center;
    padding: 12px;
}

.success-message {
    background: #e8f8ed;
    color: #d4af37;
    border: 2px solid ##d4af37;
    padding: 24px 15px;
    margin-top: 22px;
    text-align: center;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    overflow-wrap: anywhere;
}

.success-message .check {
    display: block;
    font-size: 45px;
    margin-bottom: 8px;
}

.success-message h3 {
    margin: 5px 0 12px;
}

.error-message {
    background: #fff0f0;
    color: #b00020;
    border: 2px solid #dc3545;
    padding: 15px;
    margin-top: 20px;
    border-radius: 8px;
    overflow-wrap: anywhere;
}

@media (max-width: 480px) {
    body {
        padding: 12px;
    }

    .order-box {
        padding: 20px;
        margin-top: 15px;
    }
}
</style>
</head>

<body>

<div class="order-box">

    <h2>CONFIRM YOUR ORDER</h2>

    <div class="product-info">
        <div class="product-name">
            <?= htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") ?>
        </div>

        <div class="price">
            ₹<?= number_format((float)$product["price"], 2) ?>
        </div>

        <p>Size: <?= (int)$product["ml"] ?> ML</p>

        <p>
            Stock Available:
            <strong id="stock"><?= (int)$product["stock"] ?></strong>
        </p>
    </div>

    <?php if ((int)$product["stock"] > 0) { ?>

    <form id="orderForm" action="add_order.php" method="post">

        <input type="hidden" name="product_name"
            value="<?= htmlspecialchars($product["name"], ENT_QUOTES, "UTF-8") ?>">

        <label>Quantity</label>
        <input type="number" name="quantity"
            min="1" max="<?= (int)$product["stock"] ?>"
            placeholder="Enter quantity" required>

        <label>Delivery Address</label>
        <textarea name="address" rows="3"
            placeholder="Enter your full address" required></textarea>

        <label>Payment Method</label>
        <select name="payment" required>
            <option value="">Select Payment Method</option>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="UPI Demo">UPI Demo</option>
            <option value="Card Demo">Card Demo</option>
        </select>

        <button type="submit" id="orderButton">
            CONFIRM ORDER
        </button>

    </form>

    <?php } else { ?>

        <p class="out-stock">SORRY! THIS PRODUCT IS OUT OF STOCK.</p>

    <?php } ?>

    <div id="result" aria-live="polite"></div>

    <a href="champagne.php" class="back">BACK</a>

</div>


<script>
const orderForm = document.getElementById("orderForm");
const resultBox = document.getElementById("result");
const orderButton = document.getElementById("orderButton");

if (orderForm) {
    orderForm.addEventListener("submit", async function(event) {
        event.preventDefault();

        if (orderButton.disabled) return;

        orderButton.disabled = true;
        orderButton.textContent = "Processing...";

        try {
            const response = await fetch("add_order.php", {
                method: "POST",
                body: new FormData(orderForm),
                headers: {
                    "Accept": "application/json"
                }
            });

            const data = await response.json();

            resultBox.replaceChildren();

            const messageBox = document.createElement("div");

            if (data.success) {
                messageBox.className = "success-message";

                const icon = document.createElement("div");
                icon.className = "check";
                icon.textContent = "✓";

                const title = document.createElement("h3");
                title.textContent = "Order Successfully Placed!";

                const message = document.createElement("p");
                message.textContent = data.message;

                messageBox.append(icon, title, message);
                resultBox.appendChild(messageBox);

                orderForm.style.display = "none";
            } else {
                messageBox.className = "error-message";
                messageBox.textContent =
                    data.message || "Order save zala nahi.";

                resultBox.appendChild(messageBox);
                orderButton.disabled = false;
                orderButton.textContent = "CONFIRM ORDER";
            }

        } catch (error) {
            resultBox.replaceChildren();

            const errorBox = document.createElement("div");
            errorBox.className = "error-message";
            errorBox.textContent =
                "Server kadun yogya response milala nahi. Browser madhye error tapasaa.";

            resultBox.appendChild(errorBox);

            orderButton.disabled = false;
            orderButton.textContent = "CONFIRM ORDER";
        }
    });
}
</script>
</body>
</html>


