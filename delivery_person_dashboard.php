<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();
if (!isset($_SESSION['delivery_person_logged_in']) || !isset($_SESSION['delivery_person_id'])) {
    header("Location: delivery_person_login.php");
    exit();
}

// Get the delivery person ID from the session
$delivery_person_id = $_SESSION['delivery_person_id'];

// Fetch orders assigned to the logged-in delivery person with additional details
$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id,
        c.username AS customer_name,
        c.address AS customer_address,
        c.phone AS customer_phone,
        f.name AS food_name,
        o.quantity,
        o.status,
        o.order_time,
        o.location_image
    FROM orders o
    JOIN customers c ON o.customer_id = c.id
    JOIN food_items f ON o.food_id = f.id
    WHERE o.delivery_person = :delivery_person
    ORDER BY o.order_time DESC
");
$stmt->bindParam(':delivery_person', $delivery_person_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark order as "Taken"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_taken'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Out for Delivery' WHERE id = :order_id");
    $stmt->bindParam(':order_id', $order_id);

    try {
        $stmt->execute();
        $success_message = "Order marked as Taken!";
        // Reload the page to show updated status
        header("Location: delivery_person_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "An error occurred while marking the order as Taken.";
    }
}

// Mark order as "Delivered"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_delivered'])) {
    $order_id = $_POST['order_id'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Delivered' WHERE id = :order_id");
    $stmt->bindParam(':order_id', $order_id);

    try {
        $stmt->execute();
        $success_message = "Order marked as Delivered!";
        // Reload the page to show updated status
        header("Location: delivery_person_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "An error occurred while marking the order as Delivered.";
    }
}

// View order details
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['view_details'])) {
    $order_id = $_GET['view_details'];
    // Fetch the specific order details
    $stmt = $conn->prepare("
        SELECT 
            o.id AS order_id,
            c.username AS customer_name,
            c.address AS customer_address,
            c.phone AS customer_phone,
            f.name AS food_name,
            o.quantity,
            o.status,
            o.order_time,
            o.location_image
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        JOIN food_items f ON o.food_id = f.id
        WHERE o.id = :order_id
    ");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Person Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            max-width: 1000px;
            margin: 30px auto;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 60px; /* Keeps space for the back button */
        }
        h1, h2 {
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
        button {
            padding: 5px 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
        }
        button:hover {
            background-color: #45a049;
        }
        .status-confirmed {
            color: blue;
        }
        .status-out-for-delivery {
            color: orange;
        }
        .status-delivered {
            color: green;
        }
        .success-message, .error-message {
            text-align: center;
            margin: 10px 0;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
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
        /* Order details modal styling */
        .order-details {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
        }
        .detail-value {
            flex: 1;
        }
        .location-image {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .view-details-btn {
            background-color: #2196F3;
        }
        .view-details-btn:hover {
            background-color: #0b7dda;
        }
        .close-btn {
            background-color: #607d8b;
            margin-top: 20px;
        }
        .no-image {
            color: #888;
            font-style: italic;
        }
        .order-time {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_GET['view_details']) && isset($order_details)): ?>
            <!-- Order Details View -->
            <h2>Order Details #<?php echo htmlspecialchars($order_details['order_id']); ?></h2>
            <div class="order-details">
                <div class="detail-row">
                    <div class="detail-label">Order ID:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order_details['order_id']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Order Time:</div>
                    <div class="detail-value"><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($order_details['order_time']))); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Customer:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order_details['customer_name']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value">
                        <?php 
                        // Check if phone number exists and is not empty
                        if (isset($order_details['customer_phone']) && !empty($order_details['customer_phone'])) {
                            echo htmlspecialchars($order_details['customer_phone']);
                        } else {
                            echo 'Not provided';
                        }
                        ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Address:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order_details['customer_address']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Food:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order_details['food_name']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Quantity:</div>
                    <div class="detail-value"><?php echo htmlspecialchars($order_details['quantity']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <?php
                        if ($order_details['status'] === 'Confirmed') {
                            echo '<span class="status-confirmed">With Admin</span>';
                        } elseif ($order_details['status'] === 'Out for Delivery') {
                            echo '<span class="status-out-for-delivery">Out for Delivery</span>';
                        } elseif ($order_details['status'] === 'Delivered') {
                            echo '<span class="status-delivered">Delivered</span>';
                        }
                        ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Location Image:</div>
                    <div class="detail-value">
                        <?php if (isset($order_details['location_image']) && !empty($order_details['location_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($order_details['location_image']); ?>" alt="Customer Location" class="location-image">
                        <?php else: ?>
                            <span class="no-image">No location image provided</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <?php if ($order_details['status'] === 'Confirmed'): ?>
                        <form method="POST" action="delivery_person_dashboard.php">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_details['order_id']); ?>">
                            <button type="submit" name="mark_taken">Take Order</button>
                        </form>
                    <?php elseif ($order_details['status'] === 'Out for Delivery'): ?>
                        <form method="POST" action="delivery_person_dashboard.php">
                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_details['order_id']); ?>">
                            <button type="submit" name="mark_delivered">Mark as Delivered</button>
                        </form>
                    <?php else: ?>
                        <span class="status-delivered">Order Completed</span>
                    <?php endif; ?>
                    
                    <a href="delivery_person_dashboard.php" class="close-btn" style="display: inline-block; text-decoration: none;">
                        <button type="button" class="close-btn">Back to Orders</button>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Orders List View -->
            <h1>Assigned Orders</h1>
            <?php
            if (isset($success_message)) {
                echo "<p class='success-message'>$success_message</p>";
            }
            if (isset($error_message)) {
                echo "<p class='error-message'>$error_message</p>";
            }
            ?>
            <?php if (count($orders) > 0): ?>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Food</th>
                        <th>Qty</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td class="order-time"><?php echo htmlspecialchars(date('M j, g:i a', strtotime($order['order_time']))); ?></td>
                            <td>
                                <?php
                                if ($order['status'] === 'Confirmed') {
                                    echo '<span class="status-confirmed">With Admin</span>';
                                } elseif ($order['status'] === 'Out for Delivery') {
                                    echo '<span class="status-out-for-delivery">Out for Delivery</span>';
                                } elseif ($order['status'] === 'Delivered') {
                                    echo '<span class="status-delivered">Delivered</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="delivery_person_dashboard.php?view_details=<?php echo htmlspecialchars($order['order_id']); ?>">
                                    <button type="button" class="view-details-btn">View Details</button>
                                </a>
                                
                                <?php if ($order['status'] === 'Confirmed'): ?>
                                    <form method="POST" action="delivery_person_dashboard.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <button type="submit" name="mark_taken">Take Order</button>
                                    </form>
                                <?php elseif ($order['status'] === 'Out for Delivery'): ?>
                                    <form method="POST" action="delivery_person_dashboard.php" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                        <button type="submit" name="mark_delivered">Mark Delivered</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>No orders assigned!</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Back Button -->
    <a href="index.php" class="back-button">Back</a>
</body>
</html>