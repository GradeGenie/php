<?php
header('Content-Type: application/json');

require '../vendor/autoload.php'; // Ensure Pheanstalk is installed and autoloaded

use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

$response = array();

try {
    $response['steps'][] = 'Creating Pheanstalk connection';
    $pheanstalk = Pheanstalk::create('127.0.0.1'); // Connect to Beanstalkd server

    $response['steps'][] = 'Creating TubeName object';
    $tubeName = new TubeName('grading'); // Create a TubeName object

    $response['steps'][] = 'Using grading tube';
    $pheanstalk->useTube($tubeName);

    $response['steps'][] = 'Pushing job to grading tube';
    $pheanstalk->put(json_encode(['test' => 'test']));

    $response['success'] = true;
    $response['message'] = 'Pushed job to Beanstalkd queue successfully';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    $response['trace'] = $e->getTraceAsString();
    $response['steps'][] = 'Error encountered: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
