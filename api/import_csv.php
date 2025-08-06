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

    if (isset($_FILES['importCSV']) && $_FILES['importCSV']['error'] == 0) {
        $csvFile = fopen($_FILES['importCSV']['tmp_name'], 'r');

        while (($row = fgetcsv($csvFile, 1000, ",")) !== FALSE) {
            $studentName = $row[0];
            $grade = $row[1];
            $status = $row[2];
            $review = $row[3];

            $stmt = $conn->prepare('INSERT INTO submissions (studentName, status, grade, score, comments, aid) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iissss', $assignmentId, $ownerId, $studentName, $grade, $status, $review);
            $stmt->execute();
            $stmt->close();
        }

        fclose($csvFile);
        echo json_encode(['success' => true, 'message' => 'CSV imported successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload CSV file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
