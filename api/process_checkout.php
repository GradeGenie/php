<?php
session_start();
require 'db_connection.php'; // Ensure you have this file to handle the database connection.

if (!isset($_SESSION['user_id']) || empty($_GET['plan'])) {
    // Redirect user back to login or upgrade page as needed
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$planType = $_GET['plan'];
$activeSub = ($planType === 'yearly') ? 1 : 0; // Assuming active_sub: 1 is active

// Simulate payment processing here
// On successful payment, update the user's subscription status
$query = "UPDATE users SET active_sub = ? WHERE uid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $activeSub, $userId);
$result = $stmt->execute();

if ($result) {
    echo "Subscription updated successfully. Redirecting to home...";
    header("Refresh: 2; url=index.php");
} else {
    echo "Failed to update subscription.";
}

$stmt->close();
?>
