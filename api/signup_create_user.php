<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start(); // Ensure session is started

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $stripeID = $_POST['stripeID'];

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($stripeID)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    // Insert user into the database
    $stmt = $conn->prepare('INSERT INTO users (email, password, name, stripeID) VALUES (?, ?, ?, ?)');
    $fullName = $firstName . ' ' . $lastName;
    $stmt->bind_param('ssss', $email, $password, $fullName, $stripeID);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id; // Store user ID in session
        $_SESSION['user_first_name'] = $fullName; // Store user first name in session
        echo json_encode(['success' => true, 'message' => 'User created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create user. Error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
