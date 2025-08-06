<?php
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $assignmentId = $_GET['assignmentId'];
    $userId = $_SESSION['user_id'];

    if (empty($assignmentId)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
        exit();
    }

    $stmt = $conn->prepare('SELECT instructions FROM assignments WHERE aid = ? AND owner = ?');
    $stmt->bind_param('ii', $assignmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();
        echo json_encode(['success' => true, 'instructions' => $assignment['instructions']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment not found or access denied.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
