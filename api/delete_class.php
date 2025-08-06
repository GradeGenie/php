<?php
header('Content-Type: application/json');

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required.']);
        exit;
    }

    // Delete the class
    $stmt = $conn->prepare('DELETE FROM classes WHERE cid = ?');
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Class deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete class.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
