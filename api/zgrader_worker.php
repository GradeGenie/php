<?php
require '../vendor/autoload.php'; // Ensure Pheanstalk is installed and autoloaded
require 'c.php';
require_once __DIR__ . '/../load_env.php';

use PhpOffice\PhpWord\IOFactory; // For reading DOCX files
use Smalot\PdfParser\Parser; // For reading PDF files
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

$logFile = 'log/worker.log';

// Ensure the log file exists
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0666);
    echo "Created log file.<br>";
}

// Function to log messages to a file
function log_message($message) {
    global $logFile;
    echo $message . "<br>"; // Temporary debug output
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
}

// Function to read DOCX files
function readDocx($filePath) {
    $phpWord = IOFactory::load($filePath);
    $text = '';
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if (method_exists($element, 'getText')) {
                $text .= $element->getText() . "\n";
            }
        }
    }
    return $text;
}

// Function to read PDF files
function readPdf($filePath) {
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    return $pdf->getText();
}

// Function to send content to GPT-4
function sendToGPT($content) {
    $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key


    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an experienced, accurate, objective, consistent educator evaluating student assignments. 
                Provide grades and constructive feedback.',
            ],
            [
                'role' => 'user',
                'content' => "Review the student's work based on the provided rubric and assignment details. 
                For each criterion, assign sub-scores in score/max score format, multiply by % weight, calculate the total score out of 100 & provide a final grade (A-F) based on this scale: A >= 90, B >= 80, C >= 70, D >= 60, F < 60.
                Use the rubric to justify the scores with clear evidence from the assignment. Provide detailed overall feedback highlighting strengths, improvement areas & specific action items.
                Content to be graded:\n\n$content",
            ],
        ],
        'max_tokens' => 2500,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        log_message('Error:' . curl_error($ch));
        return null;
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        log_message("HTTP Error: " . $http_status . "\nResponse: " . $result);
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? null;
}

// Function to clean the JSON response from GPT-4
function cleanJsonResponse($jsonResponse) {
    // Remove ```json and ``` from the response if they exist
    $jsonResponse = str_replace(["```json", "```"], "", $jsonResponse);
    return trim($jsonResponse);
}

log_message("Worker started");

try {
    // Connect to Beanstalkd server
    $pheanstalk = Pheanstalk::create('127.0.0.1');
    $tubeName = new TubeName('grading'); // Create a TubeName object

    log_message("Connected to Beanstalkd server.");

    // Watch the specified tube
    $pheanstalk->watch($tubeName);

    // Process all available jobs from the 'grading' tube
    while ($job = $pheanstalk->reserveWithTimeout(0)) { // Reserve with timeout to exit if no jobs are available
        $data = json_decode($job->getData(), true);
        $sid = $data['sid'];

        log_message("Processing job for submission ID: $sid");

        // Database connection
        $conn = new mysqli($host, $username, $password, $database);
        if ($conn->connect_error) {
            log_message('Database connection failed: ' . $conn->connect_error);
            $pheanstalk->delete($job);
            continue;
        }

        // Fetch submission details
        $sql = "SELECT fileName, aid FROM submissions WHERE sid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $stmt->bind_result($fileName, $assignmentId);
        $stmt->fetch();
        $stmt->close();

        // Extract file contents
        $fileContent = '';
        $filePath = '../uploads/assignments/' . basename($fileName);
        $fileType = mime_content_type($filePath);

        if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $fileContent = readDocx($filePath);
        } elseif ($fileType === 'application/pdf') {
            $fileContent = readPdf($filePath);
        } else {
            log_message("Unsupported file type: $fileType");
            $pheanstalk->delete($job);
            continue;
        }

        // Fetch assignment and rubric details
        $sql = "SELECT 
                    a.name AS assignment_name, 
                    a.details, 
                    a.instructions, 
                    a.style, 
                    a.scoring, 
                    a.extra_details, 
                    r.content AS rubric_content, 
                    r.level, 
                    r.name AS rubric_name, 
                    r.title, 
                    r.description 
                FROM assignments a 
                JOIN rubrics r ON a.rubric = r.rid 
                WHERE a.aid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $stmt->bind_result($assignmentName, $details, $instructions, $style, $scoring, $extraDetails, $rubricContent, $level, $rubricName, $title, $description);
        $stmt->fetch();
        $stmt->close();

        // Generate OpenAI prompt
        $prompt = "Assignment Details:\n";
        $prompt .= "Assignment Name: $assignmentName\n";
        $prompt .= "Assignment Details: $details\n";
        $prompt .= "Assignment Grading Instructions: $instructions\n";
        $prompt .= "Feedback Style: $style\n";
        $prompt .= "Scoring Style: $scoring\n";
        $prompt .= "Extra Grading Instructions/Details: $extraDetails\n";
        $prompt .= "\nRubric Details:\n";
        $prompt .= "Rubric Title: $title\n";
        $prompt .= "Rubric: $rubricContent\n";
        $prompt .= "Assignment/Grading Level: $level\n";
        $prompt .= "Rubric Name: $rubricName\n";
        $prompt .= "Rubric Description: $description\n";
        $prompt .= "\nStudent Submission:\n";
        $prompt .= $fileContent;
        $prompt .= "\nReturn your feedback in a JSON object like this:\n";
        $prompt .= json_encode([
            "grade" => "A",
            "score" => "90",
            "comments" => "longform feedback here, use HTML for formatting & HTML double line breaks for new lines"
        ], JSON_PRETTY_PRINT);
        $prompt .= "\nONLY return the grade, score, and comments in the JSON object.";

        log_message("Generated prompt for OpenAI.");

        // Send the prompt to OpenAI
        $openaiResponse = sendToGPT($prompt);
        if ($openaiResponse === null) {
            log_message('Error processing OpenAI response.');
            $pheanstalk->delete($job);
            continue;
        }

        // Clean the JSON response
        $cleanedResponse = cleanJsonResponse($openaiResponse);
        log_message("Cleaned JSON response: " . $cleanedResponse);

        // Parse the OpenAI response
        $parsedResponse = json_decode($cleanedResponse, true);
        $grade = $parsedResponse['grade'] ?? '';
        $score = $parsedResponse['score'] ?? '0/100';
        $comments = $parsedResponse['comments'] ?? '';

        log_message("Parsed response - Grade: $grade, Score: $score, Comments: $comments");

        // Update the submission in the database
        $sql = "UPDATE submissions SET status = 1, grade = ?, score = ?, comments = ? WHERE sid = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisi", $grade, $score, $comments, $sid);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            log_message("Submission updated successfully for SID: $sid");
        } else {
            log_message("Failed to update submission for SID: $sid");
        }
        $stmt->close();

        $conn->close();

        // Delete the job from the queue
        $pheanstalk->delete($job);
    }
} catch (Exception $e) {
    log_message('Error encountered: ' . $e->getMessage());
}

log_message("Worker finished");

echo "Worker finished.<br>";
?>
