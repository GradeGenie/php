<?php
// api/upload_assignments.php
session_start();
header('Content-Type: application/json');

require '../config.php';  // Adjust the path to your config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['assignments'])) {
        $zipFile = $_FILES['assignments'];

        // Check if the file is a valid ZIP file
        if ($zipFile['type'] !== 'application/zip') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only ZIP files are allowed.']);
            exit;
        }

        // Move the uploaded ZIP file to a specific directory
        $uploadDir = '../assignments/';
        $filePath = $uploadDir . basename($zipFile['name']);

        if (move_uploaded_file($zipFile['tmp_name'], $filePath)) {
            echo json_encode(['status' => 'success', 'message' => 'Assignments uploaded successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload assignments.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
