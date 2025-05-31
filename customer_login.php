<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate against customer credentials
    $stmt = $conn->prepare("SELECT * FROM customers WHERE username = :username AND password = :password");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password); // You can hash the password for security in production
    $stmt->execute();

    // Fetch the customer details
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        // Successful login
        $_SESSION['customer_logged_in'] = true;
        $_SESSION['customer_id'] = $customer['id']; // Save customer ID in the session
        header("Location: customer_dashboard.php"); // Redirect to customer dashboard
        exit();
    } else {
        // Invalid credentials
        $error_message = "Invalid username or password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Login</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden; /* Prevent scrolling */
        }

        /* Background video styling */
        .background-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the video covers the background */
            z-index: -1; /* Places the video behind content */
        }

        /* Main content styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            color: black; /* Darkened heading */
            font-weight: bold; /* Makes heading bold */
            margin-top: 50px;
        }

        .form-container {
            display: inline-block;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.8); /* Lightened login box */
        }

        input {
            margin: 10px 0;
            padding: 8px;
            width: 200px;
        }

        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .register-button {
            padding: 10px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
        }

        .register-button:hover {
            background-color: #d32f2f;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            font-size: 24px;
            color: #4CAF50;
            display: flex;
            align-items: center;
        }

        .back-button:hover {
            color: #45a049;
        }
    </style>
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop class="background-video">
        <source src="customerbackground-video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <!-- Back Button -->
    <a href="index.php" class="back-button">&#8592; Back</a>

    <h1>Customer Login</h1>
    <div class="form-container">
        <form method="POST" action="customer_login.php">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <button class="register-button" onclick="window.location.href='customer_register.php';">Register Now</button>
    </div>
    <?php
    // Display error message if login fails
    if (isset($error_message)) {
        echo "<p class='error-message'>$error_message</p>";
    }
    ?>
</body>
</html>