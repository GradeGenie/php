<?php
// Required headers
header("Content-Type: text/plain; charset=UTF-8");

// Include database connection
require_once 'c.php';

// Start session
session_start();

// Check if user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo "Unauthorized access. This script can only be run by an administrator.";
    exit();
}

try {
    // Create database connection
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected to database successfully.\n\n";
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        echo "Creating users table...\n";
        
        $sql = "CREATE TABLE users (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL,
            stripe_id VARCHAR(255) NULL,
            subscription_id VARCHAR(255) NULL,
            plan_id VARCHAR(255) NULL,
            plan_name VARCHAR(255) NULL,
            subscription_status VARCHAR(50) NULL DEFAULT NULL,
            billing_period VARCHAR(50) NULL DEFAULT 'monthly',
            trial_ends_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "Users table created successfully.\n";
        } else {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    } else {
        echo "Users table already exists. Checking for required columns...\n";
        
        // Check for trial_ends_at column
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'trial_ends_at'");
        if ($result->num_rows == 0) {
            echo "Adding trial_ends_at column to users table...\n";
            $sql = "ALTER TABLE users ADD COLUMN trial_ends_at DATETIME NULL AFTER subscription_status";
            
            if ($conn->query($sql) === TRUE) {
                echo "Added trial_ends_at column successfully.\n";
            } else {
                throw new Exception("Error adding trial_ends_at column: " . $conn->error);
            }
        } else {
            echo "trial_ends_at column already exists.\n";
        }
        
        // Check for plan_name column
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'plan_name'");
        if ($result->num_rows == 0) {
            echo "Adding plan_name column to users table...\n";
            $sql = "ALTER TABLE users ADD COLUMN plan_name VARCHAR(255) NULL AFTER plan_id";
            
            if ($conn->query($sql) === TRUE) {
                echo "Added plan_name column successfully.\n";
            } else {
                throw new Exception("Error adding plan_name column: " . $conn->error);
            }
        } else {
            echo "plan_name column already exists.\n";
        }
        
        // Check for billing_period column
        $result = $conn->query("SHOW COLUMNS FROM users LIKE 'billing_period'");
        if ($result->num_rows == 0) {
            echo "Adding billing_period column to users table...\n";
            $sql = "ALTER TABLE users ADD COLUMN billing_period VARCHAR(50) NULL DEFAULT 'monthly' AFTER subscription_status";
            
            if ($conn->query($sql) === TRUE) {
                echo "Added billing_period column successfully.\n";
            } else {
                throw new Exception("Error adding billing_period column: " . $conn->error);
            }
        } else {
            echo "billing_period column already exists.\n";
        }
    }
    
    // Check if sales_inquiries table exists
    $result = $conn->query("SHOW TABLES LIKE 'sales_inquiries'");
    if ($result->num_rows == 0) {
        echo "Creating sales_inquiries table...\n";
        
        $sql = "CREATE TABLE sales_inquiries (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            organization VARCHAR(255) NOT NULL,
            seats INT(11) NOT NULL DEFAULT 3,
            message TEXT NULL,
            plan_type VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'new',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (id)
        )";
        
        if ($conn->query($sql) === TRUE) {
            echo "sales_inquiries table created successfully.\n";
        } else {
            throw new Exception("Error creating sales_inquiries table: " . $conn->error);
        }
    } else {
        echo "sales_inquiries table already exists.\n";
    }
    
    echo "\nDatabase update completed successfully!";
    
    $conn->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
