
<?php
require "db.php";

/* FETCH WHISKY PRODUCTS FROM DATABASE */
$sql = "SELECT id, name, category, price, ml, stock, image
        FROM products
        WHERE LOWER(category) = 'whisky'
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

<title>Whisky Collection | Sujay Beer &amp; Wine Shop</title>

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

.whisky-container {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 20px;
    padding: 20px 25px 35px;
}

.whisky-card {
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

.whisky-card:hover {
    transform: translateY(-5px);
    border-color: #d4af37;
}

.whisky-card img {
    width: 100%;
    height: 190px;
    object-fit: contain;
    background: #202020;
}

.whisky-card h3 {
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
    .whisky-container {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 550px) {
    .whisky-container {
        grid-template-columns: 1fr;
        padding: 15px;
    }

    .heading {
        font-size: 24px;
    }

    .whisky-card img {
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
    <p>WHISKY COLLECTION</p>
</header>

<a class="back-link" href="index.php">← Back to Home</a>

<h2 class="heading">OUR WHISKY BRANDS</h2>

<div class="filter-area">
    <input
        type="text"
        id="searchBox"
        class="search-box"
        placeholder="Search Whisky Brand..."
    >

    <label for="priceFilter">Sort by Price:</label>

    <select id="priceFilter">
        <option value="default">Default Order</option>
        <option value="low">Price: Low to High</option>
        <option value="high">Price: High to Low</option>
    </select>
</div>

<div class="whisky-container" id="whiskyContainer">

<?php
$count = 0;

while ($whisky = $result->fetch_assoc()) {
    $count++;

    $name = $whisky["name"];
    $price = (float)$whisky["price"];
    $ml = (int)$whisky["ml"];
    $stock = (int)$whisky["stock"];
    $image = $whisky["image"] ?: "photos/whisky.jpg";
?>

    <div class="whisky-card"
         data-name="<?= htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8') ?>"
         data-price="<?= $price ?>"
         data-order="<?= $count ?>">

        <img
            src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
            alt="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
            onerror="this.onerror=null;this.src='photos/whisky.jpg';"
        >

        <h3><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></h3>

        <p class="price">₹<?= number_format($price) ?></p>

        <p><?= $ml ?> ML</p>

        <?php if ($stock > 0) { ?>

            <p class="stock">
                Stock Remaining: <?= $stock ?>
            </p>

            <a class="order-btn"
               href="order_whisky.php?name=<?= urlencode($name) ?>">
               ORDER NOW
            </a>

        <?php } else { ?>

            <p class="out-stock">OUT OF STOCK</p>

        <?php } ?>

    </div>

<?php
}

if ($count === 0) {
    echo '<p>No whisky products found in the database.</p>';
}
?>

</div>

<p id="noResults">No whisky brand found!</p>

<footer>
    <h3>SUJAY BEER &amp; WINE SHOP</h3>
    <p>Quality Products • Great Service</p>
    <p>Created By: Sujay</p>
</footer>

<script>
const container = document.getElementById("whiskyContainer");
const searchBox = document.getElementById("searchBox");
const priceFilter = document.getElementById("priceFilter");
const noResults = document.getElementById("noResults");

function searchWhiskies() {
    const searchText = searchBox.value.toLowerCase().trim();
    const cards = container.querySelectorAll(".whisky-card");
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

function sortWhiskies() {
    const cards = Array.from(
        container.querySelectorAll(".whisky-card")
    );

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

    searchWhiskies();
}

searchBox.addEventListener("input", searchWhiskies);
priceFilter.addEventListener("change", sortWhiskies);
</script>

</body>
</html>
