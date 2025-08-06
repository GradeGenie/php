<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $classId = $_GET['classId'];
    $userId = $_SESSION['user_id'];

    if (empty($classId)) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required.']);
        exit();
    }

    // Fetch assignments for the selected class
    $stmt = $conn->prepare('SELECT * FROM assignments WHERE class = ? AND owner = ?');
    $stmt->bind_param('ii', $classId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }

    echo json_encode(['success' => true, 'assignments' => $assignments]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
