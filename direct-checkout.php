<?php
// Start session to store user data
session_start();

// Include database connection and Stripe
require_once 'api/c.php';
require_once 'vendor/autoload.php';

// Set Stripe API key from config
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Store user data in session if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['email']) || empty($_POST['first_name']) || empty($_POST['last_name']) || 
        empty($_POST['password']) || empty($_POST['price_id'])) {
        $error = "All fields are required";
    } else {
        // Store user registration data in session
        $_SESSION['pending_signup'] = [
            'email' => $_POST['email'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'password' => $_POST['password'],
            'price_id' => $_POST['price_id'],
            'timestamp' => time()
        ];
        
        // Set domain for redirect URLs - change this to your actual domain
        $domain = 'https://app.getgradegenie.com/new';
        
        // Get price ID from form
        $price_id = $_POST['price_id'];
        
        // Create checkout session
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $_POST['email'],
                'client_reference_id' => session_id(), // To identify the session when the user returns
                'mode' => 'subscription',
                'subscription_data' => [
                    'trial_period_days' => 3, // 3-day free trial
                ],
                'line_items' => [[
                    'price' => $price_id,
                    'quantity' => 1,
                ]],
                'success_url' => $domain . '/checkout-success.php?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $domain . '/checkout-cancel.php',
            ]);
            
            // Redirect to Stripe Checkout
            header("Location: " . $session->url);
            exit();
        } catch (\Exception $e) {
            // Log the error
            error_log('Stripe error: ' . $e->getMessage());
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Checkout - GradeGenie</title>
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
            margin: 20px auto;
            padding: 30px;
            border-radius: 12px;
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #16a085;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
        }
        button {
            background-color: #16a085;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #138a72;
        }
        .plan-options {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .plan-option {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .plan-option:hover {
            border-color: #16a085;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .plan-option.selected {
            border-color: #16a085;
            background-color: #e8f8f5;
        }
        .plan-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            color: #16a085;
        }
        .plan-price {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .plan-billing {
            font-size: 14px;
            color: #7f8c8d;
        }
        .trial-badge {
            background-color: #16a085;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fadbd8;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Join GradeGenie</h1>
        
        <?php if (isset($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Select Your Plan</label>
                <div class="plan-options">
                    <div class="plan-option" onclick="selectPlan('monthly', 'price_1RSfDZPNghHxhsC6AXTelaP3')">
                        <div class="trial-badge">3-Day Free Trial</div>
                        <div class="plan-name">Monthly</div>
                        <div class="plan-price">$18.99</div>
                        <div class="plan-billing">per month</div>
                    </div>
                    <div class="plan-option" onclick="selectPlan('yearly', 'price_1RSfDZPNghHxhsC6WBDCIvQ8')">
                        <div class="trial-badge">3-Day Free Trial</div>
                        <div class="plan-name">Yearly</div>
                        <div class="plan-price">$16.99</div>
                        <div class="plan-billing">per month, billed yearly</div>
                    </div>
                </div>
                <input type="hidden" id="price_id" name="price_id" value="price_1RSfDZPNghHxhsC6AXTelaP3">
            </div>
            
            <button type="submit">Start Your Free Trial</button>
            
            <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                <i class="fas fa-shield-alt"></i> Secure payment processing with Stripe<br>
                <i class="fas fa-check-circle" style="color: #16a085;"></i> Cancel anytime during your free trial
            </p>
        </form>
    </div>
    
    <script>
        function selectPlan(planType, priceId) {
            // Update hidden input with selected price ID
            document.getElementById('price_id').value = priceId;
            
            // Update UI to show selected plan
            const planOptions = document.querySelectorAll('.plan-option');
            planOptions.forEach(option => {
                option.classList.remove('selected');
            });
            
            // Find the clicked plan option and add selected class
            event.currentTarget.classList.add('selected');
        }
        
        // Select monthly plan by default
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyPlan = document.querySelector('.plan-option');
            if (monthlyPlan) {
                monthlyPlan.classList.add('selected');
            }
        });
    </script>
</body>
</html>
