<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userName = $_SESSION["user_name"] ?? "Guest User";
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SUJAY BEER & WINE SHOP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="logo">SW</div>

    <div class="shop-title">
        <h1>SUJAY BEER & WINE SHOP</h1>
        <p>BEER • WINE • WHISKY • CHAMPAGNE • VODKA</p>
    </div>

    <div class="header-right">

     <span id="welcomeUser">
    👤<?= htmlspecialchars($_SESSION["user_name"] ?? "Guest User") ?> 
     </span>
       
<a href="orders.php" class="my-orders-btn">MY ORDERS</a>

        <button class="menu-button" onclick="openMenu()">☰</button>
        <div class="admin-center">
            <a href="admin_login.php">⚙️</a>
        </div>
    </div>
</header>

<!-- MENU -->
<nav id="menu" class="menu">
    <a href="login.php">SIGN IN</a>
    <a href="signup.php">SIGN UP</a>
    <a href="logout.php">LOGOUT</a>
</nav>

<!-- WELCOME -->
<section class="welcome">
    <div class="welcome-text">
        <h2>WELCOME TO<br>SUJAY WINE SHOP</h2>
        <h3>Enjoy Party & Happy Moments</h3>
        <p>Quality Products • Great Service</p>
    </div>

    <div class="photo-slider">
        <img src="https://imgs.search.brave.com/IBTyqTPETUzAXiJ-sKOzZjnTFyZPFyADZCtEhudggdo/rs:fit:500:0:1:0/g:ce/aHR0cHM6Ly90My5m/dGNkbi5uZXQvanBn/LzE5LzEyLzMxLzAy/LzM2MF9GXzE5MTIz/MTAyNzhfMUNWQ1Mx/VGJscnpVNUZmYWlv/UjF1WE14Q3NodkZI/dGouanBn" alt="Shop Photo">
    </div>
</section>

<!-- CATEGORY TABLE -->
<section class="category-section">
    <h2>OUR CATEGORIES</h2>

    <table class="category-table">
        <thead>
            <tr>
                
        </thead>

        <tbody>

                <td><a href="beer.php" onclick="filterProducts('beer')">View Beer</a></td>
            

                <td><a href="wine.php" onclick="filterProducts('wine')">View Wine</a></td>
 

                <td><a href="whisky.php" onclick="filterProducts('whisky')">View Whisky</a></td>
 

                <td><a href="vodka.php" onclick="filterProducts('vodka')">View Vodka</a></td>
 

                <td><a href="champagne.php" onclick="filterProducts('champagne')">View Champagne</a></td>
    

                <td><a href="soda.php" onclick="filterProducts('sparkling')">View soda</a></td>
            </tr>
        </tbody>
    </table>
</section>


<!-- ORDER POPUP -->
<div id="orderBox" class="popup">
    <div class="popup-content">
        <button class="close-button" onclick="closeOrder()">×</button>

        <h2>ORDER INFORMATION</h2>
        <h3 id="orderProduct"></h3>

        <p>Price: ₹<span id="orderPrice">0</span></p>
        <p>Size: <span id="orderML"></span></p>
        <p>Available Stock: <span id="orderStock"></span></p>

        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" min="1" value="1">

        <label for="address">Address</label>
        <textarea id="address" placeholder="Enter your address"></textarea>

        <label for="payment">Payment Option</label>
        <select id="payment">
            <option value="">Select Payment</option>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="UPI Demo">UPI (Demo)</option>
            <option value="Card Demo">Card (Demo)</option>
        </select>

        <h3>Total: ₹<span id="total">0</span></h3>

        <button class="confirm-button" onclick="confirmOrder()">CONFIRM DEMO ORDER</button>
        <p class="note">Demo only. No real payment is processed.</p>
    </div>
</div>

<!-- ORDER SUCCESS -->
<div id="successBox" class="popup">
    <div class="popup-content success-content">
        <h1>✓</h1>
        <h2>ORDER SUCCESSFUL</h2>
        <p>Your demo order token is:</p>
        <div id="token" class="token"></div>
        <p id="successDetails"></p>
        <button class="confirm-button" onclick="closeSuccess()">CLOSE</button>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <h2>Sandesh & Harvir WINE SHOP</h2>
    <p>Address: Lodha Mala, Ward No. 1, Shrirampur, Ahilyanagar, Maharashtra</p>
    <p>Phone: XXXXXXXX803</p>
    <p>Email: sandeshwakade123@gmail.com</p>
    <p>Email: harvirvarma123@gmail.com</p>
    <p>Created By: Sandesh wakade</p>
    <p>Created By: harvir varma </p>

   
</footer>

<script src="script.js"></script>
</body>
</html>
