<?php
/**
 * Stripe Webhook Handler
 * 
 * This script handles Stripe webhook events to manage subscription lifecycle:
 * - Subscription created (trial started)
 * - Subscription trial ending
 * - Subscription payment succeeded/failed
 * - Subscription canceled
 */

// Include dependencies
require_once 'c.php';
require_once 'error-log.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Get the webhook payload and signature
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    // Initialize Stripe with the secret key
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Verify webhook signature
    // You should set this in your Stripe dashboard
    $endpoint_secret = 'whsec_your_webhook_signing_secret';
    
    // Log webhook received
    log_debug('Stripe webhook received', [
        'event_type' => $_SERVER['HTTP_STRIPE_EVENT_TYPE'] ?? 'unknown',
        'signature_present' => !empty($sig_header)
    ]);
    
    // Verify signature if we have a secret
    if ($endpoint_secret) {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            log_error('webhook_error', 'Invalid payload', ['error' => $e->getMessage()]);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid payload']);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            log_error('webhook_error', 'Invalid signature', ['error' => $e->getMessage()]);
            http_response_code(400);
            echo json_encode(['error' => 'Invalid signature']);
            exit();
        }
    } else {
        // If no webhook signing secret, parse the event manually
        $data = json_decode($payload, true);
        $event = \Stripe\Event::constructFrom($data);
    }
    
    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            handleCheckoutSessionCompleted($event->data->object);
            break;
            
        case 'customer.subscription.created':
            handleSubscriptionCreated($event->data->object);
            break;
            
        case 'customer.subscription.updated':
            handleSubscriptionUpdated($event->data->object);
            break;
            
        case 'customer.subscription.deleted':
            handleSubscriptionCanceled($event->data->object);
            break;
            
        case 'invoice.payment_succeeded':
            handleInvoicePaymentSucceeded($event->data->object);
            break;
            
        case 'invoice.payment_failed':
            handleInvoicePaymentFailed($event->data->object);
            break;
            
        default:
            // Unexpected event type
            log_debug('Unhandled event type', ['type' => $event->type]);
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    log_error('webhook_error', $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle checkout.session.completed event
 */
function handleCheckoutSessionCompleted($session) {
    global $conn;
    
    try {
        // Log the event
        log_debug('Checkout session completed', [
            'session_id' => $session->id,
            'customer_id' => $session->customer,
            'subscription_id' => $session->subscription,
            'client_reference_id' => $session->client_reference_id
        ]);
        
        // Get user ID from client_reference_id or metadata
        $userId = $session->client_reference_id;
        if (empty($userId) && isset($session->metadata->user_id)) {
            $userId = $session->metadata->user_id;
        }
        
        if (empty($userId)) {
            throw new Exception("User ID not found in checkout session");
        }
        
        // Get plan key from metadata
        $planKey = $session->metadata->plan_key ?? 'educator';
        
        // Update user record in database
        $stmt = $conn->prepare("UPDATE users SET 
            stripeID = ?,
            subscription_id = ?,
            subscription_status = 'trialing'
            WHERE uid = ?");
            
        $stmt->bind_param("ssi", 
            $session->customer,
            $session->subscription,
            $userId
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update subscription: " . $stmt->error);
        }
        
        // No need to insert a new record as we're updating the users table directly
        
        // User record already updated above
        
        // Get subscription details from Stripe
        $subscription = \Stripe\Subscription::retrieve($session->subscription);
        
        // Update trial end date
        if ($subscription->trial_end) {
            $trialEndDate = date('Y-m-d H:i:s', $subscription->trial_end);
            
            // Update user record with trial end date
            $stmt = $conn->prepare("UPDATE users SET 
                trial_end_date = ?,
                trial_ending = 0
                WHERE subscription_id = ?");
                
            $stmt->bind_param("ss", 
                $trialEndDate,
                $subscription->id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user trial end date: " . $stmt->error);
            }
        }
        
    } catch (Exception $e) {
        log_error('checkout_completed_error', $e->getMessage(), [
            'session_id' => $session->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Handle customer.subscription.created event
 */
function handleSubscriptionCreated($subscription) {
    global $conn;
    
    try {
        // Log the event
        log_debug('Subscription created', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer,
            'status' => $subscription->status,
            'trial_end' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null
        ]);
        
        // Update user record
        $stmt = $conn->prepare("UPDATE users SET 
            subscription_status = ?,
            trial_end_date = ?
            WHERE subscription_id = ?");
            
        $status = $subscription->status;
        $trialEndDate = $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null;
        
        $stmt->bind_param("sss", 
            $status,
            $trialEndDate,
            $subscription->id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        log_error('subscription_created_error', $e->getMessage(), [
            'subscription_id' => $subscription->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Handle customer.subscription.updated event
 */
function handleSubscriptionUpdated($subscription) {
    global $conn;
    
    try {
        // Log the event
        log_debug('Subscription updated', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'trial_end' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
            'cancel_at' => $subscription->cancel_at ? date('Y-m-d H:i:s', $subscription->cancel_at) : null
        ]);
        
        // Update user record
        $stmt = $conn->prepare("UPDATE users SET 
            subscription_status = ?,
            trial_end_date = ?
            WHERE subscription_id = ?");
            
        $status = $subscription->status;
        $trialEndDate = $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null;
        
        $stmt->bind_param("sss", 
            $status,
            $trialEndDate,
            $subscription->id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user: " . $stmt->error);
        }
        
        // Handle trial to active transition
        if ($subscription->status === 'active' && $subscription->trial_end && $subscription->trial_end < time()) {
            // Trial has ended and subscription is now active
            log_debug('Trial ended, subscription now active', [
                'subscription_id' => $subscription->id
            ]);
        }
        
    } catch (Exception $e) {
        log_error('subscription_updated_error', $e->getMessage(), [
            'subscription_id' => $subscription->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Handle customer.subscription.deleted event
 */
function handleSubscriptionCanceled($subscription) {
    global $conn;
    
    try {
        // Log the event
        log_debug('Subscription canceled', [
            'subscription_id' => $subscription->id,
            'customer_id' => $subscription->customer
        ]);
        
        // Update user record
        $stmt = $conn->prepare("UPDATE users SET 
            subscription_status = 'canceled'
            WHERE subscription_id = ?");
            
        $stmt->bind_param("s", $subscription->id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        log_error('subscription_canceled_error', $e->getMessage(), [
            'subscription_id' => $subscription->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Handle invoice.payment_succeeded event
 */
function handleInvoicePaymentSucceeded($invoice) {
    global $conn;
    
    try {
        // Only process subscription invoices
        if (empty($invoice->subscription)) {
            return;
        }
        
        // Log the event
        log_debug('Invoice payment succeeded', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription,
            'customer_id' => $invoice->customer,
            'amount_paid' => $invoice->amount_paid,
            'currency' => $invoice->currency
        ]);
        
        // Update user record
        $stmt = $conn->prepare("UPDATE users SET 
            subscription_status = 'active'
            WHERE subscription_id = ?");
            
        $stmt->bind_param("s", $invoice->subscription);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update user: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        log_error('invoice_payment_succeeded_error', $e->getMessage(), [
            'invoice_id' => $invoice->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}

/**
 * Handle invoice.payment_failed event
 */
function handleInvoicePaymentFailed($invoice) {
    global $conn;
    
    try {
        // Only process subscription invoices
        if (empty($invoice->subscription)) {
            return;
        }
        
        // Log the event
        log_debug('Invoice payment failed', [
            'invoice_id' => $invoice->id,
            'subscription_id' => $invoice->subscription,
            'customer_id' => $invoice->customer,
            'attempt_count' => $invoice->attempt_count
        ]);
        
        // If this is the final attempt, mark subscription as past_due
        if ($invoice->attempt_count >= 3) {
            // Update user record
            $stmt = $conn->prepare("UPDATE users SET 
                subscription_status = 'past_due'
                WHERE subscription_id = ?");
                
            $stmt->bind_param("s", $invoice->subscription);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update user: " . $stmt->error);
            }
        }
        
    } catch (Exception $e) {
        log_error('invoice_payment_failed_error', $e->getMessage(), [
            'invoice_id' => $invoice->id,
            'trace' => $e->getTraceAsString()
        ]);
    }
}
?>
