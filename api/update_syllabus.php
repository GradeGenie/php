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
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Check if required fields are provided
if (isset($_POST['syllabus_id']) && isset($_POST['title']) && isset($_POST['content'])) {
    $syllabus_id = $_POST['syllabus_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $course_name = isset($_POST['course_name']) ? $_POST['course_name'] : '';
    $academic_level = isset($_POST['academic_level']) ? $_POST['academic_level'] : '';
    $updated_at = date('Y-m-d H:i:s');
    
    // First check if the user has permission to edit this syllabus
    $check_stmt = $conn->prepare("SELECT user_id FROM syllabi WHERE id = ?");
    $check_stmt->bind_param("i", $syllabus_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Syllabus not found']);
        exit;
    }
    
    $syllabus_owner = $check_result->fetch_assoc()['user_id'];
    
    // Only allow the owner or admin to update
    if ($user_id != $syllabus_owner && !$is_admin) {
        echo json_encode(['status' => 'error', 'message' => 'You do not have permission to edit this syllabus']);
        exit;
    }
    
    // Prepare SQL statement to update syllabus
    $stmt = $conn->prepare("UPDATE syllabi SET title = ?, content = ?, course_name = ?, academic_level = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $content, $course_name, $academic_level, $updated_at, $syllabus_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Syllabus updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
}
?>
