<?php
session_start();
header('Content-Type: application/json');
require 'c.php';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionId = $_POST['id']; // Submission ID
    $grade = $_POST['grade'];
    $score = $_POST['score'];
    $comments = $_POST['comments'];

    // Debug: Log received values
    error_log("Submission ID: " . $submissionId);
    error_log("Grade: " . $grade);
    error_log("Score: " . $score);
    error_log("Comments: " . $comments);

    if (!$submissionId) {
        die(json_encode(['success' => false, 'message' => 'Submission ID not set']));
    }

    // Ensure the submission exists
    $stmt = $conn->prepare("SELECT * FROM submissions WHERE sid = ?");
    if (!$stmt) {
        die(json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]));
    }
    $stmt->bind_param('i', $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update statement
        $stmt = $conn->prepare("UPDATE submissions SET grade = ?, score = ?, comments = ?, status = 1 WHERE sid = ?");
        if (!$stmt) {
            die(json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]));
        }
        $stmt->bind_param('sssi', $grade, $score, $comments, $submissionId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Submission updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update submission: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Submission not found.']);
    }
    $result->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
