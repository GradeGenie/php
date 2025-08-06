<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check connectionz
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT uid, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['user_first_name'] = $user['name'];
            $_SESSION['user_email'] = $email;
            header('Location: ../index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid password';
        }
    } else {
        $_SESSION['error'] = 'User not found';
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    header('Location: ../login.php'); // Redirect back to login page
    exit;
}
?>
