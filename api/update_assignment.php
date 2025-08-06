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
    $assignmentId = $_POST['id'];
    $assignmentName = $_POST['assignmentName'];
    $gradingInstructions = $_POST['gradingInstructions'];
    $rubric = $_POST['rubric'];
    $feedbackStyle = $_POST['feedbackStyle'];
    $extraInstructions = $_POST['extraInstructions'];
    $ownerId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session

    if (empty($assignmentId) || empty($assignmentName) || empty($feedbackStyle)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID, name, and feedback style are required.']);
        exit;
    }

    // Handle rubric file upload
    $rubricFile = NULL;
    if (!empty($_FILES['uploadRubric']['name'])) {
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["uploadRubric"]["name"]);
        if (move_uploaded_file($_FILES["uploadRubric"]["tmp_name"], $targetFile)) {
            $rubricFile = $targetFile;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload rubric file.']);
            exit;
        }
    }

    $stmt = $conn->prepare('UPDATE assignments SET name = ?, details = ?, instructions = ?, rubric = ?, style = ?, extra_details = ? WHERE aid = ? AND owner = ?');
    $stmt->bind_param('sssissii', $assignmentName, $gradingInstructions, $rubric, $feedbackStyle, $extraInstructions, $assignmentId, $ownerId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update assignment. Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
