<?php
/**
 * Create Stripe Checkout Session
 * 
 * This script creates a Stripe Checkout Session for subscription plans with a 3-day trial.
 * 
 * Requirements:
 * - Run `composer require stripe/stripe-php:^12` in the project directory
 * - Stripe API keys are defined in c.php
 * 
 * Usage:
 * POST JSON { user_id, plan_key, billing_cycle, seats }
 */

// Include dependencies
require_once '../vendor/autoload.php';
require_once 'c.php';
require_once 'error-log.php';

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data from request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate required fields
if (!$data || !isset($data['user_id']) || !isset($data['plan_key']) || !isset($data['billing_cycle'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Initialize Stripe with the secret key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    // Extract data
    $userId = intval($data['user_id']);
    $planKey = strtolower($data['plan_key']);
    $billingCycle = strtolower($data['billing_cycle']);
    $seats = isset($data['seats']) ? intval($data['seats']) : 1;
    
    // Log checkout session attempt
    log_debug('Checkout session attempt', [
        'user_id' => $userId,
        'plan_key' => $planKey,
        'billing_cycle' => $billingCycle,
        'seats' => $seats
    ]);
    
    // Validate plan key
    if (!in_array($planKey, ['educator', 'institution', 'enterprise'])) {
        throw new Exception("Invalid plan key: $planKey");
    }
    
    // Validate billing cycle
    if (!in_array($billingCycle, ['month', 'year'])) {
        throw new Exception("Invalid billing cycle: $billingCycle");
    }
    
    // Validate seats based on plan
    if ($planKey === 'educator' && $seats < 1) {
        throw new Exception("Educator plan requires at least 1 seat");
    } elseif (($planKey === 'institution' || $planKey === 'enterprise') && $seats < 3) {
        throw new Exception("$planKey plan requires at least 3 seats");
    }
    
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User not found: $userId");
    }
    
    $user = $result->fetch_assoc();
    
    // Get price ID from plans table
    $priceIdField = $billingCycle === 'month' ? 'price_id_month' : 'price_id_year';
    $stmt = $conn->prepare("SELECT * FROM plans WHERE plan_key = ?");
    $stmt->bind_param("s", $planKey);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Plan not found: $planKey");
    }
    
    $plan = $result->fetch_assoc();
    $priceId = $plan[$priceIdField];
    
    if (empty($priceId)) {
        throw new Exception("Price ID not found for $planKey ($billingCycle)");
    }
    
    // Create checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $priceId,
            'quantity' => $seats,
        ]],
        'mode' => 'subscription',
        'subscription_data' => [
            'trial_period_days' => 3,
        ],
        'payment_method_collection' => 'always',
        'customer_email' => $user['email'],
        'client_reference_id' => $userId,
        'success_url' => SITE_URL . '/success.php?sid={CHECKOUT_SESSION_ID}',
        'cancel_url' => SITE_URL . '/cancel.php',
        'metadata' => [
            'user_id' => $userId,
            'plan_key' => $planKey,
            'billing_cycle' => $billingCycle,
            'seats' => $seats
        ]
    ]);
    
    // Log successful checkout session creation
    log_debug('Checkout session created', [
        'user_id' => $userId,
        'session_id' => $session->id,
        'plan_key' => $planKey,
        'billing_cycle' => $billingCycle,
        'seats' => $seats
    ]);
    
    // Check if subscriptions table exists, create if not
    $checkTableQuery = "SHOW TABLES LIKE 'subscriptions'";
    $result = $conn->query($checkTableQuery);
    
    if ($result->num_rows == 0) {
        // Create subscriptions table
        $createTableQuery = "CREATE TABLE subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_key VARCHAR(50) NOT NULL,
            seats INT NOT NULL DEFAULT 1,
            stripe_customer_id VARCHAR(100),
            stripe_subscription_id VARCHAR(100),
            stripe_checkout_session_id VARCHAR(100),
            status VARCHAR(50) DEFAULT 'pending',
            trial_ends_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )";
        
        if (!$conn->query($createTableQuery)) {
            throw new Exception("Error creating subscriptions table: " . $conn->error);
        }
        
        log_debug('Created subscriptions table');
    }
    
    // Insert into subscriptions table
    $stmt = $conn->prepare("INSERT INTO subscriptions 
        (user_id, plan_key, seats, stripe_checkout_session_id, status) 
        VALUES (?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param("isis", $userId, $planKey, $seats, $session->id);
    
    if (!$stmt->execute()) {
        throw new Exception("Error saving subscription: " . $stmt->error);
    }
    
    // Return success response with checkout session URL
    echo json_encode([
        'success' => true,
        'url' => $session->url
    ]);
    
} catch (\Stripe\Exception\CardException $e) {
    // Log card error
    log_stripe_error('Card error', [
        'error' => $e->getMessage(),
        'code' => $e->getStripeCode(),
        'decline_code' => $e->getDeclineCode(),
        'user_id' => $userId ?? null
    ]);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Log invalid request error
    log_stripe_error('Invalid request', [
        'error' => $e->getMessage(),
        'param' => $e->getStripeParam(),
        'user_id' => $userId ?? null
    ]);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Log general error
    log_error('checkout_session_error', $e->getMessage(), [
        'trace' => $e->getTraceAsString(),
        'user_id' => $userId ?? null,
        'plan_key' => $planKey ?? null,
        'billing_cycle' => $billingCycle ?? null
    ]);
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
