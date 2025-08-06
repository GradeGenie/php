<?php
header('Content-Type: application/json');
session_start();

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $firstName = isset($input['firstName']) ? $input['firstName'] : '';
    $lastName = isset($input['lastName']) ? $input['lastName'] : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $confirmPassword = isset($input['confirmPassword']) ? $input['confirmPassword'] : '';

    // Basic validation
    if (empty($firstName) || empty($lastName)) {
        echo json_encode(['success' => false, 'message' => 'First name and last name are required.']);
        exit;
    }

    if (!empty($password) && $password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Combine first and last names into a single name field
    $name = $firstName . ' ' . $lastName;

    // Update the user information in the database
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE users SET name = ?, password = ? WHERE uid = ?');
        $stmt->bind_param('ssi', $name, $hashedPassword, $user_id);
    } else {
        $stmt = $conn->prepare('UPDATE users SET name = ? WHERE uid = ?');
        $stmt->bind_param('si', $name, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
