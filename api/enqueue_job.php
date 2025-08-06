<?php
require '../vendor/autoload.php';
require 'c.php'; // Initializes $conn

use Pheanstalk\Pheanstalk;

// Initialize Pheanstalk
$pheanstalk = Pheanstalk::create('127.0.0.1');

// Example variables - these should come from your submission handling logic
$assignmentId = $_POST['assignmentId']; // Or however you obtain this
$sid = $newSubmissionId; // The ID of the new submission record
$fileName = $uploadedFileName; // The name of the uploaded file
$filePath = '../uploads/assignments/' . basename($fileName); // Construct the file path

// Job data with required fields
$jobData = [
    'assignmentId' => $assignmentId,
    'filePath' => $filePath,
    'sid' => $sid,
];

// Encode job data to JSON
$jobDataJson = json_encode($jobData);

// Enqueue the job into the 'grading' tube
$pheanstalk->useTube('grading')->put($jobDataJson);

echo "Job has been enqueued into the 'grading' tube.\n";

if (empty($assignmentId) || empty($filePath) || empty($sid)) {
    // Handle the error appropriately, e.g., log it and do not enqueue
    error_log("Invalid job data. Missing 'assignmentId', 'filePath', or 'sid'.");
    exit("Failed to enqueue job due to missing data.\n");
}

?>
