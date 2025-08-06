<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once '../c.php';
require_once '../vendor/autoload.php'; // For JWT

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if (
    !empty($data->email) &&
    !empty($data->password)
) {
    try {
        $conn = getConnection();
        
        // Get user from database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $data->email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($data->password, $user['password'])) {
                // Update last login
                $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE uid = ?");
                $stmt->bind_param("i", $user['uid']);
                $stmt->execute();
                
                // Generate JWT token
                $iat = time();
                $exp = $iat + 60*60*24*7; // Token expires in 7 days
                
                $payload = array(
                    "iat" => $iat,
                    "exp" => $exp,
                    "userId" => $user['uid']
                );
                
                $jwt = \Firebase\JWT\JWT::encode($payload, "your_jwt_secret_key", 'HS256');
                
                // Remove password from response
                unset($user['password']);
                
                // Return success response
                http_response_code(200);
                echo json_encode(array(
                    "token" => $jwt,
                    "user" => $user
                ));
            } else {
                // Invalid password
                http_response_code(401);
                echo json_encode(array("message" => "Invalid email or password"));
            }
        } else {
            // User not found
            http_response_code(401);
            echo json_encode(array("message" => "Invalid email or password"));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Login failed: " . $e->getMessage()));
    }
} else {
    // Data is incomplete
    http_response_code(400);
    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}
?>
