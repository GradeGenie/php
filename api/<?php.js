<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

$response = array('success' => false, 'submission' => null);

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'Submission ID is required.';
    echo json_encode($response);
    exit();
}

$submissionId = $_GET['id'];
$user_id = $_SESSION['user_id'];

require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Fetch submission details
$stmt = $conn->prepare("SELECT sid, fileName, studentName, status, grade, score, comments, aid FROM submissions WHERE sid = ?");
$stmt->bind_param("i", $submissionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $response['submission'] = $row;
    $response['success'] = true;
} else {
    $response['message'] = 'Submission not found or access denied.';
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
