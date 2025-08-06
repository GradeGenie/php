<?php

// Start session to retrieve user data
session_start();

// Log the cancellation
error_log("Checkout cancelled. Session ID: " . session_id());

// Redirect back to signup with error parameter
header('Location: signup.php?error=1');
exit();


// // Start session to retrieve user data
// session_start();

// // Log the cancellation
// error_log("Checkout cancelled. Session ID: " . session_id());

// // Clean up any customer that might have been created
// if (isset($_SESSION['pending_signup']) && isset($_SESSION['pending_signup']['customer_id'])) {
//     try {
//         require_once 'api/c.php';
//         require_once 'vendor/autoload.php';
        
//         // Set Stripe API key
//         \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
//         // Delete the customer to avoid orphaned records
//         $customer_id = $_SESSION['pending_signup']['customer_id'];
//         \Stripe\Customer::delete($customer_id);
//         error_log("Deleted customer after checkout cancellation: $customer_id");
//     } catch (Exception $e) {
//         error_log("Error deleting customer after cancellation: " . $e->getMessage());
//     }
// }
?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Cancelled - GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            font-family: 'Albert Sans', sans-serif;
            color: #333;
        }
        .container {
            max-width: 800px;
            width: 100%;
            margin: 50px auto;
            padding: 40px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #34495e;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 72px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .button {
            background-color: #16a085;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #138a72;
        }
        .button.secondary {
            background-color: #95a5a6;
            margin-left: 15px;
        }
        .button.secondary:hover {
            background-color: #7f8c8d;
        }
        .options {
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <i class="fas fa-times-circle icon"></i>
        <h1>Checkout Cancelled</h1>
        <p>Your subscription process was cancelled before completion.</p>
        <p>If you experienced any issues during the checkout process or have questions about our subscription plans, please don't hesitate to contact our support team.</p>
        
        <div class="options">
            <a href="signup.php" class="button">Try Again</a>
            <a href="index.php" class="button secondary">Return to Home</a>
        </div>
    </div>
</body>
</html> -->
