<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

// Set response header to JSON
header('Content-Type: application/json');


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check connection
    if ($conn->connect_error) {
        $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    } else {
        // Prepare and execute query
        $stmt = $conn->prepare("SELECT name, email FROM users WHERE uid = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch user information
        if ($result->num_rows > 0) {
            $user_info = $result->fetch_assoc();
            $response['status'] = 'success';
            $response['user_info'] = $user_info;
        } else {
            $response['message'] = 'User not found';
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    }
} else {
    $response = ['status' => 'error', 'message' => 'User not logged in'];
}

// Return the response as JSON
echo json_encode($response);
?>
