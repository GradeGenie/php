<?php
header('Content-Type: application/json');
session_start(); // Make sure session is started

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = $_POST['classOption'];
    $assignmentName = $_POST['assignmentTitle'];
    $gradingInstructions = $_POST['gradingInstructions'];
    $rubricId = $_POST['rubricOption'];
    $feedbackStyle = $_POST['gradingStyle'];
    $details = $_POST['assignmentInstructions'];
    $ownerId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session

    if (empty($classId) || empty($assignmentName) || empty($feedbackStyle) || empty($rubricId)) {
        echo json_encode(['success' => false, 'message' => 'Check Required Fields: ' .
            'Class ID: ' . (empty($classId) ? 'Missing' : 'Filled') .
            ', Assignment Name: ' . (empty($assignmentName) ? 'Missing' : 'Filled') .
            ', Feedback Style: ' . (empty($feedbackStyle) ? 'Missing' : 'Filled') .
            ', Rubric ID: ' . (empty($rubricId) ? 'Missing' : 'Filled')]);
        exit;
    }
    

    // Insert into assignments table
    $stmt = $conn->prepare('INSERT INTO assignments (name, details, instructions, rubric, style, class, owner) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssissi', $assignmentName, $details, $gradingInstructions, $rubricId, $feedbackStyle, $classId, $ownerId);

    if ($stmt->execute()) {
        $assignmentId = $stmt->insert_id;
        echo json_encode(['success' => true, 'message' => 'Assignment created successfully.', 'assignmentId' => $assignmentId]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create assignment. Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
