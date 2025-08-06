<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
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

// Create database connection directly
try {
    require_once 'c.php';
    
    // Verify connection is established
    if (!$conn || $conn->connect_error) {
        error_log("Database connection failed in syllabus-get.php");
        throw new Exception("Database connection failed");
    }
    
    error_log("Database connection established successfully");
    
    // Check if we're getting a specific syllabus or all syllabi for a class
    if (isset($_GET['id'])) {
        // Get specific syllabus
        $syllabus_id = $_GET['id'];
        
        $stmt = $conn->prepare("
            SELECT s.* 
            FROM syllabi s
            JOIN classes c ON s.cid = c.cid
            WHERE s.id = ? AND c.owner = ?
        ");
        $stmt->bind_param("ii", $syllabus_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $syllabus = $result->fetch_assoc();
            // Decode the JSON form data
            $syllabus['form_data'] = json_decode($syllabus['form_data']);
            
            http_response_code(200);
            echo json_encode($syllabus);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Syllabus not found"));
        }
    } else if (isset($_GET['class_id'])) {
        // Get all syllabi for a class
        $class_id = $_GET['class_id'];
        
        // Verify class belongs to user
        $stmt = $conn->prepare("SELECT * FROM classes WHERE cid = ? AND owner = ?");
        $stmt->bind_param("ii", $class_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(403);
            echo json_encode(array("message" => "Class not found or not owned by user"));
            exit();
        }
        
        // Debug log
        error_log("Fetching syllabi for class ID: {$class_id} and user ID: {$user_id}");
        
        // Get syllabi for class
        $query = "SELECT id, title, created_on, updated_on FROM syllabi WHERE cid = ? ORDER BY updated_on DESC";
        error_log("SQL Query: {$query} with class_id: {$class_id}");
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Log the number of results
        $num_rows = $result->num_rows;
        error_log("Found {$num_rows} syllabi for class ID: {$class_id}");
        
        $syllabi = array();
        while ($row = $result->fetch_assoc()) {
            $syllabi[] = $row;
        }
        
        http_response_code(200);
        echo json_encode($syllabi);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Missing syllabus ID or class ID"));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Error: " . $e->getMessage()));
}
?>
