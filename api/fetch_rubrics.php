<?php
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_SESSION['user_id']; // Get the user ID from the session
    $rubricId = isset($_GET['id']) ? $_GET['id'] : null;

    if ($rubricId) {
        // Fetch specific rubric
        $stmt = $conn->prepare('SELECT * FROM rubrics WHERE rid = ? AND owner = ?');
        $stmt->bind_param('ii', $rubricId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $rubric = $result->fetch_assoc();
            echo json_encode(['success' => true, 'rubric' => $rubric]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Rubric not found or access denied.']);
        }

        $stmt->close();
    } else {
        // Fetch all rubrics for the user
        $stmt = $conn->prepare('SELECT * FROM rubrics WHERE owner = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $rubrics = [];
            while ($row = $result->fetch_assoc()) {
                $rubrics[] = $row;
            }
            echo json_encode(['success' => true, 'rubrics' => $rubrics]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No rubrics found.']);
        }

        $stmt->close();
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
