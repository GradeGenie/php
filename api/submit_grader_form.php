<?php
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

$response = ['success' => false];

// Helper function to return JSON response and exit
function return_json($response) {
    echo json_encode($response);
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in.';
    return_json($response);
}

// Error logs
error_log(print_r($_POST, true));
error_log(print_r($_FILES, true));

// Check if necessary fields are set
if (empty($_POST['classOption']) || empty($_POST['assignmentOption']) || empty($_POST['rubricOption']) || empty($_FILES['submissions'])) {
    $response['message'] = 'Missing required fields.';
    return_json($response);
}

$classId = $_POST['classOption'];
$assignmentId = $_POST['assignmentOption'];
$rubricId = $_POST['rubricOption'];
$gradingInstructions = $_POST['gradingInstructions'] ?? '';
$gradingStyle = $_POST['gradingStyle'] ?? '';
$userId = $_SESSION['user_id'];

require 'c.php';
require __DIR__ . '/../vendor/autoload.php'; // Adjust this path if needed
use Pheanstalk\Pheanstalk;

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    $response['message'] = 'Connection failed: ' . $conn->connect_error;
    return_json($response);
}

$timestamp = date('Ymd_His');
$targetDir = __DIR__ . '/../assignments';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$uploadedFiles = [];

foreach ($_FILES['submissions']['tmp_name'] as $key => $tmpName) {
    $originalName = basename($_FILES['submissions']['name'][$key]);
    $fileName = pathinfo($originalName, PATHINFO_FILENAME) . "_{$timestamp}." . pathinfo($originalName, PATHINFO_EXTENSION);
    $targetFilePath = $targetDir . "/{$fileName}";

    if (move_uploaded_file($tmpName, $targetFilePath)) {
        $uploadedFiles[] = $fileName;
    } else {
        $response['message'] = 'Failed to move uploaded file.';
        return_json($response);
    }
}

// Insert filenames into the database
$values = [];
foreach ($uploadedFiles as $fileName) {
    $values[] = "('$fileName', $assignmentId, 0, '', '', '', '', $userId)";
}
$query = "INSERT INTO submissions (fileName, aid, status, grade, score, comments, studentName, ownerId) VALUES " . implode(', ', $values);
if (!$conn->query($query)) {
    $response['message'] = 'Database insertion failed: ' . $conn->error;
    return_json($response);
}

// Add jobs to the queue
$pheanstalk = Pheanstalk::create('127.0.0.1');
foreach ($uploadedFiles as $fileName) {
    $pheanstalk->useTube('grading')->put(json_encode([
        'assignmentId' => $assignmentId,
        'fileName' => $fileName,
        'userId' => $userId,
        'rubricId' => $rubricId,
        'gradingInstructions' => $gradingInstructions,
        'gradingStyle' => $gradingStyle
    ]));
}

$response['success'] = true;
$response['message'] = 'Files uploaded and grading started.';
$conn->close();
return_json($response);
?>
