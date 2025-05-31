<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();

// Check if the session variables are properly set
if (!isset($_SESSION['customer_logged_in']) || !isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Handle Logout functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    // Destroy session and redirect to login page
    session_destroy();
    header("Location: customer_login.php");
    exit();
}

// Get the customer ID from the session
$customer_id = $_SESSION['customer_id'];

// Handle Add to Cart functionality
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    $food_id = $_POST['food_id'];
    $stmt = $conn->prepare("INSERT INTO cart (food_id, customer_id, quantity) VALUES (:food_id, :customer_id, 1)");
    $stmt->bindParam(':food_id', $food_id);
    $stmt->bindParam(':customer_id', $customer_id);

    try {
        $stmt->execute();
        $success_message = "Item added to cart successfully!";
    } catch (PDOException $e) {
        $error_message = "An error occurred while adding the item to the cart.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .navbar {
            background-color: #4CAF50;
            padding: 10px;
            text-align: center;
        }
        .navbar a, .navbar button {
            color: white;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 18px;
            border: none;
            background: none;
            cursor: pointer;
            display: inline-block;
        }
        .navbar a:hover, .navbar button:hover {
            background-color: #45a049;
        }
        .navbar button {
            background-color: #f44336;
            border-radius: 5px;
            margin-left: 10px;
        }
        .container {
            padding: 20px;
        }
        .food-item {
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #ffffff;
            margin: 10px;
            padding: 10px;
            text-align: center;
            display: inline-block;
            width: 200px;
        }
        .food-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        .food-item h3 {
            margin: 10px 0;
            font-size: 18px;
        }
        .food-item p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background-color: #45a049;
        }
        .filter {
            margin-bottom: 20px;
        }
        .success-message {
            color: green;
            margin-top: 20px;
        }
        .error-message {
            color: red;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="customer_dashboard.php">Dashboard</a>
        <a href="cart.php">Cart</a>
        <a href="customer_profile.php">Profile</a>
        <a href="feedback.php">Feedback</a>
        <a href="del_status.php">Delivery Status</a>
        <form method="POST" action="customer_dashboard.php" style="display: inline;">
            <button type="submit" name="logout">Logout</button>
        </form>
    </div>

    <div class="container">
        <h1>Food Items</h1>

        <div class="filter">
            <form method="GET" action="customer_dashboard.php">
                <label for="category">Filter by Category:</label>
                <select id="category" name="category">
                    <option value="All">All</option>
                    <option value="Breakfast">Breakfast</option>
                    <option value="Lunch">Lunch</option>
                    <option value="Dinner">Dinner</option>
                    <option value="Desserts">Desserts</option>
                    <option value="Soft Drinks">Soft Drinks</option>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>

        <div class="food-list">
            <?php
            $category_filter = isset($_GET['category']) ? $_GET['category'] : 'All';

            if ($category_filter == 'All') {
                $stmt = $conn->prepare("SELECT * FROM food_items");
            } else {
                $stmt = $conn->prepare("SELECT * FROM food_items WHERE category = :category");
                $stmt->bindParam(':category', $category_filter);
            }
            $stmt->execute();

            while ($food_item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="food-item">';
                echo '<img src="uploads/' . htmlspecialchars($food_item['image']) . '" alt="Food Image">';
                echo '<h3>' . htmlspecialchars($food_item['name']) . '</h3>';
                echo '<p>Category: ' . htmlspecialchars($food_item['category']) . '</p>';
                echo '<p>Price: ₹' . htmlspecialchars($food_item['price']) . '</p>';
                echo '<form method="POST" action="customer_dashboard.php">';
                echo '<input type="hidden" name="food_id" value="' . htmlspecialchars($food_item['id']) . '">';
                echo '<button type="submit" name="add_to_cart">Add to Cart</button>';
                echo '</form>';
                echo '</div>';
            }
            ?>
        </div>

        <?php
        if (isset($success_message)) {
            echo '<p class="success-message">' . htmlspecialchars($success_message) . '</p>';
        }
        if (isset($error_message)) {
            echo '<p class="error-message">' . htmlspecialchars($error_message) . '</p>';
        }
        ?>
    </div>
</body>
</html>