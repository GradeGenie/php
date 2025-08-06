<?php
require '../vendor/autoload.php'; // Ensure Pheanstalk is installed and autoloaded
require 'c.php';
require_once __DIR__ . '/../load_env.php';

set_time_limit(300); // Set to 300 seconds (5 minutes) or adjust as needed

use PhpOffice\PhpWord\IOFactory; // For reading DOCX files
use Spatie\PdfToText\Pdf; // For reading PDF files using Spatie
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

$logFile = 'log/worker2.html';

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
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Function to read DOCX files
function readDocx($filePath) {
    log_message("Reading DOCX file: $filePath");
    $phpWord = IOFactory::load($filePath);
    $text = '';
    foreach ($phpWord->getSections() as $section) {
        foreach ($section->getElements() as $element) {
            if (method_exists($element, 'getText')) {
                $text .= $element->getText() . "\n";
            }
        }
    }
    log_message("Finished reading DOCX file.");
    return $text;
}

// Function to read PDF files using Spatie's PDF to Text package
function readPdf($filePath) {
    log_message("Reading PDF file: $filePath");
    try {
        $text = Pdf::getText($filePath);
    } catch (Exception $e) {
        log_message("Error reading PDF file: " . $e->getMessage());
        return '';
    }
    log_message("Finished reading PDF file.");
    return $text;
}

// Function to send content to GPT-4
function sendToGPT($content) {
    log_message("Sending content to GPT-4 API.");
    $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key

    
    $data = [
        'model' => 'gpt-4o', // Fixed model name
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an experienced educator who evaluates assignments with detailed & structured analysis. You provide clear & actionable feedback to help students improve. Use "You" to address the student.'
            ],
            [
                'role' => 'user',
                'content' => $content,
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 280); // 180 seconds (3 minutes)
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        log_message('Error during GPT-4 API call: ' . curl_error($ch));
        return null;
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        log_message("HTTP Error during GPT-4 API call: " . $http_status . "\nResponse: " . $result);
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);
    log_message("Received response from GPT-4 API.");
    return $response['choices'][0]['message']['content'] ?? null;
}

function cleanJsonResponse($jsonResponse) {
    log_message("Cleaning JSON response from GPT-4.");

    // Pre-process to remove LaTeX-like syntax and non-JSON content
    // Preserve important numerical fractions like (1/3) while removing LaTeX-like math expressions
    $jsonResponse = preg_replace('/\$\$(.*?)\$\$/s', '', $jsonResponse); // Remove $$...$$ LaTeX blocks
    $jsonResponse = preg_replace('/\\\\\((?!\d+\/\d+).*?\\\\\)/s', '', $jsonResponse); // Remove LaTeX \( ... \) but not numerical fractions like (1/3)
    $jsonResponse = preg_replace('/\\\\\[(.*?)\\\\\]/s', '', $jsonResponse); // Remove LaTeX \[ ... \] blocks

    // Extract JSON by finding the first and last occurrence of '{' and '}'
    $startPos = strpos($jsonResponse, '{');
    $endPos = strrpos($jsonResponse, '}');

    if ($startPos !== false && $endPos !== false) {
        // Extract the substring that should contain JSON
        $jsonPart = substr($jsonResponse, $startPos, $endPos - $startPos + 1);

        // Decode the cleaned JSON
        $jsonArray = json_decode($jsonPart, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            log_message("Finished cleaning JSON response.");
            return $jsonArray;
        } else {
            log_message("JSON decode error: " . json_last_error_msg());
            log_message("Attempting to clean up further...");
            // Additional cleanup if necessary, e.g., remove newlines, trailing commas, etc.
            $jsonPart = preg_replace('/,\s*([\}\]])/', '$1', $jsonPart); // Remove trailing commas
            $jsonArray = json_decode($jsonPart, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                log_message("Finished cleaning JSON response after further cleanup.");
                return $jsonArray;
            } else {
                log_message("Final JSON decode error: " . json_last_error_msg());
                log_message("Failed JSON part: " . $jsonPart);
                return null; // Return null if JSON decoding failed after all attempts
            }
        }
    } else {
        log_message("No JSON found in the response.");
        return null;
    }
}

try {
    log_message("Worker started");

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

        try {
            // Fetch submission details
            $sql = "SELECT fileName, aid FROM submissions WHERE sid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $sid);
            $stmt->execute();
            $stmt->bind_result($fileName, $assignmentId);
            $stmt->fetch();
            $stmt->close();
            log_message("Fetched submission details for SID: $sid, fileName: $fileName, assignmentId: $assignmentId");

            // Extract file contents
            $fileContent = '';
            $filePath = '../uploads/assignments/' . basename($fileName);
            $fileType = mime_content_type($filePath);

            log_message("File path: $filePath, File type: $fileType");

            if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                $fileContent = readDocx($filePath);
                log_message("Extracted content from DOCX file.");
            } elseif ($fileType === 'application/pdf') {
                $fileContent = readPdf($filePath);
                log_message("Extracted content from PDF file.");
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
            log_message("Fetched assignment and rubric details for assignment ID: $assignmentId");

            // Generate OpenAI prompt
            $prompt = "Objective:
                      Evaluate a student's assignment with comprehensive grading, including sub-scores, overall scores, & constructive feedback based on a provided rubric. Feedback should detail strengths, improvement areas, and action items in bullet points. 
                      About Assignment:\n";
            $prompt .= "Assignment Name: $assignmentName\n";
            $prompt .= "Assignment Instructions: $details\n";
            $prompt .= "Grading Instructions: $instructions\n";
            $prompt .= "Grading Style: $style\n";
            $prompt .= "Extra Details: $extraDetails\n";
            $prompt .= "\nScore Calculation:Use the rubric below to assign sub-scores & provide explanations based on essay content.\n";
            $prompt .= "Rubric: $rubricContent\n";
            $prompt .= "\nTotal Scores: Calculate weighted scores by multiplying each criterion score/max score by its weight & summing the results.\n";
            $prompt .= "Assign a Grade based on the following:
                        If score between 90-100, grade A.
                        If score between 80-89, grade B.
                        If score between 70-79, grade C.
                        If score between 60-69, grade D.
                        If score below 60, grade F.\n
                        Work through the evaluation step-by-step. Ensure clarity & logical flow in each feedback section.\n";
            $prompt .= "Assignment Content: $fileContent\n";
            $prompt .= "\n\nReturn your feedback in a JSON object like this, don't use LaTeX in the JSON. See the sample comments I shared, keep your structure similar to that:\n";
            $prompt .= json_encode([
                "grade" => "A",
                "score" => "50",
                "comments" => "<p><strong>Strengths:</strong> Your essay provides a well-rounded discussion of both the positive and negative impacts of social media on mental health. You have successfully highlighted the essential aspects and made a balanced case.</p><p><strong>Improvement Areas:</strong> The analysis would benefit from a deeper exploration of the negative impacts and more substantial evidence to support your points.</p><p><strong>Action Items:</strong></p><ul><li>Incorporate more specific studies or statistics to strengthen the arguments regarding negative impacts.</li><li>Expand on the positive impacts by including additional examples or case studies.</li><li>Review and refine the organization of the essay to ensure a smoother flow of ideas.</li></ul><p><strong>Sub-Scores and Justification:</strong></p><ul><li><strong>Understanding of Topic (3/4):</strong> You have a clear understanding of the topic with a balanced discussion. However, a more nuanced insight into the negative impacts would improve the analysis.</li><li><strong>Use of Evidence (3/5):</strong> The essay includes some relevant evidence but could be improved with a wider range of studies and data to support both positive and negative aspects.</li><li><strong>Critical Thinking (4/5):</strong> There are original insights and logical analysis present, though further critical examination of the negative impacts would enhance your arguments.</li><li><strong>Clarity and Organization (4/5):</strong> The essay is generally clear and well-organized, but a few areas could benefit from improved coherence and flow.</li></ul>"
            ], JSON_PRETTY_PRINT);
            $prompt .= "\nBefore printing the JSON, show your work in calculating the score.";

            log_message("Generated prompt for OpenAI.");
            log_message("------ PROMPT START ------");
            log_message($prompt);
            log_message("------ PROMPT END ------");

            // Send the prompt to OpenAI
            $openaiResponse = sendToGPT($prompt);
            if ($openaiResponse === null) {
                log_message('Error processing OpenAI response.');
                $pheanstalk->delete($job);
                continue;
            }

            // Clean the JSON response
            log_message("OpenAI RAW response: " . $openaiResponse);
            $cleanedResponse = cleanJsonResponse($openaiResponse);
            log_message("Cleaned JSON response: " . print_r($cleanedResponse, true));

            // Parse the OpenAI response
            if ($cleanedResponse !== null) {
                $grade = $cleanedResponse['grade'] ?? '';
                $score = $cleanedResponse['score'] ?? '0/100';
                $comments = $cleanedResponse['comments'] ?? '';

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
            } else {
                log_message("Failed to clean and parse the JSON response.");
            }
        } catch (Exception $e) {
            log_message('Error during job processing: ' . $e->getMessage());
            $pheanstalk->release($job); // Release the job back to the queue
        }

        $conn->close();

        // Delete the job from the queue
        $pheanstalk->delete($job);
        log_message("Job deleted from the queue for SID: $sid");
    }
} catch (Exception $e) {
    log_message('Error encountered: ' . $e->getMessage());
}

log_message("Worker finished");

echo "Worker finished.<br>";
?>
