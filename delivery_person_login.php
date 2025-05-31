<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();

// Handle Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate credentials
    $stmt = $conn->prepare("SELECT * FROM delivery_persons WHERE username = :username AND password = :password");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();

    $delivery_person = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($delivery_person) {
        $_SESSION['delivery_person_logged_in'] = true;
        $_SESSION['delivery_person_id'] = $delivery_person['id'];
        header("Location: delivery_person_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password.";
    }
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Person Login</title>
    <style>
        body, html {
            margin: 0;
            font-family: Arial, sans-serif;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 100%;
        }

        /* Background Video */
        .video-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            z-index: -1;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Centered Login Form */
        .form-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            color: black;
        }

        /* Black Login Title */
        .form-container h1 {
            color: black;
            margin-bottom: 15px;
            font-size: 22px;
        }

        input {
            margin: 10px 0;
            padding: 8px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: rgba(255, 255, 255, 0.8);
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .logout-button {
            padding: 10px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .logout-button:hover {
            background-color: #d32f2f;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Background Video -->
    <div class="video-container">
        <video autoplay muted loop>
            <source src="Deliveryman.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <div class="form-container">
        <h1>Delivery Person Login</h1>
        <form method="POST" action="delivery_person_login.php">
            <label for="username">Username:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <button type="submit" name="login">Login</button>
        </form>

        <?php
        if (isset($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>

        <form method="POST" action="delivery_person_login.php">
            <button type="submit" name="logout" class="logout-button">Logout</button>
        </form>
    </div>

</body>
</html>