<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once '../c.php';
require_once '../vendor/autoload.php'; // For Stripe

// Set your Stripe API key
\Stripe\Stripe::setApiKey('sk_test_your_stripe_secret_key');

// Get the webhook payload and signature
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, 'whsec_your_webhook_secret'
    );
    
    $conn = getConnection();
    
    // Handle the event
    switch ($event->type) {
        case 'invoice.payment_succeeded':
            $invoice = $event->data->object;
            // Update subscription status
            if ($invoice->subscription) {
                $stmt = $conn->prepare("UPDATE users SET subscription_status = 'active', active_sub = 1 WHERE subscription_id = ?");
                $stmt->bind_param("s", $invoice->subscription);
                $stmt->execute();
            }
            break;
            
        case 'invoice.payment_failed':
            $invoice = $event->data->object;
            // Update subscription status
            if ($invoice->subscription) {
                $stmt = $conn->prepare("UPDATE users SET subscription_status = 'past_due' WHERE subscription_id = ?");
                $stmt->bind_param("s", $invoice->subscription);
                $stmt->execute();
            }
            break;
            
        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            // Update subscription status
            $stmt = $conn->prepare("UPDATE users SET subscription_status = 'canceled', active_sub = 0 WHERE subscription_id = ?");
            $stmt->bind_param("s", $subscription->id);
            $stmt->execute();
            break;
            
        default:
            // Unexpected event type
            error_log('Received unknown event type: ' . $event->type);
    }
    
    http_response_code(200);
    echo json_encode(['received' => true]);
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
} catch (Exception $e) {
    // Other error
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?>
