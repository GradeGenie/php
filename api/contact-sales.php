<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database connection
require_once 'c.php';

// Start session
session_start();

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if (
    !empty($data->name) &&
    !empty($data->email) &&
    !empty($data->organization) &&
    !empty($data->seats) &&
    !empty($data->plan_type)
) {
    try {
        // Create database connection
        $conn = new mysqli($host, $username, $password, $database);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Insert sales inquiry into database
        $stmt = $conn->prepare("INSERT INTO sales_inquiries (name, email, organization, seats, message, plan_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssiss", $data->name, $data->email, $data->organization, $data->seats, $data->message, $data->plan_type);
        
        if ($stmt->execute()) {
            // Send email notification to sales team
            $to = "serene@getgradegenie.com";
            $subject = "New Sales Inquiry: " . $data->plan_type . " Plan";
            
            $message = "Name: " . $data->name . "\n";
            $message .= "Email: " . $data->email . "\n";
            $message .= "Organization: " . $data->organization . "\n";
            $message .= "Seats: " . $data->seats . "\n";
            $message .= "Plan: " . $data->plan_type . "\n\n";
            $message .= "Message:\n" . $data->message;
            
            $headers = "From: noreply@getgradegenie.com";
            
            // Attempt to send email, but don't fail if it doesn't work
            mail($to, $subject, $message, $headers);
            
            // Return success response
            http_response_code(200);
            echo json_encode(array("success" => true, "message" => "Your inquiry has been submitted successfully."));
        } else {
            throw new Exception("Failed to save inquiry: " . $stmt->error);
        }
        
        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("success" => false, "message" => "Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("success" => false, "message" => "Missing required fields."));
}
?>
