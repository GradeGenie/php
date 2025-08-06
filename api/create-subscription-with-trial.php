<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users, but log them

// Set proper content type for JSON responses
header('Content-Type: application/json');

// Include database connection and Stripe
try {
    require_once 'c.php';
    require_once '../vendor/autoload.php';
} catch (Exception $e) {
    error_log('Include error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server configuration error']);
    exit;
}

// Ensure database connection is established
if (!isset($conn) || $conn->connect_error) {
    error_log('Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'));
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Set Stripe API key
try {
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
} catch (Exception $e) {
    error_log('Stripe API key error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Payment system configuration error']);
    exit;
}

// Get the JSON data
$jsonData = file_get_contents('php://input');
if (!$jsonData) {
    error_log('No input data received');
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit;
}

$data = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Check if all required fields are present
if (!isset($data['email']) || !isset($data['first_name']) || !isset($data['last_name']) || !isset($data['password']) || !isset($data['price_id']) || !isset($data['payment_method_id'])) {
    error_log('Missing required fields: ' . json_encode(array_keys($data)));
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Log the incoming request data for debugging
    error_log('Signup request data: ' . json_encode($data));
    
    // Create a customer in Stripe
    $customer = \Stripe\Customer::create([
        'email' => $data['email'],
        'name' => $data['first_name'] . ' ' . $data['last_name'],
    ]);
    
    // Log successful customer creation
    error_log('Stripe customer created: ' . $customer->id);
    
    // Attach the payment method to the customer
    try {
        // Attach the payment method to the customer
        \Stripe\PaymentMethod::attach(
            $data['payment_method_id'],
            ['customer' => $customer->id]
        );
        
        // Set as the default payment method
        \Stripe\Customer::update($customer->id, [
            'invoice_settings' => [
                'default_payment_method' => $data['payment_method_id'],
            ],
        ]);
        
        error_log('Payment method attached to customer: ' . $data['payment_method_id']);
    } catch (\Exception $e) {
        error_log('Error attaching payment method: ' . $e->getMessage());
        throw $e; // Re-throw to be caught by the outer try-catch
    }
    
    // Create a subscription with a 3-day trial
    $subscription = \Stripe\Subscription::create([
        'customer' => $customer->id,
        'items' => [[
            'price' => $data['price_id'],
        ]],
        'trial_period_days' => 3, // 3-day free trial
        'default_payment_method' => $data['payment_method_id'],
    ]);
    
    // Calculate trial end date
    $trial_end_date = date('Y-m-d H:i:s', strtotime('+3 days'));
    
    // Hash the password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Log the database insertion attempt
    error_log('Attempting to insert user into database: ' . $email);
    
    // Check if user already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $checkUser->store_result();
    
    if ($checkUser->num_rows > 0) {
        error_log('User already exists with email: ' . $email);
        echo json_encode(['success' => false, 'message' => 'A user with this email already exists']);
        return;
    }
    $checkUser->close();
    
    // Get database table structure for debugging
    try {
        $tableInfo = $conn->query("DESCRIBE users");
        $columns = [];
        while ($row = $tableInfo->fetch_assoc()) {
            $columns[] = $row['Field'] . ' (' . $row['Type'] . ')';
        }
        error_log('Database table structure: ' . implode(', ', $columns));
    } catch (\Exception $e) {
        error_log('Error fetching table structure: ' . $e->getMessage());
    }
    
    // Insert user into database - using existing DB structure
    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, stripeID, subscription_id, plan_id, subscription_status, trial_end_date, trial_ending, active_sub) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new \Exception("Prepare failed: " . $conn->error);
        }
        
        // Set values for insertion
        $name = $data['first_name'] . ' ' . $data['last_name'];
        $email = $data['email'];
        $stripeID = $customer->id;
        $subscription_id = $subscription->id;
        $plan_id = $data['price_id'];
        $subscription_status = 'trialing';
        $trial_ending = 0; // Not ending yet
        $active_sub = 1; // Active subscription
        
        error_log('Binding parameters for database insertion');
        $bindResult = $stmt->bind_param("ssssssssii", 
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
        
        if (!$bindResult) {
            throw new \Exception("Binding parameters failed: " . $stmt->error);
        }
        
        error_log('Executing database insertion');
        if ($stmt->execute()) {
        // Start a session and store user info
        session_start();
        // Set the primary session variables that menu.php checks for
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['uid'] = $conn->insert_id;
        
        // Set additional session variables
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['trial_end'] = $trial_end_date;
        $_SESSION['logged_in'] = true;
        
        // Set session cookie parameters to ensure persistence
        session_set_cookie_params(86400 * 30); // 30 days
        session_regenerate_id(true); // Regenerate session ID for security
        
        // Return success
        echo json_encode([
            'success' => true, 
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'trial_end' => $trial_end_date
        ]);
    } else {
        // If database insertion fails, log detailed error and cancel the subscription
        error_log('Database insertion failed: ' . $stmt->error);
        
        // Get more detailed error information
        error_log('MySQL Error: ' . $conn->error . ' (Code: ' . $conn->errno . ')');
        
        // Cancel the subscription in Stripe
        try {
            \Stripe\Subscription::update($subscription->id, ['cancel_at_period_end' => true]);
            error_log('Subscription marked for cancellation: ' . $subscription->id);
        } catch (\Exception $e) {
            error_log('Error cancelling subscription: ' . $e->getMessage());
        }
        
        echo json_encode(['success' => false, 'message' => 'Database error: Unable to create user account. Please try again or contact support.']);
    }
} catch (\Exception $dbException) {
    // Handle any exceptions in the database insertion process
    error_log('Database exception: ' . $dbException->getMessage());
    error_log('Stack trace: ' . $dbException->getTraceAsString());
    
    // Cancel the subscription in Stripe
    try {
        if (isset($subscription) && $subscription->id) {
            \Stripe\Subscription::update($subscription->id, ['cancel_at_period_end' => true]);
            error_log('Subscription marked for cancellation after exception: ' . $subscription->id);
        }
    } catch (\Exception $e) {
        error_log('Error cancelling subscription after exception: ' . $e->getMessage());
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $dbException->getMessage()]);
    }
    
} catch (\Exception $e) {
    // Log the error for debugging with full details
    error_log('Signup error: ' . $e->getMessage());
    error_log('Error trace: ' . $e->getTraceAsString());
    
    // Check for specific Stripe errors
    if ($e instanceof \Stripe\Exception\CardException) {
        // Card was declined
        echo json_encode([
            'success' => false, 
            'message' => 'Your card was declined: ' . $e->getMessage(),
            'error_type' => 'card_error'
        ]);
    } else if ($e instanceof \Stripe\Exception\RateLimitException ||
              $e instanceof \Stripe\Exception\InvalidRequestException ||
              $e instanceof \Stripe\Exception\AuthenticationException ||
              $e instanceof \Stripe\Exception\ApiConnectionException ||
              $e instanceof \Stripe\Exception\ApiErrorException) {
        // Other Stripe errors
        echo json_encode([
            'success' => false, 
            'message' => 'There was an issue with the payment processor. Please try again later.',
            'error_type' => 'stripe_error'
        ]);
    } else {
        // Generic server error
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred during signup. Please try again or contact support.',
            'error_type' => 'server_error'
        ]);
    }
    
    // If a customer was created but subscription failed, clean up
    if (isset($customer) && $customer->id) {
        try {
            \Stripe\Customer::delete($customer->id);
            error_log('Deleted customer after error: ' . $customer->id);
        } catch (\Exception $deleteError) {
            error_log('Error deleting customer: ' . $deleteError->getMessage());
        }
    }
}
?>
