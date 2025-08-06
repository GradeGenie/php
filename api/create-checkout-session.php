<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to store user data
session_start();

// Set proper content type for JSON responses
header('Content-Type: application/json');

// Define Stripe keys directly to avoid database connection issues
require_once __DIR__ . '/../load_env.php';
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
// Include Stripe library
require_once '../vendor/autoload.php';

// Set Stripe API key from config
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get the raw input
$input = file_get_contents('php://input');

// Log received data for debugging
error_log('Raw checkout request data: ' . $input);

// Parse JSON data
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit();
}

// Check for required parameters
if (!isset($data['email']) || !isset($data['priceId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters: email and priceId are required']);
    exit();
}

// Store user registration data in session if provided
if (isset($data['firstName']) && isset($data['lastName']) && isset($data['password'])) {
    $_SESSION['pending_signup'] = [
        'email' => $data['email'],
        'first_name' => $data['firstName'],
        'last_name' => $data['lastName'],
        'password' => $data['password'],
        'price_id' => $data['priceId'],
        'timestamp' => time()
    ];
    error_log('Stored registration data in session for: ' . $data['email']);
}

// Set success and cancel URLs
// Redirect to checkout-success.php after successful checkout
$success_url = 'https://app.getgradegenie.com/checkout-success.php?session_id={CHECKOUT_SESSION_ID}';
$cancel_url = 'https://app.getgradegenie.com/checkout-cancel.php';

try {
    // Create a customer in Stripe if we have full registration data
    $customer_id = null;
    if (isset($data['firstName']) && isset($data['lastName'])) {
        try {
            $customer = \Stripe\Customer::create([
                'email' => $data['email'],
                'name' => $data['firstName'] . ' ' . $data['lastName'],
                'metadata' => [
                    'session_id' => session_id(),
                    'first_name' => $data['firstName'],
                    'last_name' => $data['lastName']
                ]
            ]);
            $customer_id = $customer->id;
            if (isset($_SESSION['pending_signup'])) {
                $_SESSION['pending_signup']['customer_id'] = $customer_id;
            }
            error_log('Created Stripe customer: ' . $customer_id);
        } catch (\Exception $e) {
            error_log('Error creating customer: ' . $e->getMessage());
            // Continue with checkout even if customer creation fails
        }
    }
    
    // Create checkout session with 3-day free trial
    $checkout_params = [
        'payment_method_types' => ['card'],
        'mode' => 'subscription',
        'subscription_data' => [
            'trial_period_days' => 3, // 3-day free trial
        ],
        'line_items' => [[
            'price' => $data['priceId'],
            'quantity' => 1,
        ]],
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'client_reference_id' => session_id() // To identify the session when the user returns
    ];
    
    // Use customer ID if available, otherwise use customer_email
    if ($customer_id) {
        $checkout_params['customer'] = $customer_id;
    } else {
        $checkout_params['customer_email'] = $data['email'];
    }
    
    $checkout_session = \Stripe\Checkout\Session::create($checkout_params);
    
    error_log('Created checkout session with 3-day trial: ' . $checkout_session->id);
    
    // Return success with checkout URL and session ID
    echo json_encode([
        'success' => true,
        'id' => $checkout_session->id,
        'url' => $checkout_session->url
    ]);
    
} catch (\Exception $e) {
    // Log the error for debugging
    error_log('Stripe checkout error: ' . $e->getMessage());
    
    // Return detailed error response
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Payment system error: ' . $e->getMessage(),
        'error_details' => [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
