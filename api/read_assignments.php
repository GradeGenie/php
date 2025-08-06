<?php
require '../vendor/autoload.php'; // Include Composer's autoloader
use PhpOffice\PhpWord\IOFactory; // For reading DOCX files
use Smalot\PdfParser\Parser; // For reading PDF files

// Database connection
$servername = "localhost";
$username = "root";
$password = "password";
$dbname = "your_database";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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

// Function to call GPT-4 API
function callGpt4Api($text) {
    $apiKey = 'your_openai_api_key';
    $url = 'https://api.openai.com/v1/engines/davinci-codex/completions';

    $data = [
        'prompt' => $text,
        'max_tokens' => 500,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $apiKey,
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return null;
    }

    $response = json_decode($result, true);
    return $response['choices'][0]['text'];
}

// Handle uploaded files
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_FILES['assignments']['tmp_name'] as $index => $tmpName) {
        $fileName = $_FILES['assignments']['name'][$index];
        $fileType = $_FILES['assignments']['type'][$index];

        if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $fileContent = readDocx($tmpName);
        } elseif ($fileType === 'application/pdf') {
            $fileContent = readPdf($tmpName);
        } else {
            continue; // Skip unsupported file types
        }

        $gpt4Response = callGpt4Api($fileContent);

        if ($gpt4Response) {
            $stmt = $conn->prepare("INSERT INTO assignments (file_name, gpt_response) VALUES (?, ?)");
            $stmt->bind_param("ss", $fileName, $gpt4Response);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "Files processed successfully.";
}

$conn->close();
?>
