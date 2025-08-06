<?php
// api/select_rubric.php
session_start();
header('Content-Type: application/json');

require '../config.php';  // Adjust the path to your config file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rubricOption = $_POST['rubricOption'];

    if ($rubricOption === 'existing') {
        $existingRubric = $_POST['existingRubricSelect'];
        echo json_encode(['status' => 'success', 'rubric' => $existingRubric]);
    } elseif ($rubricOption === 'upload') {
        if (isset($_FILES['rubricFile'])) {
            $rubricFile = $_FILES['rubricFile'];

            // Check if the file is a valid PDF file
            if ($rubricFile['type'] !== 'application/pdf') {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only PDF files are allowed.']);
                exit;
            }

            // Move the uploaded PDF file to a specific directory
            $uploadDir = '../rubrics/';
            $filePath = $uploadDir . basename($rubricFile['name']);

            if (move_uploaded_file($rubricFile['tmp_name'], $filePath)) {
                echo json_encode(['status' => 'success', 'message' => 'Rubric uploaded successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to upload rubric.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
        }
    } elseif ($rubricOption === 'create') {
        echo json_encode(['status' => 'redirect', 'url' => 'rubric.create.php']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid rubric option.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
