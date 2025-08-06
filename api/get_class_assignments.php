<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'c.php';

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if (!$class_id) {
    echo json_encode(['error' => 'Class ID is required']);
    exit;
}

try {
    if (!isset($conn)) {
        throw new Exception('Database connection not established');
    }

    // First verify the user owns this class
    $stmt = $conn->prepare("SELECT cid FROM classes WHERE cid = ? AND owner = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ii", $class_id, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get result: ' . $stmt->error);
    }
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Class not found or access denied']);
        exit;
    }

    // Now get the assignments
    $stmt = $conn->prepare("SELECT aid as id, name FROM assignments WHERE class = ? ORDER BY created_at DESC");
    if (!$stmt) {
        throw new Exception('Failed to prepare assignments statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $class_id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute assignments statement: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception('Failed to get assignments result: ' . $stmt->error);
    }
    
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    echo json_encode($assignments);
} catch (Exception $e) {
    error_log('Error in get_class_assignments.php: ' . $e->getMessage());
    echo json_encode(['error' => 'Error fetching assignments: ' . $e->getMessage()]);
}
?>
