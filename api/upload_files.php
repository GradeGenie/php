<?php
header('Content-Type: application/json');
session_start();

// Database connection
require 'c.php';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignmentId = $_POST['assignmentId'] ?? null;
$ownerId = $_SESSION['user_id'];

if (empty($assignmentId)) {
    echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
    exit;
}

$filenames = [];
$timestamp = date('dmY_His');

foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
    $originalName = basename($_FILES['files']['name'][$key]);
    $fileName = pathinfo($originalName, PATHINFO_FILENAME) . "_{$timestamp}." . pathinfo($originalName, PATHINFO_EXTENSION);
    $targetFilePath = "assignments/{$fileName}";

    if (move_uploaded_file($tmpName, $targetFilePath)) {
        $filenames[] = $fileName;
    }
}

if (!empty($filenames)) {
    // Insert all filenames into the database in one query
    $values = [];
    foreach ($filenames as $fileName) {
        $values[] = "('{$fileName}', {$assignmentId}, 0, '', '', '', '', {$ownerId})";
    }
    $query = "INSERT INTO submissions (fileName, aid, status, grade, score, comments, studentName, ownerId) VALUES " . implode(', ', $values);
    $conn->query($query);

    echo json_encode(['success' => true, 'message' => 'Files uploaded successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No files were uploaded.']);
}

$conn->close();
?>
