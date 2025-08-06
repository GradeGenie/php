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

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if (
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->password) &&
    !empty($data->paymentMethodId)
) {
    try {
        $conn = getConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $data->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Email already exists
            http_response_code(400);
            echo json_encode(array("message" => "User with this email already exists"));
            exit;
        }
        
        // Create Stripe customer
        $customer = \Stripe\Customer::create([
            'email' => $data->email,
            'name' => $data->name,
            'payment_method' => $data->paymentMethodId,
            'invoice_settings' => [
                'default_payment_method' => $data->paymentMethodId,
            ]
        ]);
        
        // Hash password
        $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);
        
        // Calculate trial end date (3 days from now)
        $trial_end_date = date('Y-m-d H:i:s', strtotime('+3 days'));
        
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, stripeID, trial_end_date, trial_ending) VALUES (?, ?, ?, ?, ?, ?)");
        $trial_ending = 0;
        $stmt->bind_param("sssssi", $data->name, $data->email, $hashed_password, $customer->id, $trial_end_date, $trial_ending);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Generate JWT token
            $iat = time();
            $exp = $iat + 60*60*24*7; // Token expires in 7 days
            
            $payload = array(
                "iat" => $iat,
                "exp" => $exp,
                "userId" => $user_id
            );
            
            $jwt = \Firebase\JWT\JWT::encode($payload, "your_jwt_secret_key", 'HS256');
            
            // Get the newly created user
            $stmt = $conn->prepare("SELECT * FROM users WHERE uid = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            // Remove password from response
            unset($user['password']);
            
            // Return success response
            http_response_code(201);
            echo json_encode(array(
                "token" => $jwt,
                "user" => $user
            ));
        } else {
            // Failed to create user
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create user"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Registration failed: " . $e->getMessage()));
    }
} else {
    // Data is incomplete
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
?>
