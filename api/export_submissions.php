<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="submissions.csv"');
header('Pragma: no-cache');
header('Expires: 0');

require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignmentId = $_GET['id'];
$ownerId = $_SESSION['user_id'];

$output = fopen('php://output', 'w');
fputcsv($output, array('Student Name', 'Grade', 'Status', 'Review'));

$stmt = $conn->prepare('SELECT student_name, grade, status, review FROM submissions WHERE aid = ? AND uid = ?');
$stmt->bind_param('ii', $assignmentId, $ownerId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
$stmt->close();
$conn->close();
?>
