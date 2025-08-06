<?php
require 'config.php'; // Include your configuration file
require_once __DIR__ . '/../load_env.php';
// Set response header to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['assignments']) && isset($_FILES['rubric'])) {
        $assignments = $_FILES['assignments'];
        $rubric = $_FILES['rubric'];

        // Process the assignments and rubric files
        $assignmentsContent = file_get_contents($assignments['tmp_name']);
        $rubricContent = file_get_contents($rubric['tmp_name']);

        // OpenAI API configuration
        $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key
        $openaiApiUrl = 'https://api.openai.com/v1/engines/davinci-codex/completions';

        // Prepare the prompt for grading
        $prompt = "Grade the following assignments based on the provided rubric:\n\nRubric:\n{$rubricContent}\n\nAssignments:\n{$assignmentsContent}";

        // Prepare the payload for the OpenAI API
        $data = [
            'prompt' => $prompt,
            'max_tokens' => 1500,
            'temperature' => 0.7,
        ];

        // Make the API request
        $ch = curl_init($openaiApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $openaiApiKey,
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            echo json_encode(['status' => 'error', 'message' => $error_msg]);
            exit;
        }
        curl_close($ch);

        $apiResponse = json_decode($response, true);
        if (isset($apiResponse['choices'][0]['text'])) {
            $gradingResults = $apiResponse['choices'][0]['text'];
            echo json_encode(['status' => 'success', 'gradingResults' => $gradingResults]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to get a valid response from OpenAI API']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Assignments or rubric file missing']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
