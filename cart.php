<?php
// Include the database connection file
include 'db_connect.php';

// Start the session
session_start();
if (!isset($_SESSION['customer_logged_in'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id']; // Assuming customer_id is stored in session

// Handle quantity update in cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    $stmt = $conn->prepare("UPDATE cart SET quantity = :quantity WHERE id = :cart_id");
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':cart_id', $cart_id);
    $stmt->execute();
}

// Handle item removal from cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_item'])) {
    $cart_id = $_POST['remove_item'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = :cart_id AND customer_id = :customer_id");
    $stmt->bindParam(':cart_id', $cart_id);
    $stmt->bindParam(':customer_id', $customer_id);
    $stmt->execute();
}

// Handle saving the location image and redirect to payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_order'])) {
    $delivery_person = $_POST['delivery_person'];
    
    // Check if location image is uploaded
    if (isset($_POST['location_image']) && !empty($_POST['location_image'])) {
        // Store the base64 image data
        $location_image = $_POST['location_image'];
        
        // Remove the data URL prefix to get only the base64 data
        $image_parts = explode(";base64,", $location_image);
        $image_base64 = isset($image_parts[1]) ? $image_parts[1] : "";
        
        // Generate a unique filename
        $filename = 'location_' . $customer_id . '_' . time() . '.png';
        $filepath = 'location_images/' . $filename;
        
        // Create directory if it doesn't exist
        if (!file_exists('location_images')) {
            mkdir('location_images', 0777, true);
        }
        
        // Save the image to server
        file_put_contents($filepath, base64_decode($image_base64));
        
        // Store the filepath in session
        $_SESSION['location_image'] = $filepath;
    } else {
        // Set error message if no image is captured
        $_SESSION['error_message'] = "Please capture and upload your location image before confirming the order.";
        header("Location: cart.php");
        exit();
    }

    // Store delivery person in session for later use
    $_SESSION['delivery_person'] = $delivery_person;

    // Redirect to payment method selection
    header("Location: payment_method.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart</title>
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
            margin-bottom: 70px; /* Space for back button */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        input[type="number"] {
            width: 50px;
            padding: 5px;
        }

        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px 0;
        }

        button:hover {
            background-color: #45a049;
        }

        .success-message {
            color: green;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
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

        /* Camera section styling */
        #camera-section {
            margin: 20px 0;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f5f5f5;
        }

        #camera-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        #camera-view {
            width: 100%;
            height: 375px;
            background-color: #000;
            border-radius: 5px;
            overflow: hidden;
        }

        #camera-canvas {
            display: none;
        }

        #photo-preview {
            width: 100%;
            max-width: 500px;
            height: auto;
            margin: 10px 0;
            border-radius: 5px;
            display: none;
        }

        .camera-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        .camera-button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #retake-button {
            background-color: #f44336;
        }

        #camera-status {
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
        }

        select {
            padding: 8px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error-message"><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        
        <?php
        // Fetch cart items
        $stmt = $conn->prepare("SELECT c.*, f.name AS food_name, f.price AS food_price FROM cart c 
                                JOIN food_items f ON c.food_id = f.id 
                                WHERE c.customer_id = :customer_id");
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($cart_items) > 0) {
            echo '<table>';
            echo '<tr><th>Food Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>';
            $grand_total = 0;
            foreach ($cart_items as $item) {
                $total = $item['food_price'] * $item['quantity'];
                $grand_total += $total;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($item['food_name']) . '</td>';
                echo '<td>₹' . htmlspecialchars($item['food_price']) . '</td>';
                echo '<td>';
                echo '<form method="POST" action="cart.php">';
                echo '<input type="number" name="quantity" value="' . htmlspecialchars($item['quantity']) . '" min="1">';
                echo '<input type="hidden" name="cart_id" value="' . htmlspecialchars($item['id']) . '">';
                echo '<button type="submit" name="update_quantity">Update</button>';
                echo '</form>';
                echo '</td>';
                echo '<td>₹' . htmlspecialchars($total) . '</td>';
                echo '<td>';
                echo '<form method="POST" action="cart.php">';
                echo '<button type="submit" name="remove_item" value="' . htmlspecialchars($item['id']) . '">Remove</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="3" style="text-align: right;"><strong>Grand Total:</strong></td>';
            echo '<td colspan="2"><strong>₹' . htmlspecialchars($grand_total) . '</strong></td></tr>';
            echo '</table>';

            // Fetch delivery persons
            $stmt = $conn->prepare("SELECT id, name FROM delivery_persons");
            $stmt->execute();
            $delivery_persons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <form method="POST" action="cart.php" id="order-form">
                <div class="form-group">
                    <label for="delivery_person"><strong>Select Delivery Person:</strong></label>
                    <select name="delivery_person" required>
                        <?php foreach ($delivery_persons as $person): ?>
                            <option value="<?php echo htmlspecialchars($person['id']); ?>">
                                <?php echo htmlspecialchars($person['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Camera Section -->
                <div id="camera-section">
                    <h3>Capture Your Location</h3>
                    <p>Please capture a photo of your location to help the delivery person find you easily.</p>
                    
                    <div id="camera-container">
                        <video id="camera-view" autoplay playsinline></video>
                        <canvas id="camera-canvas"></canvas>
                        <img id="photo-preview" alt="Your location image preview">
                        
                        <div id="camera-status">Click "Start Camera" to begin</div>
                        
                        <div class="camera-buttons">
                            <button type="button" id="start-camera" class="camera-button">Start Camera</button>
                            <button type="button" id="capture-button" class="camera-button" disabled>Capture Photo</button>
                            <button type="button" id="upload-button" class="camera-button" disabled>Upload Photo</button>
                            <button type="button" id="retake-button" class="camera-button" disabled>Retake Photo</button>
                        </div>
                    </div>
                    
                    <!-- Hidden input to store the captured image data -->
                    <input type="hidden" name="location_image" id="location-image-input">
                </div>
                
                <button type="submit" name="confirm_order" id="confirm-button">Confirm Order</button>
            </form>

        <?php } else { ?>
            <p>Your cart is empty!</p>
        <?php } ?>
    </div>

    <!-- Back Button -->
    <a href="customer_dashboard.php" class="back-button">Back</a>

    <!-- Camera Script -->
    <script>
        // DOM elements
        const cameraView = document.getElementById('camera-view');
        const cameraCanvas = document.getElementById('camera-canvas');
        const photoPreview = document.getElementById('photo-preview');
        const startCameraButton = document.getElementById('start-camera');
        const captureButton = document.getElementById('capture-button');
        const retakeButton = document.getElementById('retake-button');
        const locationImageInput = document.getElementById('location-image-input');
        const orderForm = document.getElementById('order-form');
        const cameraStatus = document.getElementById('camera-status');
        
        // Global variables
        let stream = null;
        let photoTaken = false;
        let photoUploaded = false;
        
        // Start camera function
        startCameraButton.addEventListener('click', async function() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' },
                    audio: false 
                });
                
                cameraView.srcObject = stream;
                captureButton.disabled = false;
                startCameraButton.disabled = true;
                cameraStatus.textContent = 'Camera is active, click "Capture Photo" when ready';
            } catch (error) {
                console.error('Error accessing camera:', error);
                cameraStatus.textContent = 'Error: Could not access camera. Please ensure camera permissions are granted.';
            }
        });
        
        // Capture photo function
        captureButton.addEventListener('click', function() {
            if (!stream) return;
            
            // Set canvas dimensions to match video
            cameraCanvas.width = cameraView.videoWidth;
            cameraCanvas.height = cameraView.videoHeight;
            
            // Draw the video frame to the canvas
            const context = cameraCanvas.getContext('2d');
            context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
            
            // Convert canvas to data URL and set as preview
            const imageDataUrl = cameraCanvas.toDataURL('image/png');
            photoPreview.src = imageDataUrl;
            photoPreview.style.display = 'block';
            cameraView.style.display = 'none';
            
            // Update UI state
            captureButton.disabled = true;
            uploadButton.disabled = false;
            retakeButton.disabled = false;
            cameraStatus.textContent = 'Photo captured! Click "Upload Photo" to confirm or "Retake Photo" if you want to try again.';
            
            // Stop the camera stream
            stopCamera();
        });
        
        // Upload photo function
        const uploadButton = document.getElementById('upload-button');
        uploadButton.addEventListener('click', function() {
            const imageDataUrl = photoPreview.src;
            
            // Save the image data to the hidden input
            locationImageInput.value = imageDataUrl;
            
            photoTaken = true;
            uploadButton.disabled = true;
            cameraStatus.textContent = 'Photo uploaded successfully! You can now proceed with your order.';
        });
        
        // Retake photo function
        retakeButton.addEventListener('click', async function() {
            photoPreview.style.display = 'none';
            cameraView.style.display = 'block';
            photoTaken = false;
            locationImageInput.value = '';
            
            // Restart camera
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' },
                    audio: false 
                });
                
                cameraView.srcObject = stream;
                captureButton.disabled = false;
                uploadButton.disabled = true;
                retakeButton.disabled = true;
                cameraStatus.textContent = 'Camera is active, click "Capture Photo" when ready';
            } catch (error) {
                console.error('Error restarting camera:', error);
                cameraStatus.textContent = 'Error: Could not restart camera.';
            }
        });
        
        // Stop camera function
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => {
                    track.stop();
                });
                stream = null;
            }
        }
        
        // Form submission validation
        orderForm.addEventListener('submit', function(event) {
            if (!photoTaken) {
                event.preventDefault();
                alert('Please capture your location photo before confirming the order.');
                cameraStatus.textContent = 'Please take a photo of your location first!';
            }
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', stopCamera);
    </script>
</body>
</html>