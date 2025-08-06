<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

require 'c.php'; // Database connection
$conn = new mysqli($host, $username, $password, $database);

// Capture any unexpected output during script execution
$output = '';

if ($conn->connect_error) {
    $output .= 'Database connection failed: ' . $conn->connect_error;
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner = $_SESSION['user_id'] ?? 0;
    $title = $_POST['assignment-title'] ?? 'New Rubric'; // Ensure this matches the form
    $description = $_POST['description'] ?? 'Custom';
    $content = $_POST['content'] ?? 'Custom';
    $subject = $_POST['subject'] ?? 'Custom'; // Default if not provided
    $level = $_POST['level'] ?? 'Custom'; // Default if not provided
    $style = $_POST['style'] ?? 'Simple'; // Default if not provided

    // Validate required fields
    if (empty($title) || empty($content)) {
        $output .= 'Required fields are missing.';
    } else {
        // Prepare and execute the insert statement
        $stmt = $conn->prepare("INSERT INTO rubrics (owner, title, description, content, subject, level, assignment_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $owner, $title, $description, $content, $subject, $level, $style);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'rubric_id' => $stmt->insert_id];
        } else {
            $output .= 'Failed to save rubric: ' . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $output .= 'Invalid request method.';
}

// Fetch unexpected output and check if empty
$output .= ob_get_clean();
if (!empty($output)) {
    echo json_encode(['status' => 'error', 'message' => 'Unexpected output: ' . $output]);
} else {
    // Only echo the response if there's no unexpected output
    if (isset($response)) {
        echo json_encode($response);
    }
}
?>
