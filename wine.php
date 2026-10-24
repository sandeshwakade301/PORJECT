
<?php
require "db.php";

/* FETCH WINE PRODUCTS FROM DATABASE */
$sql = "SELECT id, name, category, price, ml, stock, image
        FROM products
        WHERE LOWER(category) IN ('wine', 'redwine', 'whitewine',
        'bluewine', 'rosewine', 'sparkling', 'sparkling wine')
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

<title>Wine Collection | Sujay Beer &amp; Wine Shop</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #111111;
    color: #eeeeee;
}

header {
    background: #080808;
    color: #dddddd;
    padding: 22px 15px;
    text-align: center;
    border-bottom: 2px solid #d4af37;
}

header h1 {
    margin: 0;
    color: #d4af37;
}

header p {
    margin-bottom: 0;
    color: #bbbbbb;
}

.back-link {
    display: inline-block;
    margin: 20px;
    padding: 11px 18px;
    background: #292929;
    color: #d4af37;
    border: 1px solid #d4af37;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
}

.back-link:hover {
    background: #d4af37;
    color: #111111;
}

.heading {
    text-align: center;
    margin: 10px 0 20px;
    font-size: 30px;
    color: #d4af37;
}

.filter-area {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
    margin: 20px;
}

.search-box {
    width: 320px;
    max-width: 100%;
    padding: 13px 15px;
    border: 1px solid #555555;
    border-radius: 6px;
    background: #222222;
    color: #eeeeee;
    font-size: 16px;
    outline: none;
}

.search-box:focus {
    border-color: #d4af37;
}

.search-box::placeholder {
    color: #aaaaaa;
}

.filter-area label {
    font-weight: bold;
    color: #d4af37;
}

.filter-area select {
    padding: 12px;
    border: 1px solid #555555;
    border-radius: 5px;
    background: #222222;
    color: #eeeeee;
    font-size: 15px;
}

.filter-area select option {
    background: #222222;
    color: #eeeeee;
}

.wine-container {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    padding: 20px 25px 35px;
}

.wine-card {
    overflow: hidden;
    padding: 18px;
    background: #202020;
    color: #eeeeee;
    text-align: center;
    border: 1px solid #3d3d3d;
    border-radius: 10px;
    box-shadow: 0 3px 10px #050505;
    transition: transform 0.3s, border-color 0.3s;
}

.wine-card:hover {
    transform: translateY(-5px);
    border-color: #d4af37;
}

.wine-card img {
    width: 100%;
    height: 190px;
    object-fit: contain;
    background: #202020;
}

.wine-card h3 {
    min-height: 40px;
    margin: 12px 0;
    color: #d4af37;
}

.price {
    color: #d4af37;
    font-size: 23px;
    font-weight: bold;
}

.stock {
    color: #d4af37;
    font-weight: bold;
}

.order-btn {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 5px;
    background: #d4af37;
    color: #111;
    font-weight: bold;
    text-decoration: none;
    border: 1px solid #d4af37;
}

.order-btn:hover {
    background: #d4af37;
    color: #ffffff;
}

.out-stock {
    padding: 10px;
    background: #8f1010;
    color: #eeeeee;
    font-weight: bold;
    border-radius: 4px;
    border: 1px solid #d94343;
}

#noResults {
    display: none;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    color: #ff5555;
    padding: 30px;
}

footer {
    padding: 22px;
    background: #080808;
    color: #cccccc;
    text-align: center;
    border-top: 1px solid #d4af37;
}

@media (max-width: 1000px) {
    .wine-container {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 550px) {
    .wine-container {
        grid-template-columns: 1fr;
        padding: 15px;
    }

    .heading {
        font-size: 24px;
    }

    .wine-card img {
        height: 220px;
    }

    .search-box {
        width: 100%;
    }
}

</style>
</head>

<body>

<header>
    <h1>SUJAY BEER &amp; WINE SHOP</h1>
    <p>WINE COLLECTION</p>
</header>

<a class="back-link" href="index.php">← Back to Home</a>

<h2 class="heading">OUR WINE BRANDS</h2>

<div class="filter-area">
    <input
        type="text"
        id="searchBox"
        class="search-box"
        placeholder="Search Wine Brand..."
    >

    <label for="priceFilter">Sort by Price:</label>

    <select id="priceFilter">
        <option value="default">Default Order</option>
        <option value="low">Price: Low to High</option>
        <option value="high">Price: High to Low</option>
    </select>
</div>

<div class="wine-container" id="wineContainer">

<?php
$count = 0;

while ($wine = $result->fetch_assoc()) {
    $count++;

    $name = $wine["name"];
    $price = (float)$wine["price"];
    $ml = (int)$wine["ml"];
    $stock = (int)$wine["stock"];
    $image = $wine["image"] ?: "photos/wine.jpg";
?>

    <div class="wine-card"
         data-name="<?= htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8') ?>"
         data-price="<?= $price ?>"
         data-order="<?= $count ?>">

        <img
            src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
            onerror="this.onerror=null;this.src='photos/wine.jpg';"
        >

        <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>

        <p class="price">₹<?= number_format($price) ?></p>

        <p><?= $ml ?> ML</p>

        <?php if ($stock > 0) { ?>

            <p class="stock">
                Stock Remaining: <?= $stock ?>
            </p>

            <a class="order-btn"
               href="order_wine.php?name=<?= urlencode($name) ?>">
               ORDER NOW
            </a>

        <?php } else { ?>

            <p class="out-stock">OUT OF STOCK</p>

        <?php } ?>

    </div>

<?php
}

if ($count === 0) {
    echo '<p>No wine products found in the database.</p>';
}
?>

</div>

<p id="noResults">No wine brand found!</p>

<footer>
    <h3>SUJAY BEER &amp; WINE SHOP</h3>
    <p>Quality Products • Great Service</p>
    <p>Created By: Sujay</p>
</footer>

<script>
const container = document.getElementById("wineContainer");
const searchBox = document.getElementById("searchBox");
const priceFilter = document.getElementById("priceFilter");
const noResults = document.getElementById("noResults");

function searchWines() {
    const searchText = searchBox.value.toLowerCase().trim();
    const cards = container.querySelectorAll(".wine-card");
    let visibleCount = 0;

    cards.forEach(function(card) {
        if (card.dataset.name.includes(searchText)) {
            card.style.display = "";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    noResults.style.display = visibleCount === 0 ? "block" : "none";
}

function sortWines() {
    const cards = Array.from(container.querySelectorAll(".wine-card"));
    const value = priceFilter.value;

    cards.sort(function(a, b) {
        if (value === "low") {
            return Number(a.dataset.price) - Number(b.dataset.price);
        }

        if (value === "high") {
            return Number(b.dataset.price) - Number(a.dataset.price);
        }

        return Number(a.dataset.order) - Number(b.dataset.order);
    });

    cards.forEach(function(card) {
        container.appendChild(card);
    });

    searchWines();
}

searchBox.addEventListener("input", searchWines);
priceFilter.addEventListener("change", sortWines);
</script>

</body>
</html>
