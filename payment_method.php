<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();
if (!isset($_SESSION['customer_logged_in'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$delivery_person = $_SESSION['delivery_person'];

// Handle payment confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_payment'])) {
    $payment_method = $_POST['payment_method'];
    $payment_details = isset($_POST['payment_details']) ? $_POST['payment_details'] : null;

    // Move items from cart to orders
    $cart_items = $conn->prepare("SELECT * FROM cart WHERE customer_id = :customer_id");
    $cart_items->bindParam(':customer_id', $customer_id);
    $cart_items->execute();

    while ($item = $cart_items->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, food_id, quantity, status, delivery_person, payment_method, payment_details) 
                                VALUES (:customer_id, :food_id, :quantity, 'Confirmed', :delivery_person, :payment_method, :payment_details)");
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':food_id', $item['food_id']);
        $stmt->bindParam(':quantity', $item['quantity']);
        $stmt->bindParam(':delivery_person', $delivery_person);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':payment_details', $payment_details);
        $stmt->execute();
    }

    // Clear the cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();

    $payment_success_message = "Payment confirmed! Your order has been placed.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Method</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9; /* Light green background */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff; /* White background for the form */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #388e3c; /* Dark green */
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2e7d32; /* Medium green */
        }

        input[type="radio"], input[type="text"] {
            margin-bottom: 15px;
            display: block;
            width: 100%;
            padding: 10px;
            border: 1px solid #c8e6c9; /* Light green border */
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4caf50; /* Green button */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 10px;
        }

        button:hover {
            background-color: #45a049; /* Slightly darker green */
        }

        .success-message {
            text-align: center;
            color: #388e3c; /* Dark green */
            font-weight: bold;
        }
    </style>
    <script>
        function togglePaymentDetails() {
            const onlinePaymentOption = document.getElementById('online_payment');
            const paymentDetailsField = document.getElementById('payment_details_field');

            if (onlinePaymentOption.checked) {
                paymentDetailsField.style.display = 'block';
            } else {
                paymentDetailsField.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Select Payment Method</h1>
        <?php
        if (isset($payment_success_message)) {
            echo '<p class="success-message">' . htmlspecialchars($payment_success_message) . '</p>';
            // Add back button after order confirmation
            echo '<button onclick="window.location.href=\'cart.php\'">Back to Cart</button>';
        } else {
            echo '<form method="POST" action="payment_method.php">';
            echo '<label for="payment_method">Payment Method:</label><br>';
            echo '<input type="radio" name="payment_method" value="Cash on Delivery" id="cash_on_delivery" onclick="togglePaymentDetails()" required> Cash on Delivery<br>';
            echo '<input type="radio" name="payment_method" value="Online Payment" id="online_payment" onclick="togglePaymentDetails()" required> Online Payment<br><br>';
            echo '<div id="payment_details_field" style="display: none;">';
            echo '<label for="payment_details">Payment Details (UPI ID / Card Number):</label><br>';
            echo '<input type="text" name="payment_details" placeholder="Enter UPI ID or Card Number"><br><br>';
            echo '</div>';
            echo '<button type="submit" name="confirm_payment">Confirm Payment</button>';
            echo '</form>';
        }
        ?>
    </div>
</body>
</html>