<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection and error logging
require_once 'c.php';
require_once '../vendor/autoload.php'; // For Stripe and JWT
require_once 'error-log.php'; // For error logging

// Start session
session_start();

// Set your Stripe API key
\Stripe\Stripe::setApiKey('sk_test_your_stripe_secret_key');

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Log subscription attempt
log_debug('Subscription API called', [
    'has_user_data' => !empty($data->user_data),
    'has_payment_method' => !empty($data->payment_method_id),
    'plan_type' => $data->plan_type ?? 'not specified',
    'billing_period' => $data->billing_period ?? 'not specified',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);

try {
    // Create database connection
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if user is logged in via session or needs to register
    $user = null;
    $isNewUser = false;
    
    // Log authentication status
    log_debug('Authentication check', [
        'session_exists' => isset($_SESSION['user_id']),
        'has_user_data' => !empty($data->user_data)
    ]);
    
    if (isset($_SESSION['user_id'])) {
        // Get existing user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        } else {
            throw new Exception("User not found");
        }
    } else if (!empty($data->user_data)) {
        // Register new user
        $isNewUser = true;
        
        // Log new user registration attempt
        log_debug('New user registration attempt', [
            'email' => $data->user_data->email,
            'has_name' => !empty($data->user_data->name)
        ]);
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $data->user_data->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            log_signup_error('Email already exists', [
                'email' => $data->user_data->email
            ]);
            throw new Exception("Email already exists. Please log in instead.");
        }
        
        // Hash password
        $hashedPassword = password_hash($data->user_data->password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $data->user_data->name, $data->user_data->email, $hashedPassword);
        
        if (!$stmt->execute()) {
            $error = "Failed to create user: " . $stmt->error;
            log_signup_error($error, [
                'email' => $data->user_data->email,
                'db_error' => $stmt->error
            ]);
            throw new Exception($error);
        }
        
        // Log successful user creation
        log_debug('User created successfully', [
            'email' => $data->user_data->email,
            'user_id' => $stmt->insert_id
        ]);
        
        $userId = $stmt->insert_id;
        
        // Get the new user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
    } else {
        throw new Exception("Authentication required");
    }
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
    // Check if payment method ID is provided
    if (empty($data->payment_method_id)) {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "Payment method ID is required"));
        exit;
    }
    
    // Check if user already has an active subscription
    if (isset($user['subscription_status']) && $user['subscription_status'] == 'active') {
        http_response_code(400);
        echo json_encode(array("success" => false, "message" => "You already have an active subscription"));
        exit;
    }
    
    // Check if user is already on trial
    if (isset($user['trial_ends_at']) && !empty($user['trial_ends_at'])) {
        $trialEndsAt = strtotime($user['trial_ends_at']);
        $now = time();
        
        if ($trialEndsAt > $now) {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "You are already on a free trial"));
            exit;
        }
    }
        
    // If user doesn't have a Stripe customer ID, create one
    $stripeId = isset($user['stripe_id']) ? $user['stripe_id'] : null;
    if (empty($stripeId)) {
        $customer = \Stripe\Customer::create([
            'email' => $user['email'],
            'name' => $user['name'],
            'payment_method' => $data->payment_method_id,
            'invoice_settings' => [
                'default_payment_method' => $data->payment_method_id,
            ]
        ]);
        $stripeId = $customer->id;
        
        // Update user's Stripe ID
        $stmt = $conn->prepare("UPDATE users SET stripe_id = ? WHERE id = ?");
        $stmt->bind_param("si", $stripeId, $user['id']);
        $stmt->execute();
    } else {
        // Attach payment method to existing customer
        \Stripe\PaymentMethod::attach($data->payment_method_id, [
            'customer' => $stripeId
        ]);
        
        // Set as default payment method
        \Stripe\Customer::update($stripeId, [
            'invoice_settings' => [
                'default_payment_method' => $data->payment_method_id,
            ]
        ]);
    }
        
    // Set price based on plan and billing period
    $planName = 'Educator Plan';
    $unitAmount = 1899; // $18.99 in cents (default is monthly)
    $interval = 'month';
    
    // Check if yearly billing
    if (isset($data->billing_period) && $data->billing_period === 'yearly') {
        $unitAmount = 16990; // $169.90 in cents (yearly at 10% discount)
        $interval = 'year';
    }
    
    // Create subscription with 3-day trial
    $trialEnd = time() + (3 * 24 * 60 * 60); // 3 days from now
    
    // Log subscription creation attempt
    log_debug('Creating Stripe subscription', [
        'user_id' => $user['id'],
        'stripe_id' => $stripeId,
        'plan_name' => $planName,
        'billing_period' => isset($data->billing_period) ? $data->billing_period : 'monthly',
        'unit_amount' => $unitAmount,
        'trial_end' => date('Y-m-d H:i:s', $trialEnd)
    ]);
    
    try {
        $subscription = \Stripe\Subscription::create([
        'customer' => $stripeId,
        'items' => [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'GradeGenie ' . $planName,
                        'description' => 'Premium access to GradeGenie features',
                    ],
                    'unit_amount' => $unitAmount,
                    'recurring' => [
                        'interval' => $interval
                    ]
                ]
            ],
        ],
        'trial_end' => $trialEnd,
        'expand' => ['latest_invoice.payment_intent'],
    ]);
        
        // Log successful subscription creation
        log_debug('Stripe subscription created successfully', [
            'user_id' => $user['id'],
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'trial_end' => date('Y-m-d H:i:s', $subscription->trial_end)
        ]);
    } catch (\Stripe\Exception\CardException $e) {
        // Card was declined
        $error_message = $e->getMessage();
        $error_code = $e->getStripeCode();
        $decline_code = $e->getDeclineCode();
        
        log_stripe_error('Card declined', [
            'user_id' => $user['id'],
            'error' => $error_message,
            'error_code' => $error_code,
            'decline_code' => $decline_code
        ]);
        
        throw new Exception("Payment failed: " . $error_message);
    } catch (\Stripe\Exception\RateLimitException $e) {
        // Too many requests made to the API too quickly
        log_stripe_error('Rate limit exceeded', [
            'user_id' => $user['id'],
            'error' => $e->getMessage()
        ]);
        throw $e;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        // Invalid parameters were supplied to Stripe's API
        log_stripe_error('Invalid request', [
            'user_id' => $user['id'],
            'error' => $e->getMessage(),
            'param' => $e->getStripeParam()
        ]);
        throw $e;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        // Authentication with Stripe's API failed
        log_stripe_error('Authentication failed', [
            'error' => $e->getMessage()
        ]);
        throw new Exception("Payment system configuration error. Please contact support.");
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        // Network communication with Stripe failed
        log_stripe_error('API connection error', [
            'error' => $e->getMessage()
        ]);
        throw new Exception("Could not connect to payment processor. Please try again later.");
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Generic API error
        log_stripe_error('API error', [
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
        
    // Update user in database with trial information
    $trialEndsAt = date('Y-m-d H:i:s', $trialEnd);
    $stmt = $conn->prepare("UPDATE users SET 
        subscription_id = ?, 
        plan_id = ?, 
        subscription_status = ?, 
        trial_ends_at = ?,
        plan_name = ?,
        billing_period = ?
        WHERE id = ?");
    
    $plan_id = $subscription->items->data[0]->price->id;
    $status = $subscription->status;
    $planNameDb = $planName;
    $billingPeriod = isset($data->billing_period) ? $data->billing_period : 'monthly';
    
    $stmt->bind_param("ssssssi", $subscription->id, $plan_id, $status, $trialEndsAt, $planNameDb, $billingPeriod, $user['id']);
    $stmt->execute();
    
    // Get updated user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $updated_user = $result->fetch_assoc();
    
    // Remove password from response
    unset($updated_user['password']);
    
    // Set session variables
    $_SESSION['user_id'] = $updated_user['id'];
    $_SESSION['email'] = $updated_user['email'];
    $_SESSION['name'] = $updated_user['name'];
    
    // Return success response
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "user" => $updated_user,
        "subscription" => $subscription,
        "trial_ends_at" => $trialEndsAt
    ));
} catch (Exception $e) {
    // Log the exception
    log_error('subscription_error', $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'user_id' => isset($user['id']) ? $user['id'] : null,
        'email' => isset($data->user_data->email) ? $data->user_data->email : (isset($user['email']) ? $user['email'] : null),
        'plan_type' => $data->plan_type ?? 'not specified',
        'billing_period' => $data->billing_period ?? 'not specified'
    ]);
    
    http_response_code(500);
    echo json_encode(array("success" => false, "message" => "Subscription failed: " . $e->getMessage()));
}
?>
