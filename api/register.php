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
    // Decode the JSON data from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Get the input values
    $firstName = isset($input['firstName']) ? $input['firstName'] : '';
    $lastName = isset($input['lastName']) ? $input['lastName'] : '';
    $email = isset($input['email']) ? $input['email'] : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $confirmPassword = isset($input['confirmPassword']) ? $input['confirmPassword'] : '';

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare('SELECT uid FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists.']);
        exit;
    }

    $stmt->close();

    // Combine first and last names into a single name field
    $name = $firstName . ' ' . $lastName;

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Placeholder for Stripe ID (this will be updated after payment processing)
    $stripeID = NULL;

    // Insert the new user into the database
    $stmt = $conn->prepare('INSERT INTO users (name, email, password, stripeID) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hashedPassword, $stripeID);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful.']);
        $_SESSION['user_id'] = $stmt->insert_id; // Set the session user ID

        // Here you would typically generate a Stripe Checkout session
        // and return the session ID to the client to complete the payment.
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to register. Please try again.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
