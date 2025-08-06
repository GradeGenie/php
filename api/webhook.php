<?php
require '../vendor/autoload.php';

require '../vendor/autoload.php';
require_once __DIR__ . '/../load_env.php';

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY'));

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$webhook_secret = 'whsec_59SKxNluITIp1rD3aT06wbFaD1pc0G21';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $webhook_secret
    );
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

switch ($event->type) {
    case 'checkout.session.completed':
        // Handle successful subscription creation
        $session = $event->data->object;
        $sessionId = $session->id;
        $customer_id = $session->customer;

        // Database connection
        require 'c.php';
        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            http_response_code(500);
            exit('Database connection failed: ' . $conn->connect_error);
        }

        // First attempt to update based on sessionId
        $stmt = $conn->prepare('UPDATE users SET stripeID = ?, active_sub = 1 WHERE stripeID = ?');
        $stmt->bind_param('ss', $customer_id, $sessionId);
        $stmt->execute();

        // Check if any rows were affected by the first update
        if ($stmt->affected_rows === 0) {
            // If no rows were affected, try updating based on customer_id instead
            $stmt = $conn->prepare('UPDATE users SET stripeID = ?, active_sub = 1 WHERE stripeID = ?');
            $stmt->bind_param('ss', $customer_id, $customer_id);
            $stmt->execute();
        }

        if ($stmt->affected_rows > 0) {
            http_response_code(200);
        } else {
            http_response_code(500);
        }

        $stmt->close();
        $conn->close();
        break;
    case 'invoice.paid':
        // Handle successful invoice payment
        break;
    case 'invoice.payment_failed':
        // Handle failed payment
        $invoice = $event->data->object;
        $customer_id = $invoice->customer;

        // Database connection
        require 'c.php';
        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            http_response_code(500);
            exit('Database connection failed: ' . $conn->connect_error);
        }

        // Update user's subscription status to inactive
        $stmt = $conn->prepare('UPDATE users SET active_sub = 0 WHERE stripeID = ?');
        $stmt->bind_param('s', $customer_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            http_response_code(200);
        } else {
            http_response_code(500);
        }

        $stmt->close();
        $conn->close();
        break;
    default:
        http_response_code(400);
        exit();
}

http_response_code(200);
?>
