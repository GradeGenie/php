<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php'; // Include Composer's autoloader

use Spatie\PdfToText\Pdf;

// Function to read DOCX files
function readDocx($filePath) {
    $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
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

// Function to read PDF files using Spatie's PDF to Text package
function readPdf($filePath) {
    try {
        $text = Pdf::getText($filePath);
    } catch (Exception $e) {
        echo "An error occurred: " . $e->getMessage();
        return '';
    }
    
    return $text;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'];
    $filePath = $file['tmp_name'];
    $fileType = $file['type'];

    if ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
        $content = readDocx($filePath);
        echo "<h3>DOCX File Content:</h3><pre>$content</pre>";
    } elseif ($fileType === 'application/pdf') {
        $content = readPdf($filePath);
        echo "<h3>PDF File Content:</h3><pre>$content</pre>";
    } else {
        echo "Unsupported file type.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test File Upload</title>
</head>
<body>
    <h1>Test File Upload</h1>
    <form action="test_smalot.php" method="post" enctype="multipart/form-data">
        <label for="file">Upload a DOCX or PDF file:</label>
        <input type="file" id="file" name="file" accept=".pdf,.docx" required>
        <button type="submit">Upload and Read</button>
    </form>
</body>
</html>
