<?php
// Include database configuration
require 'config.php';

// Set response header to JSON
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request method'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if the required fields are set
    if (isset($input['assignment_id']) && isset($input['assignment_name']) && isset($input['progress']) && isset($input['status'])) {
        $assignment_id = intval($input['assignment_id']);
        $assignment_name = $input['assignment_name'];
        $progress = intval($input['progress']);
        $status = $input['status'];

        // Create a new database connection
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // Check connection
        if ($conn->connect_error) {
            $response['message'] = 'Database connection failed: ' . $conn->connect_error;
        } else {
            // Prepare and bind
            $stmt = $conn->prepare("INSERT INTO grading_progress (assignment_id, assignment_name, progress, status) VALUES (?, ?, ?, ?)
                                    ON DUPLICATE KEY UPDATE progress = ?, status = ?, updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("isiiss", $assignment_id, $assignment_name, $progress, $status, $progress, $status);

            // Execute the statement
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Progress saved successfully';
            } else {
                $response['message'] = 'Error saving progress: ' . $stmt->error;
            }

            // Close the statement and connection
            $stmt->close();
            $conn->close();
        }
    } else {
        $response['message'] = 'Missing required fields';
    }
}

// Return the response as JSON
echo json_encode($response);
?>
