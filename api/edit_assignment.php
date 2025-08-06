<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'c.php'; // Database connection
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? null;
    $details = $_POST['details'] ?? null;
    $instructions = $_POST['instructions'] ?? null;
    $rubric = $_POST['rubric'] ?? null;
    $style = $_POST['style'] ?? null;
    $extraDetails = $_POST['extra_details'] ?? null;

    if (!$assignmentId || !$name) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
        exit();
    }

    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE assignments SET name = ?, details = ?, instructions = ?, rubric = ?, style = ?, extra_details = ? WHERE aid = ?");
    $stmt->bind_param("sssisii", $name, $details, $instructions, $rubric, $style, $extraDetails, $assignmentId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update assignment: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
