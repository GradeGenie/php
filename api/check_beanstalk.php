<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; // Autoload the Pheanstalk library

use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

// Connect to beanstalkd on the default host and port
$pheanstalk = Pheanstalk::create('127.0.0.1');

// List all tubes
$tubes = $pheanstalk->listTubes();
echo "Tubes:\n";
foreach ($tubes as $tube) {
    echo "- " . $tube->value . "\n";
}

// Select a tube (grading tube in this example)
$tubeName = new TubeName('grading');

// Get stats for the selected tube
$tubeStats = $pheanstalk->statsTube($tubeName);

echo "\nTube Statistics for '" . $tubeName->value . "':\n";
echo "Current Jobs Ready: " . $tubeStats->currentJobsReady . "\n";
echo "Current Jobs Reserved: " . $tubeStats->currentJobsReserved . "\n";
echo "Current Jobs Delayed: " . $tubeStats->currentJobsDelayed . "\n";
echo "Current Jobs Buried: " . $tubeStats->currentJobsBuried . "\n";

// Watch the 'grading' tube to peek at jobs
$pheanstalk->watch($tubeName);

// Peek at the next ready job in the 'grading' tube
$job = $pheanstalk->peekReady();

if ($job) {
    echo "\nNext Ready Job in 'grading' tube:\n";
    echo "Job ID: " . $job->getId() . "\n";
    echo "Job Data: " . $job->getData() . "\n";
} else {
    echo "\nNo jobs are ready in the 'grading' tube.\n";
}

// After listing tubes and tube stats, add:
echo "\nAttempting to reserve a job:\n";
try {
    $job = $pheanstalk->reserveWithTimeout(5); // Wait up to 5 seconds for a job
    if ($job) {
        echo "Successfully reserved job ID: " . $job->getId() . "\n";
        echo "Job data: " . $job->getData() . "\n";
        // Release the job back to the queue
        $pheanstalk->release($job);
    } else {
        echo "No job could be reserved within 5 seconds.\n";
    }
} catch (Exception $e) {
    echo "Error reserving job: " . $e->getMessage() . "\n";
}

// Add server stats
echo "\nServer Stats:\n";
$serverStats = $pheanstalk->stats();
echo "Total Jobs: " . $serverStats->totalJobs . "\n";
echo "Jobs Ready: " . $serverStats->currentJobsReady . "\n";
echo "Workers: " . $serverStats->currentWorkers . "\n";
echo "Connections: " . $serverStats->currentConnections . "\n";

?>
