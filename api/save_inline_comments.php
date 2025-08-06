<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// Include database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

$sql = "
CREATE TABLE IF NOT EXISTS inline_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    highlighted_text TEXT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'inline_comments' created or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Get raw POST data
$input = json_decode(file_get_contents("php://input"), true);

// Validate required fields
if (!isset($input['submissionId']) || !isset($input['highlightedText']) || !isset($input['comment'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

$submissionId = $input['submissionId'];
$highlightedText = $input['highlightedText'];
$comment = $input['comment'];
$createdAt = date('Y-m-d H:i:s');

// Prepare and execute insert
$stmt = $conn->prepare("INSERT INTO inline_comments (submission_id, highlighted_text, comment, created_at) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $submissionId, $highlightedText, $comment, $createdAt);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comment saved.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save comment.']);
}

$stmt->close();
$conn->close();
?>
