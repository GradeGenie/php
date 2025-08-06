<?php
session_start();
header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'classId' => null);

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}

if (!isset($_POST['className']) || empty($_POST['className']) ||
    !isset($_POST['level']) || empty($_POST['level']) ||
    !isset($_POST['subject']) || empty($_POST['subject'])) {
    $response['message'] = 'All fields are required.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$className = $_POST['className'];
$level = $_POST['level'];
$subject = $_POST['subject'];

// Include the database connection file
require 'c.php';

// Check if the connection is valid
if (!isset($conn) || (isset($conn->connect_error) && $conn->connect_error)) {
    error_log('Database connection failed in create_class.php: ' . ($conn->connect_error ?? 'Connection not established'));
    $response['message'] = 'Database connection error. Please try again later.';
    echo json_encode($response);
    exit();
}

$stmt = $conn->prepare("INSERT INTO classes (name, owner, level, subject) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siss", $className, $user_id, $level, $subject);

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Class created successfully.';
    $response['classId'] = $stmt->insert_id;
} else {
    $response['message'] = 'Failed to create class: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
