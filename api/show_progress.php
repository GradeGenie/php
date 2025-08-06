<?php
require 'config.php';

header('Content-Type: application/json');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$query = "SELECT assignment_id, assignment_title, progress, status FROM grading_progress";
$result = $conn->query($query);

$assignments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $assignments]);

$conn->close();
?>
