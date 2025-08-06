<?php
session_start();
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $assignmentId = $_GET['assignmentId'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE aid = ? AND status = 1");
    $stmt->bind_param('i', $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $updated = $row['count'] > 0;

    echo json_encode(['updated' => $updated]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>