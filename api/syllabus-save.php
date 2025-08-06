<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once 'c.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Debug
if (!$data) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid JSON data", "raw" => file_get_contents("php://input")));
    exit();
}

// Check if user is authenticated using session
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized - Please log in"));
    exit();
}

$user_id = $_SESSION['user_id'];

// Make sure data is not empty
if (
    !empty($data->title) &&
    !empty($data->content) &&
    !empty($data->form_data) &&
    !empty($data->class_id)
) {
    try {
        // Create database connection directly
        $conn = new mysqli($host, $username, $password, $database);
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Check if class exists and belongs to user
        $stmt = $conn->prepare("SELECT * FROM classes WHERE cid = ? AND owner = ?");
        $stmt->bind_param("ii", $data->class_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            http_response_code(403);
            echo json_encode(array("message" => "Class not found or not owned by user"));
            exit();
        }
        
        // Check if we're updating an existing syllabus or creating a new one
        if (!empty($data->id)) {
            // Update existing syllabus
            $stmt = $conn->prepare("UPDATE syllabi SET title = ?, content = ?, form_data = ?, updated_on = NOW() WHERE id = ? AND cid = ?");
            $form_data_json = $data->form_data; // Already a JSON string from the client
            $stmt->bind_param("sssii", $data->title, $data->content, $form_data_json, $data->id, $data->class_id);
            
            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "Syllabus updated successfully", "id" => $data->id));
            } else {
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "Unable to update syllabus"));
            }
        } else {
            // Create new syllabus
            // Debug log the data being saved
            error_log("Saving new syllabus for class ID: {$data->class_id}, title: {$data->title}");
            
            $stmt = $conn->prepare("INSERT INTO syllabi (cid, title, content, form_data) VALUES (?, ?, ?, ?)");
            $form_data_json = $data->form_data; // Already a JSON string from the client
            
            // Log the exact values being inserted
            error_log("SQL: INSERT INTO syllabi (cid, title, content, form_data) VALUES ({$data->class_id}, '{$data->title}', [content], [form_data])");
            
            $stmt->bind_param("isss", $data->class_id, $data->title, $data->content, $form_data_json);
            
            if ($stmt->execute()) {
                $syllabus_id = $conn->insert_id;
                error_log("Syllabus saved successfully with ID: {$syllabus_id}");
                http_response_code(201);
                echo json_encode(array("success" => true, "message" => "Syllabus created successfully", "id" => $syllabus_id));
            } else {
                error_log("Error saving syllabus: " . $stmt->error);
                http_response_code(500);
                echo json_encode(array("success" => false, "message" => "Unable to create syllabus: " . $stmt->error));
            }
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error: " . $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Unable to save syllabus. Data is incomplete.",
        "required" => array("title", "content", "form_data", "class_id"),
        "received" => json_encode($data)
    ));
}
?>
