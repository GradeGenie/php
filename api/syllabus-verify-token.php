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

// Get authorization header
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Extract the token
$token = null;
if (!empty($auth)) {
    if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
        $token = $matches[1];
    }
}

// Check if token exists
if (!$token) {
    http_response_code(401);
    echo json_encode(array("message" => "Authentication required"));
    exit;
}

try {
    // Decode token
    $decoded = \Firebase\JWT\JWT::decode($token, "your_jwt_secret_key", array('HS256'));
    
    $conn = getConnection();
    
    // Get user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->bind_param("i", $decoded->userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if trial is ending soon (less than 24 hours)
        if ($user['trial_end_date']) {
            $trial_end_date = new DateTime($user['trial_end_date']);
            $now = new DateTime();
            $one_day_from_now = new DateTime();
            $one_day_from_now->add(new DateInterval('P1D'));
            
            if ($trial_end_date <= $one_day_from_now && $trial_end_date > $now && 
                $user['trial_ending'] == 0 && $user['subscription_status'] != 'active') {
                // Update trial_ending flag
                $stmt = $conn->prepare("UPDATE users SET trial_ending = 1 WHERE uid = ?");
                $stmt->bind_param("i", $user['uid']);
                $stmt->execute();
                $user['trial_ending'] = 1;
            }
        }
        
        // Remove password from response
        unset($user['password']);
        
        // Return success response
        http_response_code(200);
        echo json_encode(array("user" => $user));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "User not found"));
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array("message" => "Invalid token"));
}
?>
