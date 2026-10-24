
// MENU
function openMenu() {
    const menu = document.getElementById("menu");
    menu.style.display =
        menu.style.display === "block" ? "none" : "block";
}

// PHOTO SLIDER
const photos = [
    "photos/group.png",
    "photos/group1.png"
];

let currentSlide = 0;

function displaySlide() {
    document.getElementById("slidePhoto").src = photos[currentSlide];
    document.getElementById("slideNumber").textContent =
        (currentSlide + 1) + " / " + photos.length;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % photos.length;
    displaySlide();
}

function previousSlide() {
    currentSlide = (currentSlide - 1 + photos.length) % photos.length;
    displaySlide();
}

// CATEGORY FILTER
function filterProducts(category) {
    document.querySelectorAll(".product-card").forEach(function(card) {
        card.style.display =
            category === "all" || card.dataset.category === category
                ? ""
                : "none";
    });
}

// ORDER
let selectedCard = null;


function openOrder(button) {
    const card = button.closest(".product-card");
    const productName = card.dataset.name;

    window.location.href =
        "order.php?name=" + encodeURIComponent(productName);
}


function closeOrder() {
    document.getElementById("orderBox").style.display = "none";
}

function calculateTotal() {
    if (!selectedCard) return;

    const price = Number(selectedCard.dataset.price);
    const stock = Number(selectedCard.dataset.stock);
    const input = document.getElementById("quantity");

    let quantity = Number(input.value);

    if (quantity < 1 || !Number.isInteger(quantity)) quantity = 1;
    if (quantity > stock) quantity = stock;

    input.value = quantity;
    document.getElementById("total").textContent = price * quantity;
}

document.getElementById("quantity").addEventListener("input", calculateTotal);

// CONFIRM ORDER - SAVE IN DATABASE
async function confirmOrder() {
    if (!selectedCard) return;

    const quantity = Number(
        document.getElementById("quantity").value
    );

    const address = document.getElementById("address").value.trim();
    const payment = document.getElementById("payment").value;
    const stock = Number(selectedCard.dataset.stock);

    if (!Number.isInteger(quantity) || quantity < 1 || quantity > stock) {
        alert("योग्य Quantity टाका.");
        return;
    }

    if (!address) {
        alert("कृपया तुमचा Address टाका.");
        return;
    }

    if (!payment) {
        alert("कृपया Payment Option निवडा.");
        return;
    }

    const button = document.querySelector(
        '#orderBox button[onclick="confirmOrder()"]'
    );

    if (button) button.disabled = true;

    try {
        const response = await fetch("add_order.php", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                product_name: selectedCard.dataset.name,
                quantity: quantity,
                address: address,
                payment: payment
            })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            if (response.status === 401) {
                alert("Order करण्यासाठी आधी Login करा.");
                window.location.href = "login.php";
                return;
            }

            alert(result.message || "Order सेव्ह झाला नाही.");
            return;
        }

        document.getElementById("token").textContent =
            result.message.split("Token: ")[1] || "Success";

        document.getElementById("successDetails").textContent =
            selectedCard.dataset.name +
            " × " + quantity +
            " | Total: ₹" +
            (Number(selectedCard.dataset.price) * quantity);

        // Update stock on the page
        const newStock = stock - quantity;
        selectedCard.dataset.stock = newStock;

        const stockElement = selectedCard.querySelector(".stock");
        if (stockElement) {
            stockElement.textContent = newStock;
        }

        document.getElementById("orderStock").textContent = newStock;

        closeOrder();
        document.getElementById("successBox").style.display = "block";

    } catch (error) {
        alert("Server shi connection zala nahi. XAMPP ani PHP files tapasaa.");
        console.error(error);
    } finally {
        if (button) button.disabled = false;
    }
}

// MY ORDERS
function showOrders() {
    const orders = JSON.parse(localStorage.getItem("sujayDemoOrders") || "[]");

    if (orders.length === 0) {
        alert("No demo orders found.");
        return;
    }

    let message = "MY ORDERS\n\n";

    orders.forEach(function(order) {
        message += "Token: " + order.token +
            "\nProduct: " + order.product +
            "\nQuantity: " + order.quantity +
            "\nTotal: ₹" + order.total + "\n\n";
    });

    alert(message);
}

// LOGOUT
function logout() {
    document.getElementById("welcomeUser").textContent = "Guest User";
    document.getElementById("menu").style.display = "none";
    alert("Demo logout completed.");
}
