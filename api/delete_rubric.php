<?php
header('Content-Type: application/json');
session_start(); // Ensure session is started

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['success' => false, 'message' => 'Rubric ID is required.']);
        exit();
    }

    $rubricId = $_POST['id'];
    $userId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session

    // Delete the rubric
    $stmt = $conn->prepare('DELETE FROM rubrics WHERE rid = ? AND owner = ?');
    $stmt->bind_param('ii', $rubricId, $userId);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Rubric deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Rubric not found or you do not have permission to delete this rubric.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete rubric.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
