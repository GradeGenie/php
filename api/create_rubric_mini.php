<?php
session_start(); // Start the session

// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../load_env.php';

// Initialize the session variable for usage count if it doesn't exist
if (!isset($_SESSION['usage_count'])) {
    $_SESSION['usage_count'] = 0;
}

// Check if the user has exceeded the free usage limit
if ($_SESSION['usage_count'] >= 2) {
    echo json_encode([
        'status' => 'limit_reached',
        'message' => 'You have reached the limit of 2 free rubric generations. Please sign up to continue using the service.',
        'redirect_url' => 'signup.php' // Change this to your actual signup page URL
    ]);
    exit();
}

// Check if the POST variables are set
if (isset($_POST['subject']) && isset($_POST['description']) && isset($_POST['level']) && isset($_POST['style'])) {
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $level = $_POST['level'];
    $style = $_POST['style'];

    $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key


    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a rubric creator. People will give you a subject, description, and style, and you will generate a rubric for them. Please return an HTML table with the rubric. Make it comprehensive, with at least 4 categories and 3 levels of performance for each category. Include weighting for each criteria and quantitative number for each qualitative evaluation level. ONLY return the HTML table, no other text or formatting.'
            ],
            [
                'role' => 'user',
                'content' => "Generate a rubric for the following:\n\nSubject: $subject\nDescription: $description\nStyle: $style\nAcademic Level: $level"
            ]
        ]
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => curl_error($ch)]);
    } else {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            // Increment the usage count
            $_SESSION['usage_count'] += 1;

            echo json_encode(['status' => 'success', 'rubric' => $result['choices'][0]['message']['content']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid response from API.']);
        }
    }

    curl_close($ch);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
}
?>
