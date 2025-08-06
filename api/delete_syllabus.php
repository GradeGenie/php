<?php
// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'c.php';

// Start session to get user ID
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if syllabus ID is provided
if (isset($_POST['syllabus_id'])) {
    $syllabus_id = $_POST['syllabus_id'];
    
    // Prepare SQL statement to delete syllabus (only if it belongs to the current user)
    $stmt = $conn->prepare("DELETE FROM syllabi WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $syllabus_id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Syllabus deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Syllabus not found or you do not have permission to delete it']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Syllabus ID is required']);
}
?>
