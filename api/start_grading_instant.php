<?php
session_start();
header('Content-Type: application/json');

require '../vendor/autoload.php'; // Ensure Pheanstalk is installed and autoloaded

use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

$response = array();
$response['steps'] = array();

try {
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User is not logged in.');
    }
    $response['steps'][] = 'User is logged in';

    // Ensure the uploads directory exists
    $uploadDir = '../uploads/assignments/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Failed to create upload directory.');
        }
    }
    $response['steps'][] = 'Upload directory is set';

    // Check if files were uploaded
    if (isset($_FILES['submissions'])) {
        $totalFiles = count($_FILES['submissions']['name']);
        $uploadedFiles = array();
        $response['steps'][] = 'Files detected for upload';

        require 'c.php';
        $conn = new mysqli($host, $username, $password, $database);

        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        $response['steps'][] = 'Database connection established';

        $pheanstalk = Pheanstalk::create('127.0.0.1'); // Connect to Beanstalkd server
        $tubeName = new TubeName('grading'); // Create a TubeName object
        $response['steps'][] = 'Connected to Beanstalkd server and created TubeName object';

        $assignmentId = $_POST['assignmentOption'];

        for ($i = 0; $i < $totalFiles; $i++) {
            // Original filename
            $originalFileName = basename($_FILES['submissions']['name'][$i]);
            // Replace spaces with underscores
            $safeFileName = str_replace(' ', '_', $originalFileName);
            // Append current date and time
            $dateTime = date('mdY_Hi');
            $newFileName = pathinfo($safeFileName, PATHINFO_FILENAME) . '_' . $dateTime . '.' . pathinfo($safeFileName, PATHINFO_EXTENSION);
            $targetFilePath = $uploadDir . $newFileName;

            // Debugging: Log file details to response
            $response['debug'][] = "Uploading file: " . $originalFileName . " as " . $newFileName . " to " . $targetFilePath;
            $response['debug'][] = "Temporary file path: " . $_FILES['submissions']['tmp_name'][$i];
            $response['debug'][] = "Upload error code: " . $_FILES['submissions']['error'][$i];

            // Check if the file has been uploaded without errors
            if ($_FILES['submissions']['error'][$i] === UPLOAD_ERR_OK) {
                $response['steps'][] = 'File ' . $originalFileName . ' uploaded successfully';

                // Upload the file
                if (move_uploaded_file($_FILES['submissions']['tmp_name'][$i], $targetFilePath)) {
                    $uploadedFiles[] = $newFileName;
                    // $response['steps'][] = 'File ' . $newFileName moved to target directory';

                    // Prepare file URL
                    $fileUrl = 'https://app.getgradegenie.com/uploads/assignments/' . $newFileName;

                    // Debugging SQL statement preparation
                    $response['steps'][] = 'Preparing SQL statement';
                    $sql = "INSERT INTO submissions (fileName, studentName, status, grade, score, comments, aid) VALUES (?, '', 0, '', 0, '', ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        $stmt->bind_param("si", $fileUrl, $assignmentId);
                        $stmt->execute();
                        $sid = $stmt->insert_id; // Get the inserted submission ID
                        $stmt->close();
                        $response['steps'][] = 'File info inserted into database with SID: ' . $sid;

                        // Push job to Beanstalkd queue
                        $response['steps'][] = 'Pushing job to Beanstalkd queue';
                        $pheanstalk->useTube($tubeName);
                        $pheanstalk->put(json_encode(['sid' => $sid]));
                        $response['steps'][] = 'Job pushed to Beanstalkd queue for SID: ' . $sid;

                        // Run worker.php to process the job
                        shell_exec('php worker.php > log/worker.log 2>&1 &');
                    } else {
                        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
                    }
                } else {
                    throw new Exception('Failed to upload file: ' . $originalFileName . '. Possible reasons could be file permissions or an invalid path.');
                }
            } else {
                throw new Exception('Error uploading file: ' . $originalFileName . '. Error code: ' . $_FILES['submissions']['error'][$i]);
            }
        }

        $conn->close();
        $response['steps'][] = 'Database connection closed';

        // Return success response
        $response['success'] = true;
        $response['message'] = 'Files uploaded and saved successfully.';
        $response['uploadedFiles'] = $uploadedFiles;
    } else {
        throw new Exception('No files were uploaded.');
    }
} catch (Exception $e) {
    // Output the error message to the response
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['file'] = $e->getFile();
    $response['line'] = $e->getLine();
    $response['trace'] = $e->getTraceAsString();
    $response['steps'][] = 'Error encountered: ' . $e->getMessage();
}

// Print the response and exit
echo json_encode($response);
exit;
?>
