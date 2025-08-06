<?php
// This file can be called directly or included from index.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define Stripe keys directly to avoid database connection issues
require_once __DIR__ . '/load_env.php';
if (!defined('STRIPE_SECRET_KEY')) {
    define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
}

if (!defined('STRIPE_PUBLISHABLE_KEY')) {
    define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
}

// Include Stripe library
require_once 'vendor/autoload.php';

// Include database connection
require_once 'api/c.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if this file is being called directly or included
$is_direct_access = (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__));

// Check if session_id is provided
if (!isset($_GET['session_id'])) {
    // No session ID, redirect to homepage only if direct access
    if ($is_direct_access) {
        header('Location: index.php');
        exit;
    } else {
        // If included from another file, just return
        return;
    }
}

$session_id = $_GET['session_id'];
$checkout_session = null;
$subscription = null;
$customer = null;

// Add debug logging
error_log("Processing Stripe session ID: $session_id");

try {
    // Retrieve the checkout session
    $checkout_session = \Stripe\Checkout\Session::retrieve($session_id);
    
    // Verify the session is complete
    if ($checkout_session->status !== 'complete') {
        throw new Exception('Checkout session is not complete');
    }
    
    // Get subscription details
    $subscription = \Stripe\Subscription::retrieve($checkout_session->subscription);
    
    // Get customer details
    $customer = \Stripe\Customer::retrieve($checkout_session->customer);
    
    // Log successful checkout
    error_log("Successful checkout: Session ID: $session_id, Customer: {$customer->id}, Subscription: {$subscription->id}");
    
    // Check if we have pending signup data in the session
    if (isset($_SESSION['pending_signup'])) {
        $userData = $_SESSION['pending_signup'];
        
        // Verify this is the same user
        if ($userData['email'] === $customer->email) {
            // Hash the password
            $hashed_password = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Calculate trial end date
            $trial_end_date = date('Y-m-d H:i:s', $subscription->trial_end);
            
            // Prepare user data for database insertion
            $email = $userData['email'];
            $name = $userData['first_name'] . ' ' . $userData['last_name'];
            $stripeID = $customer->id;
            $subscription_id = $subscription->id;
            $plan_id = $subscription->items->data[0]->price->id;
            $subscription_status = 'trialing';
            $trial_ending = 0; // Not ending yet
            $active_sub = 1; // Active subscription
            
            // Insert user into database
            try {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, stripeID, subscription_id, plan_id, subscription_status, trial_end_date, trial_ending, active_sub) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ssssssssii", 
                    $name, 
                    $email, 
                    $hashed_password, 
                    $stripeID, 
                    $subscription_id, 
                    $plan_id, 
                    $subscription_status, 
                    $trial_end_date, 
                    $trial_ending, 
                    $active_sub
                );
                
                if ($stmt->execute()) {
                    // Set session variables for the logged-in user
                    $_SESSION['user_id'] = $conn->insert_id;
                    $_SESSION['uid'] = $conn->insert_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $name;
                    $_SESSION['user_first_name'] = $name;
                    $_SESSION['trial_end'] = $trial_end_date;
                    $_SESSION['logged_in'] = true;
                    
                    // Set session cookie parameters to ensure persistence
                    session_set_cookie_params(86400 * 30); // 30 days
                    session_regenerate_id(true); // Regenerate session ID for security
                    
                    // Clear the pending signup data
                    unset($_SESSION['pending_signup']);
                    
                    // Redirect to index.php with success parameter
                    header('Location: index.php?signup=success');
                    exit;
                } else {
                    error_log("Database insertion failed: " . $stmt->error);
                    // Redirect to index.php anyway, but without success parameter
                    header('Location: index.php');
                    exit;
                }
            } catch (Exception $dbException) {
                error_log("Database error during user creation: " . $dbException->getMessage());
                // Redirect to index.php anyway
                header('Location: index.php');
                exit;
            }
        } else {
            error_log("Email mismatch: Session email: {$userData['email']}, Stripe email: {$customer->email}");
            header('Location: index.php');
            exit;
        }
    } else {
        error_log("No pending signup data found in session for checkout: $session_id");
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error processing checkout: " . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>
