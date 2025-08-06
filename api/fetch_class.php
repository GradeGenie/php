<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

$response = array('success' => false, 'class' => null, 'assignments' => array());

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'Class ID is required.';
    echo json_encode($response);
    exit();
}

$classId = $_GET['id'];
$user_id = $_SESSION['user_id'];

require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Fetch class information
$stmt = $conn->prepare("SELECT name, level, subject FROM classes WHERE cid = ? AND owner = ?");
$stmt->bind_param("ii", $classId, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $response['class'] = $row;
    $response['success'] = true;
} else {
    $response['message'] = 'Class not found or access denied.';
}

$stmt->close();

// Fetch assignments for the class
$stmt = $conn->prepare("SELECT aid, name, created_on FROM assignments WHERE class = ?");
$stmt->bind_param("i", $classId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $row['submissions'] = rand(0, 10); // Placeholder for submissions count
    $response['assignments'][] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
