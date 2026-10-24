
<?php
require "db.php";

$sql = "SELECT id, name, category, price, ml, stock, image
        FROM products
        WHERE LOWER(category) = 'champagne'
        ORDER BY id ASC";

$result = $conn->query($sql);

if (!$result) {
    die("Database Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Champagne - SUJAY BEER & WINE SHOP</title>

    <style>
      
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 20px;
    font-family: Arial, sans-serif;
    background: #111111;
    color: #eeeeee;
}

header {
    text-align: center;
    padding: 20px;
    background: #080808;
    border-bottom: 2px solid #d4af37;
    margin-bottom: 25px;
}

header h1 {
    color: #d4af37;
    margin: 0 0 10px;
}

header p {
    color: #cccccc;
}

.top-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.back-btn {
    background: #d4af37;
    color: #111111;
    padding: 10px 16px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
}

.back-btn:hover {
    background: #b8962e;
}

.controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

input, select {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid #d4af37;
    background: #222222;
    color: #eeeeee;
}

input::placeholder {
    color: #aaaaaa;
}

select option {
    background: #222222;
    color: #eeeeee;
}

.products {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
}

.card {
    background: #202020;
    border: 1px solid #444444;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 3px 10px #080808;
    transition: 0.3s;
}

.card:hover {
    border-color: #d4af37;
    transform: translateY(-4px);
}

.card img {
    width: 100%;
    height: 200px;
    object-fit: contain;
    background: #202020;
    border-radius: 6px;
}

.card h3 {
    color: #d4af37;
    min-height: 40px;
}

.price {
    color: #d4af37;
    font-size: 20px;
    font-weight: bold;
}

.stock {
    color: #cccccc;
}

.order-btn {
    display: inline-block;
    background: #d4af37;
    color: #111111;
    padding: 10px 18px;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px;
    margin-top: 10px;
}

.order-btn:hover {
    background: #b8962e;
}

.out-stock {
    color: #ff5555;
    font-weight: bold;
    margin-top: 10px;
}

.empty {
    text-align: center;
    color: #ffcc00;
    padding: 30px;
    grid-column: 1 / -1;
}

footer {
    text-align: center;
    margin-top: 35px;
    padding: 15px;
    border-top: 1px solid #d4af37;
    color: #bbbbbb;
    background: #080808;
}

@media (max-width: 900px) {
    .products {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 500px) {
    .products {
        grid-template-columns: 1fr;
    }

    .top-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .controls {
        flex-direction: column;
    }

    input, select {
        width: 100%;
    }
}

    </style>
</head>

<body>

<header>
    <h1>SUJAY BEER & WINE SHOP</h1>
    <p>CHAMPAGNE COLLECTION</p>
</header>

<div class="top-buttons">
    <a href="index.php" class="back-btn">← Back to Home</a>

    <div class="controls">
        <input
            type="text"
            id="search"
            placeholder="Search Champagne..."
            oninput="filterProducts()"
        >

        <select id="sort" onchange="sortProducts()">
            <option value="">Sort By Price</option>
            <option value="low">Price: Low to High</option>
            <option value="high">Price: High to Low</option>
        </select>
    </div>
</div>

<div class="products" id="products">

    <?php if ($result->num_rows > 0) { ?>

        <?php while ($row = $result->fetch_assoc()) { ?>

            <div class="card"
                 data-name="<?php echo htmlspecialchars($row['name']); ?>"
                 data-price="<?php echo (float)$row['price']; ?>">

                <?php
                $image = trim($row['image'] ?? '');

                if ($image == '') {
                    $image = 'photos/champagne.jpg';
                }
                ?>

                <img
                    src="<?php echo htmlspecialchars($image); ?>"
                    alt="<?php echo htmlspecialchars($row['name']); ?>"
                    onerror="this.onerror=null;this.src='photos/champagne.jpg';"
                >

                <h3>
                    <?php echo htmlspecialchars($row['name']); ?>
                </h3>

                <p class="price">
                    ₹<?php echo number_format((float)$row['price'], 2); ?>
                </p>

                <p>Volume: <?php echo (int)$row['ml']; ?> ML</p>

                <p class="stock">
                    Stock: <?php echo (int)$row['stock']; ?>
                </p>

                <?php if ((int)$row['stock'] > 0) { ?>

                    <a class="order-btn"
                       href="order_champagne.php?name=<?php echo urlencode($row['name']); ?>">
                        ORDER NOW
                    </a>

                <?php } else { ?>

                    <div class="out-stock">OUT OF STOCK</div>

                <?php } ?>

            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="empty">
            No Champagne Products Found in Database.
        </div>

    <?php } ?>

</div>

<footer>
    <p>© <?php echo date("Y"); ?> SUJAY BEER & WINE SHOP</p>
</footer>

<script>
function filterProducts() {
    let search = document.getElementById("search").value.toLowerCase();
    let cards = document.querySelectorAll(".card");

    cards.forEach(function(card) {
        let name = card.dataset.name.toLowerCase();
        card.style.display = name.includes(search) ? "" : "none";
    });
}

function sortProducts() {
    let container = document.getElementById("products");
    let cards = Array.from(container.querySelectorAll(".card"));
    let sort = document.getElementById("sort").value;

    cards.sort(function(a, b) {
        let priceA = Number(a.dataset.price);
        let priceB = Number(b.dataset.price);

        if (sort === "low") {
            return priceA - priceB;
        }

        if (sort === "high") {
            return priceB - priceA;
        }

        return 0;
    });

    cards.forEach(function(card) {
        container.appendChild(card);
    });
}
</script>

</body>
</html>
