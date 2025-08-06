<?php
require 'c.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to add new columns for trial tracking
$sql = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS subscription_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS plan_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS subscription_status VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS trial_end_date DATETIME NULL,
ADD COLUMN IF NOT EXISTS trial_ending TINYINT(1) DEFAULT 0;
";

if ($conn->multi_query($sql)) {
    echo "Database updated successfully to support free trial tracking.";
} else {
    echo "Error updating database: " . $conn->error;
}

$conn->close();
?>
