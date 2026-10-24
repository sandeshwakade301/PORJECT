
<?php
require "db.php";

// Admin login security
if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$messageType = "success";

// CSRF protection
if (empty($_SESSION["admin_csrf"])) {
    $_SESSION["admin_csrf"] = bin2hex(random_bytes(32));
}

// Check database table columns
function tableColumns($conn, $table) {
    $allowed = ["products", "users", "orders"];

    if (!in_array($table, $allowed, true)) {
        return [];
    }

    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    $columns = [];

    while ($row = $result->fetch_assoc()) {
        $columns[] = $row["Field"];
    }

    return $columns;
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Handle dashboard actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (
            !isset($_POST["csrf_token"]) ||
            !hash_equals($_SESSION["admin_csrf"], $_POST["csrf_token"])
        ) {
            throw new Exception("Invalid request. Please refresh the page.");
        }

        $action = $_POST["action"] ?? "";

        // ADD PRODUCT
        if ($action === "add_product") {
            $name = trim($_POST["name"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $price = (float)($_POST["price"] ?? 0);
            $ml = (int)($_POST["ml"] ?? 0);
            $stock = (int)($_POST["stock"] ?? 0);
            $image = trim($_POST["image"] ?? "");

            if (
                $name === "" || $category === "" ||
                $price < 0 || $ml <= 0 || $stock < 0
            ) {
                throw new Exception("Please enter valid product details.");
            }

            $stmt = $conn->prepare(
                "INSERT INTO products (name, category, price, ml, stock, image)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssdiss",
                $name, $category, $price, $ml, $stock, $image
            );
            $stmt->execute();
            $stmt->close();

            $message = "Product added successfully!";
        }

        // UPDATE PRODUCT
        elseif ($action === "update_product") {
            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["name"] ?? "");
            $category = trim($_POST["category"] ?? "");
            $price = (float)($_POST["price"] ?? 0);
            $ml = (int)($_POST["ml"] ?? 0);
            $stock = (int)($_POST["stock"] ?? 0);
            $image = trim($_POST["image"] ?? "");

            if (
                $id <= 0 || $name === "" || $category === "" ||
                $price < 0 || $ml <= 0 || $stock < 0
            ) {
                throw new Exception("Invalid product details.");
            }

            $stmt = $conn->prepare(
                "UPDATE products
                 SET name=?, category=?, price=?, ml=?, stock=?, image=?
                 WHERE id=?"
            );
            $stmt->bind_param(
                "ssdissi",
                $name, $category, $price, $ml, $stock, $image, $id
            );
            $stmt->execute();
            $stmt->close();

            $message = "Product updated successfully!";
        }

        // DELETE PRODUCT
        elseif ($action === "delete_product") {
            $id = (int)($_POST["id"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid product ID.");
            }

            // Keep historical orders intact
            $stmt = $conn->prepare(
                "SELECT COUNT(*) AS total FROM orders WHERE product_id=?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $used = $stmt->get_result()->fetch_assoc()["total"];
            $stmt->close();

            if ((int)$used > 0) {
                throw new Exception(
                    "This product has order history. Set its stock to 0 instead."
                );
            }

            $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $message = "Product deleted successfully!";
        }

        // UPDATE ORDER STATUS
        elseif ($action === "order_status") {
            $id = (int)($_POST["id"] ?? 0);
            $status = $_POST["status"] ?? "";

            $allowedStatuses = ["Pending", "Confirmed", "Cancelled", "Delivered"];

            if ($id <= 0 || !in_array($status, $allowedStatuses, true)) {
                throw new Exception("Invalid order status.");
            }

            $orderColumns = tableColumns($conn, "orders");

            if (!in_array("status", $orderColumns, true)) {
                throw new Exception(
                    "The orders table needs a status column. Follow the SQL instructions below."
                );
            }

            $stmt = $conn->prepare(
                "UPDATE orders SET status=? WHERE id=?"
            );
            $stmt->bind_param("si", $status, $id);
            $stmt->execute();
            $stmt->close();

            $message = "Order status updated!";
        }

        // DELETE ORDER
        elseif ($action === "delete_order") {
            $id = (int)($_POST["id"] ?? 0);

            if ($id <= 0) {
                throw new Exception("Invalid order ID.");
            }

            $stmt = $conn->prepare("DELETE FROM orders WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                $stmt->close();
                throw new Exception("Order not found.");
            }

            $stmt->close();
            $message = "Order deleted. Stock was not automatically changed.";
        }

        else {
            throw new Exception("Unknown action.");
        }

        // Prevent resubmission on refresh
        $_SESSION["admin_csrf"] = bin2hex(random_bytes(32));
        header("Location: admin.php?message=" . urlencode($message));
        exit;

    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }
}

if (isset($_GET["message"])) {
    $message = (string)$_GET["message"];
}

// Load dashboard information
try {
    $productColumns = tableColumns($conn, "products");
    $userColumns = tableColumns($conn, "users");
    $orderColumns = tableColumns($conn, "orders");

    foreach ([
        "products" => ["id", "name", "category", "price", "ml", "stock", "image"],
        "users" => ["id", "name", "email"],
        "orders" => [
            "id", "user_id", "product_id", "quantity",
            "address", "payment", "token", "order_date"
        ]
    ] as $table => $requiredColumns) {
        $existing = tableColumns($conn, $table);
        $missing = array_diff($requiredColumns, $existing);

        if ($missing) {
            throw new Exception(
                "Table " . $table . " is missing columns: " .
                implode(", ", $missing)
            );
        }
    }

    $stats = $conn->query("
        SELECT
            COUNT(*) AS total_products,
            COALESCE(SUM(stock), 0) AS total_stock,
            COALESCE(SUM(stock = 0), 0) AS out_of_stock,
            COALESCE(SUM(stock > 0 AND stock <= 5), 0) AS low_stock
        FROM products
    ")->fetch_assoc();

    $totalUsers = (int)$conn->query(
        "SELECT COUNT(*) AS total FROM users"
    )->fetch_assoc()["total"];

    $totalOrders = (int)$conn->query(
        "SELECT COUNT(*) AS total FROM orders"
    )->fetch_assoc()["total"];

    $products = $conn->query(
        "SELECT id, name, category, price, ml, stock, image
         FROM products ORDER BY id DESC"
    );

    $users = $conn->query(
        "SELECT id, name, email" .
        (in_array("phone", $userColumns, true) ? ", phone" : "") .
        (in_array("created_at", $userColumns, true) ? ", created_at" : "") .
        " FROM users ORDER BY id DESC"
    );

    $statusSelect = in_array("status", $orderColumns, true)
        ? "o.status"
        : "'Pending' AS status";

    $orders = $conn->query("
        SELECT
            o.id, o.token, o.quantity, o.address,
            o.payment, o.order_date,
            $statusSelect,
            u.name AS customer_name,
            u.email,
            p.name AS product_name,
            p.price,
            p.ml
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN products p ON o.product_id = p.id
        ORDER BY o.id DESC
    ");

} catch (Throwable $e) {
    http_response_code(500);
    die(
        "<h2>Dashboard Database Error</h2><p>" .
        h($e->getMessage()) .
        "</p><p>Check your sujay_shop tables and columns in phpMyAdmin.</p>"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sujay Shop Admin Dashboard</title>

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    background: #101010;
    color: #eee;
    font-family: Arial, sans-serif;
}
header {
    background: #080808;
    padding: 20px 4%;
    border-bottom: 2px solid #c9a227;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
header h1 { color: #d4af37; margin: 0; font-size: 23px; }
header a {
    color: #fff;
    border: 1px solid #c9a227;
    padding: 10px 14px;
    border-radius: 5px;
    text-decoration: none;
}
nav {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 15px 4%;
    background: #191919;
}
nav a {
    color: #eee;
    text-decoration: none;
    padding: 10px 13px;
    border-radius: 5px;
}
nav a:hover { background: #3a3015; color: #f2d36f; }
main { width: 94%; max-width: 1500px; margin: 25px auto; }
h2 { color: #d4af37; margin-top: 30px; }
.notice {
    padding: 13px;
    margin: 15px 0;
    border-radius: 5px;
    background: #173922;
    color: #b7f4c6;
}
.notice.error { background: #512323; color: #ffd0d0; }
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 15px;
}
.card {
    background: #1c1c1c;
    border: 1px solid #55451a;
    padding: 20px;
    border-radius: 9px;
}
.card h3 { color: #d4af37; font-size: 14px; }
.card strong { font-size: 30px; }
.panel {
    margin-top: 18px;
    padding: 18px;
    background: #1a1a1a;
    border: 1px solid #343434;
    border-radius: 9px;
}
.table-wrap { overflow-x: auto; margin-top: 15px; }
table { width: 100%; min-width: 850px; border-collapse: collapse; }
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #363636;
    vertical-align: top;
}
th { background: #272727; color: #e5c65c; }
input, select {
    width: 100%;
    min-width: 90px;
    padding: 9px;
    margin: 4px 0;
    background: #101010;
    color: #fff;
    border: 1px solid #555;
    border-radius: 4px;
}
button, .btn {
    display: inline-block;
    border: 0;
    padding: 9px 12px;
    margin: 3px 2px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
}
.primary { background: #c9a227; color: #111; }
.green { background: #23864c; color: white; }
.orange { background: #a76d13; color: white; }
.red { background: #a82c2c; color: white; }
form.inline { display: inline-block; }
form.inline select { width: auto; }
.status {
    display: inline-block;
    padding: 5px 8px;
    background: #343434;
    border-radius: 4px;
}
.add-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
}
.small { color: #bbb; font-size: 12px; }

/* Order search box */
.order-search-box {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}
.order-search-box input {
    flex: 1;
    min-width: 220px;
    padding: 13px;
    font-size: 15px;
}
.order-search-box button {
    padding: 13px 18px;
}
.search-info {
    color: #d4af37;
    margin-top: 10px;
    font-size: 13px;
}
footer { padding: 25px; text-align: center; color: #aaa; }
</style>
</head>

<body>
<header>
    <h1>SUJAY BEER &amp; WINE SHOP</h1>
    <a href="admin_logout.php" class="logout-btn">LOGOUT</a>
</header>

<nav>
    <a href="#overview">Dashboard</a>
    <a href="#products">Stock / Products</a>
    <a href="#orders">Orders</a>
    <a href="#customers">Customers</a>
</nav>

<main>
<?php if ($message !== ""): ?>
    <div class="notice <?= $messageType === "error" ? "error" : "" ?>">
        <?= h($message) ?>
    </div>
<?php endif; ?>

<section id="overview">
    <h2>Dashboard Overview</h2>

    <div class="cards">
        <div class="card">
            <h3>Total Products</h3>
            <strong><?= (int)$stats["total_products"] ?></strong>
        </div>
        <div class="card">
            <h3>Remaining Stock (Units)</h3>
            <strong><?= (int)$stats["total_stock"] ?></strong>
        </div>
        <div class="card">
            <h3>Low Stock (1–5 Units)</h3>
            <strong><?= (int)$stats["low_stock"] ?></strong>
        </div>
        <div class="card">
            <h3>Out of Stock Products</h3>
            <strong><?= (int)$stats["out_of_stock"] ?></strong>
        </div>
        <div class="card">
            <h3>Total Customers</h3>
            <strong><?= $totalUsers ?></strong>
        </div>
        <div class="card">
            <h3>Total Orders</h3>
            <strong><?= $totalOrders ?></strong>
        </div>
    </div>
</section>

<section id="products">
    <h2>Stock &amp; Product Management</h2>

    <div class="panel">
        <h3>Add New Product</h3>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION["admin_csrf"]) ?>">
            <input type="hidden" name="action" value="add_product">

            <div class="add-grid">
                <input name="name" placeholder="Product Name" required>
                <input name="category" placeholder="Category" required>
                <input type="number" name="price" placeholder="Price (₹)" min="0" step="0.01" required>
                <input type="number" name="ml" placeholder="ML" min="1" required>
                <input type="number" name="stock" placeholder="Stock Quantity" min="0" required>
                <input name="image" placeholder="Image path e.g. images/beer.jpg">
            </div>

            <button class="primary" type="submit">Add Product</button>
            
        </form>
    </div>
    <!-- Product Search Box -->
   <div class="panel">
    <input type="text"
    id="productSearch"
    placeholder="Search Product by ID, Name, Category, Price or Stock..."
    autocomplete="off"
    aria-label="Search products"
>

<div class="search-info" id="productSearchInfo">
    Showing all products.
</div></div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Product</th><th>Category</th>
                    <th>Price</th><th>ML</th><th>Stock</th><th>Update / Delete</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <form method="post">
                    <td>
                        <?= (int)$p["id"] ?>
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION["admin_csrf"]) ?>">
                        <input type="hidden" name="action" value="update_product">
                        <input type="hidden" name="id" value="<?= (int)$p["id"] ?>">
                    </td>
                    <td><input name="name" value="<?= h($p["name"]) ?>" required></td>
                    <td><input name="category" value="<?= h($p["category"]) ?>" required></td>
                    <td><input type="number" name="price" min="0" step="0.01" value="<?= h($p["price"]) ?>" required></td>
                    <td><input type="number" name="ml" min="1" value="<?= (int)$p["ml"] ?>" required></td>
                    <td>
                        <input type="number" name="stock" min="0" value="<?= (int)$p["stock"] ?>" required>
                        <?php if ((int)$p["stock"] === 0): ?>
                            <span class="small">OUT OF STOCK</span>
                        <?php elseif ((int)$p["stock"] <= 5): ?>
                            <span class="small">LOW STOCK</span>
                        <?php endif; ?>
                        <input name="image" value="<?= h($p["image"] ?? "") ?>" placeholder="Image path">
                    </td>
                    <td>
                        <button class="primary" type="submit">Save Changes</button>
                    </td>
                    </form>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION["admin_csrf"]) ?>">
                            <input type="hidden" name="action" value="delete_product">
                            <input type="hidden" name="id" value="<?= (int)$p["id"] ?>">
                            <button class="red" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>

<section id="orders">
    <h2>Order Management</h2>

    <!-- NEW ORDER SEARCH BOX -->
    <div class="panel">
        <div class="order-search-box">
            <input
                type="text"
                id="orderSearch"
                placeholder="Search by Token ID, Product Name, User Name, Order ID or Email..."
                autocomplete="off"
                aria-label="Search orders"
            >
            <button type="button" class="primary" onclick="searchOrders()">
                Search
            </button>
            <button type="button" class="orange" onclick="clearOrderSearch()">
                Clear
            </button>
        </div>
        <div class="search-info" id="searchInfo">
            Showing all orders.
        </div>
    </div>

    <div class="table-wrap">
        <table id="ordersTable">
            <thead>
                <tr>
                    <th>ID / Token</th><th>Customer</th><th>Product</th>
                    <th>Qty</th><th>Unit Price</th><th>Subtotal</th>
                    <th>Address</th><th>Payment</th><th>Date</th>
                    <th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($orders->num_rows > 0): ?>
                <?php while ($o = $orders->fetch_assoc()): ?>
                    <?php
                    $qty = (int)$o["quantity"];
                    $price = (float)($o["price"] ?? 0);
                    $status = $o["status"] ?? "Pending";
                    ?>
                    <tr>
                        <td>
                            #<?= (int)$o["id"] ?><br>
                            <?= h($o["token"] ?? "-") ?>
                        </td>
                        <td>
                            <?= h($o["customer_name"] ?? "Unknown") ?><br>
                            <span class="small"><?= h($o["email"] ?? "") ?></span>
                        </td>
                        <td>
                            <?= h($o["product_name"] ?? "Product unavailable") ?><br>
                            <span class="small"><?= (int)($o["ml"] ?? 0) ?> ML</span>
                        </td>
                        <td><?= $qty ?></td>
                        <td>₹<?= number_format($price, 2) ?></td>
                        <td>₹<?= number_format($price * $qty, 2) ?></td>
                        <td><?= h($o["address"] ?? "-") ?></td>
                        <td><?= h($o["payment"] ?? "-") ?></td>
                        <td><?= h($o["order_date"] ?? "-") ?></td>
                        <td><span class="status"><?= h($status) ?></span></td>
                        <td>
                            <?php if (in_array("status", $orderColumns, true)): ?>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["admin_csrf"]) ?>">
                                    <input type="hidden" name="action" value="order_status">
                                    <input type="hidden" name="id" value="<?= (int)$o["id"] ?>">

                                    <select name="status">
                                        <?php foreach (["Pending", "Confirmed", "Cancelled", "Delivered"] as $s): ?>
                                            <option value="<?= h($s) ?>" <?= $status === $s ? "selected" : "" ?>>
                                                <?= h($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="green" type="submit">Update</button>
                                </form>
                            <?php else: ?>
                                <span class="small">Add status column to manage status.</span>
                            <?php endif; ?>

                            <form method="post" onsubmit="return confirm('Permanently delete this order?');">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION["admin_csrf"]) ?>">
                                <input type="hidden" name="action" value="delete_order">
                                <input type="hidden" name="id" value="<?= (int)$o["id"] ?>">
                                <button class="red" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11" class="no-orders">No orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section id="customers">
    <h2>Customer Management</h2>

    
<div class="panel">
    <input
        type="text"
        id="customerSearch"
        placeholder="Customer name, Mobail no. and Email seach..."
        autocomplete="off"
        style="width:100%; padding:12px; margin-bottom:10px;"
    >

    <div id="customerSearchInfo">
       All customer displya..
    </div>
</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th>
                    <?php if (in_array("phone", $userColumns, true)): ?><th>Phone</th><?php endif; ?>
                    <?php if (in_array("created_at", $userColumns, true)): ?><th>Registered At</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($users->num_rows > 0): ?>
                <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int)$u["id"] ?></td>
                        <td><?= h($u["name"]) ?></td>
                        <td><?= h($u["email"]) ?></td>
                        <?php if (array_key_exists("phone", $u)): ?><td><?= h($u["phone"]) ?></td><?php endif; ?>
                        <?php if (array_key_exists("created_at", $u)): ?><td><?= h($u["created_at"]) ?></td><?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No customers registered.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<footer>
    SUJAY BEER &amp; WINE SHOP — Admin Dashboard
</footer>
</main>

<script>
function searchOrders() {
    const input = document.getElementById("orderSearch");
    const filter = input.value.trim().toLowerCase();
    const table = document.getElementById("ordersTable");
    const tbody = table.querySelector("tbody");
    const rows = tbody.querySelectorAll("tr");
    const info = document.getElementById("searchInfo");

    let visibleCount = 0;
    let searchableRows = 0;

    rows.forEach(function(row) {
        if (row.querySelector(".no-orders")) {
            row.style.display = filter === "" ? "" : "none";
            return;
        }

        searchableRows++;

        const rowText = row.innerText.toLowerCase();

        if (rowText.includes(filter)) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });

    if (filter === "") {
        info.textContent = "Showing all orders.";
    } else {
        info.textContent = "Matching orders: " + visibleCount;
    }
}

function clearOrderSearch() {
    document.getElementById("orderSearch").value = "";
    searchOrders();
}

document.getElementById("orderSearch").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        searchOrders();
    }
});

document.getElementById("orderSearch").addEventListener("input", searchOrders);

function searchProducts() {
    const filter = document.getElementById("productSearch")
        .value.trim().toLowerCase();

    const rows = document.querySelectorAll(
        "#products .table-wrap table tbody tr"
    );

    const info = document.getElementById("productSearchInfo");
    let count = 0;

    rows.forEach(function(row) {
        if (row.innerText.toLowerCase().includes(filter)) {
            row.style.display = "";
            count++;
        } else {
            row.style.display = "none";
        }
    });

    info.textContent = filter === ""
        ? "Showing all products."
        : "Matching products: " + count;
}

document.getElementById("productSearch").addEventListener(
    "input",
    searchProducts
);


function searchProducts() {
    var searchBox = document.getElementById("productSearch");

    if (!searchBox) {
        return;
    }

    var searchText = searchBox.value.toLowerCase().trim();

    var rows = document.querySelectorAll(
        "#products .table-wrap table tbody tr"
    );

    var count = 0;

    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();

        var inputs = row.querySelectorAll("input");

        inputs.forEach(function(input) {
            text += " " + input.value.toLowerCase();
        });

        if (text.includes(searchText)) {
            row.style.display = "";
            count++;
        } else {
            row.style.display = "none";
        }
    });

    var info = document.getElementById("productSearchInfo");

    if (info) {
        info.textContent = searchText === ""
            ? ""
            : "" + count;
    }
}

document.getElementById("productSearch").addEventListener(
    "input",
    searchProducts
);


function searchCustomers() {
    var input = document.getElementById("customerSearch");
    var filter = input.value.toLowerCase().trim();

    var rows = document.querySelectorAll(
        "#customers .table-wrap tbody tr"
    );

    var count = 0;

    rows.forEach(function(row) {
        var text = row.innerText.toLowerCase();

        var inputs = row.querySelectorAll("input");

        inputs.forEach(function(item) {
            text += " " + item.value.toLowerCase();
        });

        if (text.includes(filter)) {
            row.style.display = "";
            count++;
        } else {
            row.style.display = "none";
        }
    });

    document.getElementById("customerSearchInfo").textContent =
        filter === ""
        ? "all customer displya..."
        : "customer search: " + count;
}

document.getElementById("customerSearch").addEventListener(
    "input",
    searchCustomers
);

</script>

</body>
</html>
