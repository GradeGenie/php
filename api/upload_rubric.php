<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; // Include Composer's autoloader
require_once __DIR__ . '/../load_env.php';

use PhpOffice\PhpWord\IOFactory; // For reading DOCX files
use Smalot\PdfParser\Parser; // For reading PDF files

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

function sendToGPT($content) {
    // Your OpenAI API key
        $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key


    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant.',
            ],
            [
                'role' => 'user',
                'content' => "Take these raw file contents from an uploaded rubric PDF/DOCX, and attempt to format it into an HTML table. Return the HTML. Any titles or extra contents outside of the rubric should be discarded. ONLY return the HTML table, no other text or formatting. \n\n$content",
            ],
        ],
        'max_tokens' => 1500,
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
        echo 'Error:' . curl_error($ch);
        return null;
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        echo "HTTP Error: " . $http_status . "\n";
        echo "Response: " . $result . "\n";
        return null;
    }

    curl_close($ch);

    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? null;
}

function cleanHtmlTable($htmlTable) {
    // Remove ```html and ``` from the response if they exist
    $htmlTable = str_replace(["```html", "```"], "", $htmlTable);
    return trim($htmlTable);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'];
    $filePath = $file['tmp_name'];
    $fileType = $file['type'];

    if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $content = readDocx($filePath);
    } elseif ($fileType === 'application/pdf') {
        $content = readPdf($filePath);
    } else {
        echo "Unsupported file type.";
        exit();
    }

    $htmlTable = sendToGPT($content);
    if ($htmlTable) {
        $cleanedHtmlTable = cleanHtmlTable($htmlTable);
        
        // Display the extracted HTML table and the form for additional rubric details
        echo "<h3>Extracted Rubric</h3>$cleanedHtmlTable";
        echo "<br><br><form id='saveForm'>
                <div class='formField'>
                    <label for='assignment-title'>Rubric Name *</label>
                    <input type='text' id='assignment-title' name='assignment-title' placeholder='Essay on the Roman Empire' style='width: 300px;' required>
                </div>
                <div class='formField'>
                    <label for='description'>Description <span class='optional'>(optional)</span></label>
                    <textarea id='description' name='description' placeholder='Rubric for the Roman Empire Assignment, week 3 history - 2000 word paper'></textarea>
                </div>
                <input type='hidden' name='content' value='" . htmlspecialchars($cleanedHtmlTable, ENT_QUOTES, 'UTF-8') . "'>
                <button type='button' class='button' onclick='saveRubric()'>Save Rubric</button>
              </form>";
    } else {
        echo "Failed to process the file content.";
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
function saveRubric() {
    var formData = $('#saveForm').serialize();

    $.ajax({
        type: 'POST',
        url: 'api/save_rubric.php',
        data: formData,
        success: function(response) {
            console.log("Raw response:", response);
            try {
                var jsonResponse = JSON.parse(response);
                console.log("Parsed JSON response:", jsonResponse);

                if (jsonResponse.status === 'success') {
                    alert('Rubric saved successfully.');
                } else {
                    alert('Error: ' + jsonResponse.message);
                }
            } catch (e) {
                console.error('Error parsing JSON:', e);
                alert('Unexpected error occurred. Check console for details.');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            alert('An error occurred: ' + error);
        }
    });
}


</script>
