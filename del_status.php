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

// Get the customer ID from the session
$customer_id = $_SESSION['customer_id'];

// Fetch orders for the logged-in customer
$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id, 
        f.name AS food_name, 
        o.quantity, 
        o.status, 
        o.delivery_person 
    FROM orders o
    JOIN food_items f ON o.food_id = f.id
    WHERE o.customer_id = :customer_id
    ORDER BY o.id DESC
");
$stmt->bindParam(':customer_id', $customer_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Status</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            position: relative; /* For positioning the back button */
            min-height: 100vh; /* Ensures full height for proper positioning */
        }
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 60px; /* Keeps space for the back button */
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        .status-admin {
            color: blue;
        }
        .status-delivery {
            color: orange;
        }
        .status-delivered {
            color: green;
        }
        /* Back button styling */
        .back-button {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            text-decoration: none;
            font-size: 18px;
            color: white;
            background-color: #f44336;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
        }
        .back-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delivery Status</h1>
        <?php if (count($orders) > 0): ?>
            <table>
                <tr>
                    <th>Food Name</th>
                    <th>Quantity</th>
                    <th>Delivery Person</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td>
                            <?php 
                            echo $order['delivery_person'] ? 
                                htmlspecialchars($order['delivery_person']) : 
                                'Not Assigned'; 
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($order['status'] === 'Confirmed') {
                                echo '<span class="status-admin">With Admin</span>';
                            } elseif ($order['status'] === 'Out for Delivery') {
                                echo '<span class="status-delivery">With Delivery Person</span>';
                            } elseif ($order['status'] === 'Delivered') {
                                echo '<span class="status-delivered">Delivered</span>';
                            } else {
                                echo htmlspecialchars($order['status']);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No orders found!</p>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <a href="customer_dashboard.php" class="back-button">Back</a>
</body>
</html>