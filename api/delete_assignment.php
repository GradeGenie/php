<?php
header('Content-Type: application/json');

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
        exit;
    }

    // Delete the assignment
    $stmt = $conn->prepare('DELETE FROM assignments WHERE aid = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete assignment.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
