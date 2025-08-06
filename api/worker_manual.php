<?php
require '../vendor/autoload.php';
require 'c.php';
require_once __DIR__ . '/../load_env.php';

set_time_limit(300);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);

use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;

function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message<br>";
}

function readDocx($filePath) {
    log_message("Reading DOCX file: $filePath");
    try {
        if (!file_exists($filePath)) {
            throw new Exception("File does not exist: $filePath");
        }
        if (!is_readable($filePath)) {
            throw new Exception("File is not readable: $filePath");
        }

        $phpWord = IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= extractTextFromElement($element);
            }
        }
        return trim($text);
    } catch (Exception $e) {
        log_message("Error reading DOCX file: " . $e->getMessage());
        return null;
    }
}

function extractTextFromElement($element) {
    $text = '';
    if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
        $text .= $element->getText() . ' ';
    } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun || $element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
        foreach ($element->getElements() as $childElement) {
            $text .= extractTextFromElement($childElement);
        }
    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Footnote) {
        foreach ($element->getFootnoteRelationId() as $footnoteElement) {
            $text .= extractTextFromElement($footnoteElement);
        }
    } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItem) {
        $text .= $element->getText() . ' ';
    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Image) {
        // Skip images or handle as needed
    } elseif (method_exists($element, 'getElements')) {
        foreach ($element->getElements() as $childElement) {
            $text .= extractTextFromElement($childElement);
        }
    } else {
        // Handle other element types if necessary
    }
    return $text;
}


function readPdf($filePath) {
    log_message("Reading PDF file: $filePath");
    try {
        return Pdf::getText($filePath);
    } catch (Exception $e) {
        log_message("Error reading PDF file: " . $e->getMessage());
        return '';
    }
}

function sendToGPT($content) {
    log_message("Sending content to GPT-4 API.");
    $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key

    
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are an experienced educator who evaluates assignments with detailed & structured analysis. You provide clear & actionable feedback to help students improve.'
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 280);
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
            return null;
        }
    } else {
        log_message("No JSON found in the response.");
        return null;
    }
}

function get_db_connection() {
    $host = 'localhost';
    $username = 'root';
    $password = 'JustWing1t';
    $database = 'grady';

    $db_connection = new mysqli($host, $username, $password, $database);

    if ($db_connection->connect_error) {
        throw new Exception("Database connection failed: " . $db_connection->connect_error);
    }

    return $db_connection;
}

// Main logic
try {
    $submissionId = isset($_GET['submissionId']) ? (int)$_GET['submissionId'] : null;

    if (!$submissionId) {
        throw new Exception("No submission ID provided.");
    }

    log_message("Processing submission ID: $submissionId");

    $db_connection = get_db_connection();

    // Fetch submission details
    $sql = "SELECT fileName, aid FROM submissions WHERE sid = ?";
    $stmt = $db_connection->prepare($sql);
    $stmt->bind_param("i", $submissionId);
    $stmt->execute();
    $stmt->bind_result($fileName, $assignmentId);
    $stmt->fetch();
    $stmt->close();
    log_message("Fetched submission details for SID: $submissionId, fileName: $fileName, assignmentId: $assignmentId");

    // Extract file contents
    $filePath = '../uploads/assignments/' . basename($fileName);
    $fileType = mime_content_type($filePath);

    log_message("File path: $filePath, File type: $fileType");

    if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $fileContent = readDocx($filePath);
    } elseif ($fileType === 'application/pdf') {
        $fileContent = readPdf($filePath);
    } else {
        throw new Exception("Unsupported file type: $fileType");
    }

    // Fetch assignment and rubric details
    $sql = "SELECT 
                a.name AS assignment_name, 
                a.details, 
                a.instructions, 
                a.style, 
                a.scoring, 
                a.extra_details, 
                r.content AS rubric_content 
            FROM assignments a 
            JOIN rubrics r ON a.rubric = r.rid 
            WHERE a.aid = ?";
    $stmt = $db_connection->prepare($sql);
    $stmt->bind_param("i", $assignmentId);
    $stmt->execute();
    $stmt->bind_result($assignmentName, $details, $instructions, $style, $scoring, $extraDetails, $rubricContent);
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
$prompt .= "\nTotal Scores: Calculate weighted scores by dividing each criterion score by its maximum possible score, then multiplying by its respective weight %. Sum the weighted results to obtain the final score.\n";
$prompt .= "Assign a Grade based on the following:
        If score between 90-100, grade A. so on & so forth.\n
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
throw new Exception("Failed to get OpenAI response");
}

    log_message("OpenAI RAW response: $openaiResponse");
    
    // Clean and parse the response
    $cleanedResponse = cleanJsonResponse($openaiResponse);
    
    if ($cleanedResponse !== null) {
        $grade = $cleanedResponse['grade'] ?? '';
        $score = $cleanedResponse['score'] ?? '0/100';
        $comments = $cleanedResponse['comments'] ?? '';

        log_message("Parsed response - Grade: $grade, Score: $score, Comments: $comments");

        // Update the submission in the database
        $sql = "UPDATE submissions SET status = 1, grade = ?, score = ?, comments = ? WHERE sid = ?";
        $stmt = $db_connection->prepare($sql);
        $stmt->bind_param("sisi", $grade, $score, $comments, $submissionId);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            log_message("Submission updated successfully for SID: $submissionId");
        } else {
            log_message("Failed to update submission for SID: $submissionId");
        }
        $stmt->close();
    } else {
        log_message("Failed to clean and parse the JSON response.");
    }

    $db_connection->close();

} catch (Exception $e) {
    log_message("Error: " . $e->getMessage());
}
