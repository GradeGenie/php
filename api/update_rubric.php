<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'c.php'; // Ensure this file contains the correct database credentials

// Initialize database connection
$conn = new mysqli($host, $username, $password, $database);

// Check for a connection error
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Make sure we're dealing with a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'HTTP method not allowed.']);
    exit;
}

// Debugging: output received POST data
error_log('Received POST data: ' . print_r($_POST, true));

// Sanitize and validate input
$rubricId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$title = $_POST['title']; // Directly using POST data for HTML content
$description = $_POST['description'];
$level = $_POST['level'];
$subject = $_POST['subject'];
$assignment_type = $_POST['assignment_type'];
$content = $_POST['content']; // No sanitization to preserve HTML
$owner = $_SESSION['user_id'] ?? null;

if (!$owner) {
    echo json_encode(['success' => false, 'message' => 'User must be logged in to update a rubric.']);
    exit;
}

// Ensure all required fields are provided
if (!$rubricId || !$title || !$description || !$level || !$subject || !$assignment_type || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields. Please provide all required information.']);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("UPDATE rubrics SET title=?, description=?, level=?, subject=?, assignment_type=?, content=? WHERE rid=? AND owner=?");
$stmt->bind_param('ssssssii', $title, $description, $level, $subject, $assignment_type, $content, $rubricId, $owner);

// Execute the statement and handle errors
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Rubric updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update rubric: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
