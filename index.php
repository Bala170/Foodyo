<!DOCTYPE html>
<html>
<head>
    <title>FOODYO</title>
    <style>
        /* Reset styles */
        body, html {
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevent scrollbar */
            height: 100%;
        }

        /* Background video */
        .background-video {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the video covers the entire background */
            z-index: -1;
        }

        /* Main content styling */
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: #000; /* Fallback background */
        }

        /* Login buttons styling */
        .login-box {
            display: inline-block;
            padding: 15px 30px;
            margin: 15px;
            border: 2px solid white;
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0.67);
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .login-box:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        h1 {
            font-size: 3rem;
            font-weight: bold;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
        }
    </style>
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop class="background-video">
        <source src="background-video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <h1>FOODYO</h1>
    <a href="admin_login.php" class="login-box">Admin</a>
    <a href="customer_login.php" class="login-box">Customer</a>
    <a href="delivery_person_login.php" class="login-box">Delivery Person</a>
</body>
</html>