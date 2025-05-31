<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();

// Check if the admin is logged in (basic session check)
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
            background-color: rgba(255, 255, 255, 0.9); /* Fallback color */
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #000; /* Darkened heading */
            font-weight: bold; /* Makes the heading bold */
            font-size: 2.5em; /* Enlarges the font size */
        }

        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px; /* Adds spacing between boxes */
            margin: 30px auto;
            max-width: 1200px;
        }

        .dashboard-box {
            width: 250px;
            background-color: rgba(255, 255, 255, 0.6); /* Light-white color */
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dashboard-box:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .dashboard-box a {
            text-decoration: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 15px;
            background-color: #4CAF50;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }

        .dashboard-box a:hover {
            background-color: #45a049;
        }

        .logout-button {
            background-color: #f44336;
        }

        .logout-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <!-- Background Video -->
    <video autoplay muted loop class="background-video">
        <source src="admindashboard-video.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <h1>Admin Dashboard</h1>
    <div class="dashboard-container">
        <div class="dashboard-box">
            <h2>Add Food</h2>
            <a href="add_food.php">Go</a>
        </div>
        <div class="dashboard-box">
            <h2>Customer Details</h2>
            <a href="cus_details.php">View</a>
        </div>
        <div class="dashboard-box">
            <h2>Add Delivery Person</h2>
            <a href="add_del.php">Add</a>
        </div>
        <div class="dashboard-box">
            <h2>Delivery Status</h2>
            <a href="del_admin.php">View</a>
        </div>
        <div class="dashboard-box">
            <h2>Customer Feedback</h2>
            <a href="cus_feed.php">View</a>
        </div>
        <div class="dashboard-box">
            <h2>Food Items</h2>
            <a href="food_items.php">View</a>
        </div>
        <div class="dashboard-box">
            <h2>Logout</h2>
            <a href="index.php" class="logout-button">Logout</a>
        </div>
    </div>
</body>
</html>