<?php
require '../vendor/autoload.php';
require_once __DIR__ . '/../load_env.php';

header('Content-Type: application/json');

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$input = file_get_contents('php://input');
$data = json_decode($input, true); // Ensure we are parsing JSON correctly

// Log received data for debugging
file_put_contents('php://stderr', print_r($data, TRUE));

if (!isset($data['email']) || !isset($data['priceId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$success_url = 'https://app.getgradegenie.com/upgrade_success.php?session_id={CHECKOUT_SESSION_ID}';
$cancel_url = 'https://app.getgradegenie.com/upgrade_return.php';

try {
    require 'c.php';
    if ($conn->connect_error) {
        http_response_code(500);
        exit('Database connection failed: ' . $conn->connect_error);
    }

    // Check for existing stripeID in the database
    $stmt = $conn->prepare('SELECT stripeID FROM users WHERE email = ?');
    $stmt->bind_param('s', $data['email']);
    $stmt->execute();
    $stmt->bind_result($stripeID);
    $stmt->fetch();
    $stmt->close();

    $checkout_session_data = [
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $data['priceId'],
            'quantity' => 1,
        ]],
        'mode' => 'subscription',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
    ];

    if (isset($stripeID) && strpos($stripeID, 'cus_') === 0) {
        // If stripeID starts with 'cus_', use it as the customer ID
        $checkout_session_data['customer'] = $stripeID;
    } else {
        // Otherwise, use the email to create a new customer
        $checkout_session_data['customer_email'] = $data['email'];
    }

    // Create the Checkout Session
    $checkout_session = \Stripe\Checkout\Session::create($checkout_session_data);

    echo json_encode(['id' => $checkout_session->id]);

    $conn->close();

} catch (Exception $e) {
    // Log the error for debugging
    file_put_contents('php://stderr', $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
