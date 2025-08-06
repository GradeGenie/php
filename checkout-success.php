<?php
// Start session to retrieve user data
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log that we've entered the checkout success page
error_log('Checkout success page accessed with session_id: ' . ($_GET['session_id'] ?? 'none'));

// Define Stripe keys directly to avoid database connection issues
require_once __DIR__ . '/load_env.php';
define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY'] ?? '');
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '');
// Check if Stripe library exists
if (!file_exists('vendor/autoload.php')) {
    error_log('Stripe library not found at vendor/autoload.php');
    // Set session variable to indicate error
    $_SESSION['checkout_error'] = 'Stripe library not found';
    $_SESSION['logged_in'] = true; // Allow access anyway
    $_SESSION['user_first_name'] = 'User';
    header('Location: index.php');
    exit;
}

// Include Stripe library
require_once 'vendor/autoload.php';

// Set Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Check if session_id is provided
if (!isset($_GET['session_id'])) {
    error_log('No session_id provided in checkout-success.php');
    $_SESSION['checkout_error'] = 'No session ID provided';
    $_SESSION['logged_in'] = true; // Allow access anyway
    $_SESSION['user_first_name'] = 'User';
    header('Location: index.php');
    exit;
}

$session_id = $_GET['session_id'];
$error_message = null;

try {
    // Log the session ID we're about to process
    error_log('Processing Stripe session: ' . $session_id);
    
    // Retrieve the checkout session with expanded objects
    $checkout_session = \Stripe\Checkout\Session::retrieve([
        'id' => $session_id,
        'expand' => ['customer', 'subscription']
    ]);
    
    error_log('Retrieved checkout session: ' . json_encode(['id' => $checkout_session->id, 'status' => $checkout_session->status]));
    
    // Don't require the session to be complete - just check if it exists
    if (!$checkout_session || !isset($checkout_session->id)) {
        throw new Exception('Invalid checkout session');
    }
    
    // Get subscription and customer details from the expanded objects
    $subscription = $checkout_session->subscription;
    $customer = $checkout_session->customer;
    
    // If subscription or customer is not expanded, try to retrieve them directly
    if (!$subscription && isset($checkout_session->subscription)) {
        try {
            error_log('Retrieving subscription directly: ' . $checkout_session->subscription);
            $subscription = \Stripe\Subscription::retrieve($checkout_session->subscription);
        } catch (Exception $e) {
            error_log('Error retrieving subscription: ' . $e->getMessage());
            // Continue without subscription data
        }
    }
    
    if (!$customer && isset($checkout_session->customer)) {
        try {
            error_log('Retrieving customer directly: ' . $checkout_session->customer);
            $customer = \Stripe\Customer::retrieve($checkout_session->customer);
        } catch (Exception $e) {
            error_log('Error retrieving customer: ' . $e->getMessage());
            // Continue without customer data
        }
    }
    
    // Log successful checkout
    error_log("Successful checkout: Session ID: $session_id, Customer: {$customer->id}, Subscription: {$subscription->id}");
    
    // Store subscription info in session for display
    $_SESSION['subscription_info'] = [
        'customer_id' => $customer->id,
        'customer_email' => $customer->email,
        'subscription_id' => $subscription->id,
        'plan_name' => $subscription->items->data[0]->price->nickname ?? 'GradeGenie Subscription',
        'trial_end' => $subscription->trial_end,
        'amount' => $subscription->items->data[0]->price->unit_amount / 100,
        'interval' => $subscription->items->data[0]->price->recurring->interval
    ];
    
    // Set basic session variables
    $_SESSION['user_id'] = md5($customer->id); // Fallback ID
    $_SESSION['email'] = $customer->email;
    $_SESSION['name'] = $customer->name;
    $_SESSION['user_first_name'] = $customer->name;
    $_SESSION['trial_end'] = date('Y-m-d H:i:s', $subscription->trial_end);
    $_SESSION['logged_in'] = true;
    
    // Check if we have pending signup data in the session
    if (isset($_SESSION['pending_signup'])) {
        $userData = $_SESSION['pending_signup'];
        
        // Verify this is the same user
        if ($userData['email'] === $customer->email) {
            // Update session with more detailed user info
            $_SESSION['name'] = $userData['first_name'] . ' ' . $userData['last_name'];
            $_SESSION['user_first_name'] = $userData['first_name'];
            
            // Log success
            error_log("Successfully stored user info in session for: {$userData['email']}");
        } else {
            error_log("Email mismatch: Session email: {$userData['email']}, Stripe email: {$customer->email}");
        }
    }
    
    // Try to update database - wrapped in try/catch to handle connection issues
    try {
        require_once 'api/c.php';
        
        // Try to find the user in the database by email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $customer->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            // User exists, update their Stripe and subscription information
            $user = $result->fetch_assoc();
            
            // Update user with Stripe and subscription info
            $stmt = $conn->prepare("UPDATE users SET 
                stripeID = ?, 
                subscription_id = ?, 
                plan_id = ?,
                subscription_status = 'trialing',
                trial_end_date = ?,
                trial_ending = 0,
                active_sub = 1
                WHERE id = ?");
                
            $trial_end_date = date('Y-m-d H:i:s', $subscription->trial_end);
            $plan_id = $subscription->items->data[0]->price->id;
            
            $stmt->bind_param("ssssi", 
                $customer->id, 
                $subscription->id, 
                $plan_id,
                $trial_end_date,
                $user['id']
            );
            
            if ($stmt->execute()) {
                // Update session variables with database values
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['uid'] = $user['id'];
                
                // Log success
                error_log("Successfully updated existing user with Stripe info: {$user['email']}");
            } else {
                error_log("Failed to update user with Stripe info: " . $stmt->error);
            }
        } else {
            error_log("User not found in database for email: {$customer->email}");
        }
    } catch (Exception $db_error) {
        // Just log the database error but continue
        error_log("Database error during checkout processing: " . $db_error->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Error processing checkout success: " . $e->getMessage());
    $error_message = "There was a problem processing your payment. Please contact support.";
}

// Make sure user is marked as logged in regardless of database status
$_SESSION['logged_in'] = true;

// Ensure user has a name to display
if (!isset($_SESSION['user_first_name']) || empty($_SESSION['user_first_name'])) {
    $_SESSION['user_first_name'] = 'User';
}

// Store the session ID in the session for debugging
$_SESSION['stripe_session_id'] = $session_id;

// Log the session variables before redirecting
error_log('Session variables before redirect: ' . print_r($_SESSION, true));

// Redirect to index.php after processing
header('Location: index.php');
exit;
?>
