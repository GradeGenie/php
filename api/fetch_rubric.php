<?php
header('Content-Type: application/json');
session_start(); // Ensure session is started

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Rubric ID is required.']);
        exit();
    }

    $rubricId = $_GET['id'];
    $userId = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session

    // Fetch single rubric
    $stmt = $conn->prepare('SELECT * FROM rubrics WHERE rid = ? AND owner = ?');
    $stmt->bind_param('ii', $rubricId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $rubric = $result->fetch_assoc();
        echo json_encode(['success' => true, 'rubric' => $rubric]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Rubric not found or access denied.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
