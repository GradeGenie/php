<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once 'c.php';
require_once '../vendor/autoload.php'; // For JWT

// Check if user is authenticated using session
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized - Please log in"));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get syllabus ID
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Missing syllabus ID"));
    exit();
}

$syllabus_id = $data->id;

try {
    // Create database connection directly
    if (!$conn || $conn->connect_error) {
        error_log("Database connection failed in syllabus-delete.php");
        throw new Exception("Database connection failed");
    }
    
    error_log("Attempting to delete syllabus ID: {$syllabus_id} for user ID: {$user_id}");
    
    // Verify syllabus belongs to a class owned by the user
    $stmt = $conn->prepare("
        SELECT s.id 
        FROM syllabi s
        JOIN classes c ON s.cid = c.cid
        WHERE s.id = ? AND c.owner = ?
    ");
    $stmt->bind_param("ii", $syllabus_id, $user_id);
    if (!$stmt->execute()) {
        error_log("Error executing verification query: " . $stmt->error);
        throw new Exception("Error verifying syllabus ownership: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    error_log("Verification query found {$result->num_rows} matching syllabi");
    
    if ($result->num_rows == 0) {
        error_log("Syllabus ID {$syllabus_id} not found or not owned by user {$user_id}");
        http_response_code(403);
        echo json_encode(array("message" => "Syllabus not found or not owned by user"));
        exit();
    }
    
    // Delete the syllabus
    error_log("Preparing to delete syllabus ID: {$syllabus_id}");
    $stmt = $conn->prepare("DELETE FROM syllabi WHERE id = ?");
    $stmt->bind_param("i", $syllabus_id);
    
    if ($stmt->execute()) {
        error_log("Successfully deleted syllabus ID: {$syllabus_id}");
        http_response_code(200);
        echo json_encode(array("message" => "Syllabus deleted successfully"));
    } else {
        error_log("Failed to delete syllabus ID: {$syllabus_id}. Error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(array("message" => "Unable to delete syllabus: " . $stmt->error));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>
