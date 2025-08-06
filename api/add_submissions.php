<?php
header('Content-Type: application/json');
session_start();

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['id'];
    $studentName = $_POST['studentName'];

    if (empty($assignmentId) || empty($studentName) || empty($grade) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO submissions (studentName, grade, score, comments, aid) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iissss', $assignmentId, $ownerId, $studentName, $grade);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Submission added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add submission. Error: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
