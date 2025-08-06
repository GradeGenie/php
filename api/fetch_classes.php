<?php
session_start();
header('Content-Type: application/json');

$response = array('success' => false, 'classes' => array());

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

// Log user ID for debugging
error_log('Fetching classes for user ID: ' . $user_id);

// Include database connection with detailed error logging
require 'c.php';

// Check connection directly
if ($conn->connect_error) {
    error_log('Database connection error in fetch_classes.php: ' . $conn->connect_error);
    $response['message'] = 'Database connection error: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// Log successful connection
error_log('Database connection successful in fetch_classes.php');

// Check if connection is valid before proceeding
if ($conn === null || isset($conn->connect_error) && $conn->connect_error) {
    error_log('Invalid database connection in fetch_classes.php');
    $response['message'] = 'Database connection error. Please try again later.';
    echo json_encode($response);
    exit();
}

try {
    // Modify the SQL query to order by the creation timestamp in descending order
    $sql = "SELECT cid, name, level, subject FROM classes WHERE owner = ? ORDER BY created_on DESC";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Log successful query
    error_log('Successfully executed query in fetch_classes.php');
} catch (Exception $e) {
    error_log('SQL error in fetch_classes.php: ' . $e->getMessage());
    $response['message'] = 'Error fetching classes. Please try again later.';
    echo json_encode($response);
    exit();
}

try {
    // Fetch all classes
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $response['classes'][] = $row;
        }
        
        // Set success flag
        $response['success'] = true;
        error_log('Successfully fetched ' . count($response['classes']) . ' classes for user ' . $user_id);
    } else {
        error_log('No result set available in fetch_classes.php');
        $response['message'] = 'Error retrieving classes data';
    }
    
    // Close statement and connection
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    
    if (isset($conn) && $conn) {
        $conn->close();
    }
} catch (Exception $e) {
    error_log('Error in fetch_classes.php final section: ' . $e->getMessage());
    $response['message'] = 'An unexpected error occurred';
}

// Return the response
echo json_encode($response);
?>
