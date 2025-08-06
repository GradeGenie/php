<?php
// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'c.php';

// Check if class ID is provided
if (!isset($_GET['class_id'])) {
    echo json_encode(['success' => false, 'message' => 'Class ID is required']);
    exit;
}

$class_id = $_GET['class_id'];

// Get the syllabus ID associated with this class
$class_stmt = $conn->prepare("SELECT syllabus_id FROM classes WHERE id = ?");
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();

if ($class_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Class not found']);
    exit;
}

$class_data = $class_result->fetch_assoc();
$syllabus_id = $class_data['syllabus_id'];

if (!$syllabus_id) {
    echo json_encode(['success' => false, 'message' => 'No syllabus attached to this class', 'has_syllabus' => false]);
    exit;
}

// Get the syllabus details
$syllabus_stmt = $conn->prepare("SELECT id, title, content, course_name, academic_level, created_at FROM syllabi WHERE id = ?");
$syllabus_stmt->bind_param("i", $syllabus_id);
$syllabus_stmt->execute();
$syllabus_result = $syllabus_stmt->get_result();

if ($syllabus_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Syllabus not found', 'has_syllabus' => false]);
    exit;
}

$syllabus = $syllabus_result->fetch_assoc();
echo json_encode(['success' => true, 'syllabus' => $syllabus, 'has_syllabus' => true]);

$class_stmt->close();
$syllabus_stmt->close();
?>
