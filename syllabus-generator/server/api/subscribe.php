<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once '../c.php';
require_once '../vendor/autoload.php'; // For Stripe and JWT

// Set your Stripe API key
\Stripe\Stripe::setApiKey('sk_test_your_stripe_secret_key');

// Get authorization header
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Extract the token
$token = null;
if (!empty($auth)) {
    if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        $token = $matches[1];
    }
}

// Check if token exists
if (!$token) {
    http_response_code(401);
    echo json_encode(array("message" => "Authentication required"));
    exit;
}

try {
    // Decode token
    $decoded = \Firebase\JWT\JWT::decode($token, "your_jwt_secret_key", array('HS256'));
    
    $conn = getConnection();
    
    // Get user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->bind_param("i", $decoded->userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        // Check if payment method ID is provided
        if (empty($data->paymentMethodId)) {
            http_response_code(400);
            echo json_encode(array("message" => "Payment method ID is required"));
            exit;
        }
        
        // Check if user already has an active subscription
        if ($user['subscription_status'] == 'active') {
            http_response_code(400);
            echo json_encode(array("message" => "User already has an active subscription"));
            exit;
        }
        
        // If user doesn't have a Stripe customer ID, create one
        $stripeId = $user['stripeID'];
        if (empty($stripeId)) {
            $customer = \Stripe\Customer::create([
                'email' => $user['email'],
                'name' => $user['name'],
                'payment_method' => $data->paymentMethodId,
                'invoice_settings' => [
                    'default_payment_method' => $data->paymentMethodId,
                ]
            ]);
            $stripeId = $customer->id;
            
            // Update user's Stripe ID
            $stmt = $conn->prepare("UPDATE users SET stripeID = ? WHERE uid = ?");
            $stmt->bind_param("si", $stripeId, $user['uid']);
            $stmt->execute();
        } else {
            // Attach payment method to existing customer
            \Stripe\PaymentMethod::attach($data->paymentMethodId, [
                'customer' => $stripeId
            ]);
            
            // Set as default payment method
            \Stripe\Customer::update($stripeId, [
                'invoice_settings' => [
                    'default_payment_method' => $data->paymentMethodId,
                ]
            ]);
        }
        
        // Create subscription with hardcoded price
        $subscription = \Stripe\Subscription::create([
            'customer' => $stripeId,
            'items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Syllabus Generator Premium',
                            'description' => 'Premium access to Syllabus Generator features',
                        ],
                        'unit_amount' => 1899, // $18.99 in cents
                        'recurring' => [
                            'interval' => 'month'
                        ]
                    ]
                ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ]);
        
        // Update user in database
        $stmt = $conn->prepare("UPDATE users SET subscription_id = ?, plan_id = ?, subscription_status = ?, active_sub = 1 WHERE uid = ?");
        $plan_id = $subscription->items->data[0]->price->id;
        $status = $subscription->status;
        $stmt->bind_param("sssi", $subscription->id, $plan_id, $status, $user['uid']);
        $stmt->execute();
        
        // Get updated user
        $stmt = $conn->prepare("SELECT * FROM users WHERE uid = ?");
        $stmt->bind_param("i", $user['uid']);
        $stmt->execute();
        $result = $stmt->get_result();
        $updated_user = $result->fetch_assoc();
        
        // Remove password from response
        unset($updated_user['password']);
        
        // Return success response
        http_response_code(200);
        echo json_encode(array(
            "user" => $updated_user,
            "subscription" => $subscription
        ));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "User not found"));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Subscription failed: " . $e->getMessage()));
}
?>
