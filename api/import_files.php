<?php
header('Content-Type: application/json');
session_start();

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['id'];
    $ownerId = $_SESSION['user_id'];

    if (empty($assignmentId)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
        exit;
    }

    foreach ($_FILES['importFiles']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['importFiles']['name'][$key];
        $file_tmp = $_FILES['importFiles']['tmp_name'][$key];
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($file_name);

        if (move_uploaded_file($file_tmp, $targetFile)) {
            $stmt = $conn->prepare('INSERT INTO submissions (aid, fileName, studentName) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $assignmentId, $targetFile, $ownerId);
            $stmt->execute();
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file: ' . $file_name]);
            exit;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Files imported successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
