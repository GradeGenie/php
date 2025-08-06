<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
require_once 'c.php';

// Include Stripe
require_once 'vendor/autoload.php';

// Set your Stripe API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Error logging function
function log_error($message, $details = []) {
    error_log("[" . date('Y-m-d H:i:s') . "] ERROR in register-with-trial.php: " . $message . " - Details: " . json_encode($details));
}

// Make sure data is not empty
if (
    !empty($data->first_name) &&
    !empty($data->last_name) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->payment_method_id) &&
    !empty($data->price_id)
) {
    try {
        // Create database connection
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            log_error("Database connection failed", ["error" => $conn->connect_error]);
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $data->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Email already exists
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "User with this email already exists"]);
            exit;
        }
        
        // Format user's full name
        $fullName = $data->first_name . ' ' . $data->last_name;
        
        try {
            // Create Stripe customer
            $customer = \Stripe\Customer::create([
                'email' => $data->email,
                'name' => $fullName,
                'payment_method' => $data->payment_method_id,
                'invoice_settings' => [
                    'default_payment_method' => $data->payment_method_id,
                ]
            ]);
            
            // Create subscription with trial period
            $trialEnd = time() + (3 * 24 * 60 * 60); // 3 days from now
            
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer->id,
                'items' => [[
                    'price' => $data->price_id,
                ]],
                'trial_end' => $trialEnd,
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
                'expand' => ['latest_invoice.payment_intent'],
            ]);
        
            // Hash the password
            $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
            
            // Calculate trial end date
            $trialEndsAt = date('Y-m-d H:i:s', $trialEnd);
            
            // Start transaction
            $conn->begin_transaction();
            
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, stripe_id, stripe_customer_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $data->first_name, $data->last_name, $data->email, $hashed_password, $subscription->id, $customer->id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error creating user: " . $stmt->error);
            }
            
            $userId = $conn->insert_id;
            
            // Update user with subscription info
            $stmt = $conn->prepare("UPDATE users SET 
                subscription_id = ?, 
                subscription_status = ?, 
                trial_ends_at = ?,
                plan_id = ?,
                billing_period = ?
                WHERE id = ?");
                
            $status = $subscription->status;
            $billingPeriod = $data->billing_period;
            
            $stmt->bind_param("sssssi", 
                $subscription->id, 
                $status, 
                $trialEndsAt,
                $data->price_id,
                $billingPeriod,
                $userId
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Error updating subscription info: " . $stmt->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            // Create session for the user
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $data->email;
            $_SESSION['first_name'] = $data->first_name;
            $_SESSION['last_name'] = $data->last_name;
            
            // Return success response
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully with 3-day free trial',
                'user' => [
                    'id' => $userId,
                    'first_name' => $data->first_name,
                    'last_name' => $data->last_name,
                    'email' => $data->email,
                    'trial_ends_at' => $trialEndsAt
                ],
                'subscription' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'trial_end' => date('Y-m-d H:i:s', $subscription->trial_end)
                ]
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            log_error("Card declined", ["error" => $e->getMessage(), "code" => $e->getStripeCode()]);
            http_response_code(400);
            echo json_encode(["success" => false, "message" => $e->getMessage()]);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            log_error("Rate limit exceeded", ["error" => $e->getMessage()]);
            http_response_code(429);
            echo json_encode(["success" => false, "message" => "Too many requests. Please try again later."]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            log_error("Invalid Stripe request", ["error" => $e->getMessage(), "param" => $e->getStripeParam()]);
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment information. Please check your details."]);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            log_error("Stripe authentication failed", ["error" => $e->getMessage()]);
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Payment system configuration error. Please contact support."]);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            log_error("Stripe API connection error", ["error" => $e->getMessage()]);
            http_response_code(503);
            echo json_encode(["success" => false, "message" => "Could not connect to payment processor. Please try again later."]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Generic API error
            log_error("Stripe API error", ["error" => $e->getMessage()]);
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Payment processing error. Please try again later."]);
        }
    } catch (Exception $e) {
        // Exception occurred
        if (isset($conn) && $conn->ping()) {
            $conn->rollback(); // Rollback transaction if active
        }
        
        log_error("General exception", ["error" => $e->getMessage(), "trace" => $e->getTraceAsString()]);
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
    } finally {
        if (isset($conn) && $conn->ping()) {
            $conn->close();
        }
    }
} else {
    // Data is incomplete
    log_error("Incomplete data", ["data" => json_encode($data)]);
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Unable to create user. Data is incomplete."]);
}
?>
