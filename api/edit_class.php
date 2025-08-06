<?php
header('Content-Type: application/json');

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $level = $_POST['level'];
    $custom_level = $_POST['custom_level'];
    $subject = $_POST['subject'];
    $custom_subject = $_POST['custom_subject'];

    if (empty($id) || empty($name) || empty($level) || empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    $level = $level === 'Other' ? $custom_level : $level;
    $subject = $subject === 'Other' ? $custom_subject : $subject;

    $stmt = $conn->prepare('UPDATE classes SET name = ?, level = ?, subject = ? WHERE cid = ?');
    $stmt->bind_param('sssi', $name, $level, $subject, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Class details updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update class details.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
