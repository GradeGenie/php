<?php
// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../load_env.php';

header('Content-Type: application/json');

function sendResponse($status, $message, $data = null) {
    $response = [
        'status' => $status,
        'message' => $message,
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

if (!isset($_POST['image']) || !isset($_POST['transcription'])) {
    sendResponse('error', 'Required fields are missing.');
}

$image_data = $_POST['image'];
$transcription = $_POST['transcription'];

// Log the received image and transcription for debugging
// file_put_contents('debug_image.log', base64_decode($image_data));
// file_put_contents('debug_transcription.log', $transcription);

$apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key

$instructions = "";
// Check if transcription is not empty
if (!empty($transcription)) {
    $instructions = "Please also consider these additional grading instructions or context from the teacher: $transcription";
} 


$payload = json_encode([
    'model' => 'gpt-4o-mini',
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => "Read the image, it will be an assignment, or test, from a student. If it is a written assignment, provide meaningful feedback to improve the student's work, kindly, and provide a grade out of 100% for the work. If it is a math test, tell us whether the student got the marks or not, and if not, provide an explanation of how to reach the correct answer, and the correct answer, then provide a grade out of 100% for the work. Finally, if it is a test, grade it, use any mark indications to calculate the grade, e.g. if it says (3 marks), please consider this, and provide a grade out of 100% for the work. Please address the student directly in your response, e.g. 'Your work' instead of 'The student's work'. $instructions"
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' => "data:image/png;base64,$image_data"
                    ]
                ]
            ]
        ]
    ],
    'max_tokens' => 1000
]);

$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
];

$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
$err = curl_error($ch);
$info = curl_getinfo($ch);

curl_close($ch);

if ($err) {
    sendResponse('error', 'cURL Error: ' . $err);
}

$result = json_decode($response, true);

if ($info['http_code'] != 200) {
    sendResponse('error', 'API Error: ' . $info['http_code'], $result);
}

if (isset($result['choices'][0]['message']['content'])) {
    sendResponse('success', 'API request successful', [
        'content' => $result['choices'][0]['message']['content'],
        'raw_response' => $result
    ]);
} else {
    sendResponse('error', 'Invalid response from API', $result);
}
?>
