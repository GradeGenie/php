<?php
require '../vendor/autoload.php';
require 'c.php'; // Initializes $conn

set_time_limit(300);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/error.log'); // Ensure this path is writable

use PhpOffice\PhpWord\IOFactory;
use Spatie\PdfToText\Pdf;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Values\TubeName;

// Initialize Pheanstalk
$pheanstalk = Pheanstalk::create('127.0.0.1');

$logFile = 'log/worker2.html';

// Ensure the log file exists
if (!file_exists($logFile)) {
    touch($logFile);
    chmod($logFile, 0666);
    echo "Created log file.\n";
}

function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp] $message";
    
    // Log to PHP error log
    error_log($formattedMessage);
    
    // Echo to console (useful if running in a terminal)
    echo "$formattedMessage\n";
    
    // Append to custom log file
    file_put_contents($logFile, "$formattedMessage" . PHP_EOL, FILE_APPEND);
}

function readDocx($filePath, $rubric) {
    log_message("Reading DOCX file: $filePath");
    try {
        if (!file_exists($filePath)) {
            throw new Exception("File does not exist: $filePath");
        }
        if (!is_readable($filePath)) {
            throw new Exception("File is not readable: $filePath");
        }

        $fileSize = filesize($filePath);
        log_message("File size: $fileSize bytes");
        
        if ($fileSize === 0) {
            throw new Exception("File is empty: $filePath");
        }
        
        log_message("PHP memory limit: " . ini_get('memory_limit'));
        log_message("Current memory usage before loading DOCX: " . memory_get_usage(true) . " bytes");
        
        $phpWord = IOFactory::load($filePath);
        
        log_message("Current memory usage after loading DOCX: " . memory_get_usage(true) . " bytes");
        
        $text = '';
        $formattingIssues = [];
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . ' ';
                            
                            // Validate formatting based on rubric
                            if (!empty($rubric['fontFamily']) && $childElement->getFontName() !== $rubric['fontFamily']) {
                                $formattingIssues[] = "Incorrect font family: Expected " . $rubric['fontFamily'] . ", found " . $childElement->getFontName();
                            }
                            if (!empty($rubric['fontSize']) && $childElement->getFontSize() !== $rubric['fontSize']) {
                                $formattingIssues[] = "Incorrect font size: Expected " . $rubric['fontSize'] . ", found " . $childElement->getFontSize();
                            }
                            if (isset($rubric['bold']) && $rubric['bold'] !== null && $childElement->isBold() !== $rubric['bold']) {
                                $formattingIssues[] = "Bold formatting issue: Expected " . ($rubric['bold'] ? "bold" : "not bold") . ", but found different.";
                            }
                            if (isset($rubric['italic']) && $rubric['italic'] !== null && $childElement->isItalic() !== $rubric['italic']) {
                                $formattingIssues[] = "Italic formatting issue: Expected " . ($rubric['italic'] ? "italic" : "not italic") . ", but found different.";
                            }
                        }
                    }
                } elseif (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                    // Additional formatting checks can be added here
                }
            }
        }
        
        $text = trim($text);
        log_message("Finished reading DOCX file. Content length: " . strlen($text));
        log_message("Peak memory usage: " . memory_get_peak_usage(true) . " bytes");
        log_message("Detected formatting issues: " . json_encode($formattingIssues));
        
        return ['text' => $text, 'formattingIssues' => $formattingIssues];
    } catch (Exception $e) {
        log_message("Error reading DOCX file: " . $e->getMessage());
        log_message("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}

function readImageOCR($filePath) {
    log_message("Reading image file for OCR: $filePath");
    try {
        if (!file_exists($filePath)) {
            throw new Exception("Image file does not exist: $filePath");
        }
        // Ensure Tesseract is installed and accessible
        $ocrText = shell_exec("tesseract " . escapeshellarg($filePath) . " stdout");
        if (empty($ocrText)) {
            throw new Exception("OCR failed on image file: $filePath");
        }
        log_message("OCR text extraction successful.");
        return $ocrText;
    } catch (Exception $e) {
        log_message("Error during OCR: " . $e->getMessage());
        return null; // Skip processing this file
    }
}

function sendToGPT($content) {
    log_message("Sending content to GPT-4 API.");
    $apiKey = getenv('OPENAI_API_KEY');
    
    if (!$apiKey) {
        log_message("OpenAI API key is not set.");
        return null;
    }

    $data = [
        'model' => 'gpt-4',
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
    curl_setopt($ch, CURLOPT_TIMEOUT, 280); // Set curl timeout
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        log_message('Error during GPT-4 API call: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        log_message("HTTP Error during GPT-4 API call: " . $http_status . "\nResponse: " . $result);
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);
    log_message("Received response from GPT-4 API.");
    return $response['choices'][0]['message']['content'] ?? null;
}

function cleanJsonResponse($jsonResponse) {
    log_message("Cleaning JSON response from GPT-4.");
    
    // Remove LaTeX-like syntax and non-JSON content
    $jsonResponse = preg_replace('/\$\$(.*?)\$\$/s', '', $jsonResponse);
    $jsonResponse = preg_replace('/\\\\\((?!\d+\/\d+).*?\\\\\)/s', '', $jsonResponse);
    $jsonResponse = preg_replace('/\\\\\[(.*?)\\\\\]/s', '', $jsonResponse);

    // Extract JSON by finding the first and last occurrence of '{' and '}'
    $startPos = strpos($jsonResponse, '{');
    $endPos = strrpos($jsonResponse, '}');

    if ($startPos !== false && $endPos !== false) {
        $jsonPart = substr($jsonResponse, $startPos, $endPos - $startPos + 1);
        $jsonArray = json_decode($jsonPart, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            log_message("Finished cleaning JSON response.");
            return $jsonArray;
        } else {
            log_message("JSON decode error: " . json_last_error_msg());
            // Attempt further cleanup
            $jsonPart = preg_replace('/,\s*([\}\]])/', '$1', $jsonPart);
            $jsonArray = json_decode($jsonPart, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                log_message("Finished cleaning JSON response after further cleanup.");
                return $jsonArray;
            } else {
                log_message("Final JSON decode error: " . json_last_error_msg());
                return null;
            }
        }
    } else {
        log_message("No JSON found in the response.");
        return null;
    }
}

function isDocxFile($filePath) {
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        // Check for DOCX-specific files inside the ZIP
        $isDocx = $zip->locateName('[Content_Types].xml') !== false;
        $zip->close();
        return $isDocx;
    }
    return false;
}

function isSupportedFileType($fileType, $filePath) {
    $supportedTypes = [
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
        'application/pdf', // PDF
        'image/jpeg', // JPEG
        'image/png', // PNG
        'image/gif' // GIF
    ];
    return in_array($fileType, $supportedTypes) || 
           ($fileType === 'application/zip' && isDocxFile($filePath));
}

function alert_buried_job($jobId) {
    // Log the buried job
    file_put_contents('log/buried_jobs.log', "Job ID $jobId was buried\n", FILE_APPEND);
    
    // Send an email alert
    $to = 'hello@getgradegenie.com';
    $subject = "Job Buried Alert";
    $message = "Job ID $jobId was buried after max retries.";
    $headers = "From: no-reply@getgradegenie.com\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        log_message("Alert email sent to $to for buried job ID: $jobId");
    } else {
        log_message("Failed to send alert email for buried job ID: $jobId");
    }
}

$maxRetries = 3;

// Infinite loop to keep the worker running
while (true) {
    try {
        log_message("Worker started");
        log_message("Connected to Beanstalkd server.");

        $pheanstalk->watch(new TubeName('grading'));
        $pheanstalk->ignore(new TubeName('default'));
        log_message("Watching 'grading' tube and ignoring 'default' tube");

        // Reserve a job with a timeout
        $job = $pheanstalk->reserveWithTimeout(10);

        if ($job === false || $job === null) {
            log_message("No job available. Continuing...");
            continue; // Continue the loop to wait for new jobs
        }

        $jobData = json_decode($job->getData(), true); // Initialize $jobData

        log_message("Processing job ID: " . $job->getId());

        // Validate job data
        if (!isset($jobData['assignmentId'], $jobData['filePath'], $jobData['sid'])) {
            log_message("Invalid job data. Missing 'assignmentId', 'filePath', or 'sid'. Burying job.");
            $pheanstalk->bury($job);
            log_message("Job ID: " . $job->getId() . " buried due to invalid data.");
            continue; // Continue to the next iteration
        }

        // Assign variables from job data
        $assignmentId = $jobData['assignmentId'];
        $sid = $jobData['sid'];
        $filePath = $jobData['filePath'];

        // Fetch submission details using $conn
        $sql = "SELECT fileName, aid FROM submissions WHERE sid = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $stmt->bind_result($fileName, $assignmentId);
        if (!$stmt->fetch()) {
            log_message("No submission found for SID: $sid. Deleting job.");
            $pheanstalk->delete($job); // Delete the job from the queue
            $stmt->close();
            continue; // Continue to the next iteration
        }
        $stmt->close();
        log_message("Fetched submission details for SID: $sid, fileName: $fileName, assignmentId: $assignmentId");

        $filePath = '../uploads/assignments/' . basename($fileName);

        // Extract file contents
        $fileContent = '';
        $fileType = mime_content_type($filePath);

        log_message("File path: $filePath, File type: $fileType");

        // Check if file is supported
        if (!isSupportedFileType($fileType, $filePath)) {
            log_message("Unsupported file type: $fileType. Burying job.");
            $pheanstalk->bury($job); // Bury unsupported job to avoid retry loops
            continue; // Stop further processing
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
            r.font_family,
            r.font_size,
            r.bold,
            r.italic
        FROM assignments a 
        JOIN rubrics r ON a.rubric = r.rid 
        WHERE a.aid = ?";
        $stmt = $conn->prepare($sql); // Use $conn from c.php
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $stmt->bind_result($assignmentName, $details, $instructions, $style, $scoring, $extraDetails, $rubricContent, $fontFamily, $fontSize, $bold, $italic);
        if (!$stmt->fetch()) {
            log_message("No assignment found for AID: $assignmentId. Deleting job.");
            $pheanstalk->delete($job); // Remove job from the queue
            $stmt->close();
            continue; // Stop further processing
        }
        $stmt->close();

        // Prepare rubric formatting rules
        $rubric = [
            'fontFamily' => $fontFamily ?: null,
            'fontSize' => $fontSize ?: null,
            'bold' => isset($bold) ? (bool) $bold : null,
            'italic' => isset($italic) ? (bool) $italic : null,
        ];

        // Handle OCR for scanned PDFs or images
        if ($fileType === 'application/pdf' || strpos($fileType, 'image/') !== false) {
            $fileContent = readImageOCR($filePath);
            if ($fileContent === null) {
                log_message("OCR failed for file: $filePath. Burying job.");
                $pheanstalk->bury($job);
                continue;
            }
            log_message("Extracted content from image using OCR.");
        } elseif ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || 
                  ($fileType === 'application/zip' && isDocxFile($filePath))) {
            $fileContentDetails = readDocx($filePath, $rubric);
            if ($fileContentDetails === null) {
                log_message("Failed to read DOCX file: $filePath. Burying job.");
                $pheanstalk->bury($job); // or handle the error accordingly
                continue; // Stop further processing
            }
            log_message("Extracted content from DOCX file.");
            
            // Check if there are any formatting issues
            if (!empty($fileContentDetails['formattingIssues'])) {
                log_message("Formatting issues detected: " . json_encode($fileContentDetails['formattingIssues']));
            }
            
            // Assign the extracted text content for further processing
            $fileContent = $fileContentDetails['text'];
            
        } else {
            log_message("Unsupported file type: $fileType. Burying job.");
            $pheanstalk->bury($job); // Bury unsupported job to avoid retry loops
            continue;  // Exit job processing for unsupported file
        }

        // Generate OpenAI prompt
        $prompt = "Objective:
                  Evaluate a student's assignment with comprehensive grading, including sub-scores, overall scores, & constructive feedback based on a provided rubric. Feedback should detail strengths, improvement areas, and action items in bullet points. 
                  About Assignment:\n";
        $prompt .= "Assignment Name: $assignmentName\n";
        $prompt .= "Assignment Instructions: $details\n";
        $prompt .= "Grading Instructions: $instructions\n";
        $prompt .= "Grading Style: $style\n";
        $prompt .= "Extra Details: $extraDetails\n";
        $prompt .= "\nScore Calculation: Use the rubric below to assign sub-scores & provide explanations based on essay content.\n";
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
            if (!$stmt) {
                throw new Exception("Database prepare statement failed: " . $conn->error);
            }
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
            throw new Exception("Invalid JSON response from OpenAI");
        }

        // After successful processing
        $pheanstalk->delete($job);
        log_message("Job ID: " . $job->getId() . " completed and deleted from queue");

    } catch (Exception $e) {
        log_message("Error processing job: " . $e->getMessage());
        log_message("Stack trace: " . $e->getTraceAsString());
        
        $retries = isset($jobData['retries']) ? $jobData['retries'] + 1 : 1;
        $retryDelay = pow(2, $retries) * 60; // Exponential backoff for retry delay

        if ($retries <= $maxRetries) {
            $jobData['retries'] = $retries;
            // Re-encode $jobData with updated retries and release the job back into the queue
            $pheanstalk->useTube('grading')->put(json_encode($jobData), Pheanstalk::DEFAULT_PRIORITY, $retryDelay);
            log_message("Job ID: " . $job->getId() . " released back to queue for retry $retries/$maxRetries with $retryDelay seconds delay.");
            $pheanstalk->delete($job); // Delete the current job to avoid duplication
        } else {
            log_message("Max retries reached. Burying job.");
            alert_buried_job($job->getId()); // Log the buried job and send alert
            $pheanstalk->bury($job); // Bury the job after max retries
        }
    } catch (Error $e) {
        log_message("Fatal error occurred: " . $e->getMessage());
        log_message("Stack trace: " . $e->getTraceAsString());
        alert_buried_job($job->getId()); // Log the buried job and send alert
        $pheanstalk->bury($job); // Bury the job on a fatal error
    } finally {
        // Close database connection only if it's still open
        if (isset($conn) && $conn->ping()) {
            $conn->close();
            log_message("Database connection closed.");
        }
        
        log_message("Worker iteration finished and connections closed.");
    }
}
?>
