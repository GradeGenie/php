<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Include necessary files and start session if needed
include 'c.php';
session_start();

$response = ['success' => false, 'submissions' => []];

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Assignment ID is missing.');
    }

    $assignmentId = $_GET['id'];
    
    // Query to get submission status and comments
    $query = "SELECT sid, status, comments FROM submissions WHERE aid = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $assignmentId);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['submissions'][$row['sid']] = [
            'status' => $row['status'] == 1 ? 'Graded' : 'Pending Grading',
            'comments' => $row['comments']
        ];
    }

    $response['success'] = true;

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);