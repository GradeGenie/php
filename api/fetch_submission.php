<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
require 'c.php';

// ── 1) If download=1, proxy the PDF itself ─────────────────────────────────
if (isset($_GET['download'])) {
    // a) Auth & parameter check
    if (!isset($_SESSION['user_id'])) {
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }
    if (empty($_GET['id'])) {
        header('HTTP/1.1 400 Bad Request');
        exit();
    }
    $submissionId = intval($_GET['id']);

    // b) Pull the remote file URL from the DB
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        header('HTTP/1.1 500 Internal Server Error');
        exit();
    }
    $stmt = $conn->prepare("SELECT fileName FROM submissions WHERE sid = ?");
    $stmt->bind_param('i', $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!($row = $result->fetch_assoc())) {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
    $remoteUrl = $row['fileName'];
    $stmt->close();
    $conn->close();

    // c) Fetch the PDF bytes
    $pdfData = @file_get_contents($remoteUrl);
    if ($pdfData === false) {
        header('HTTP/1.1 502 Bad Gateway');
        exit();
    }

    // d) Stream it back as PDF
    header('Content-Type: application/pdf');
    header('Content-Length: ' . strlen($pdfData));
    header('Content-Disposition: inline; filename="submission_' . $submissionId . '.pdf"');
    echo $pdfData;
    exit();
}

// ── 2) Otherwise, return JSON with a same‑origin proxy URL ───────────────────
header('Content-Type: application/json');

$response = [
    'success'    => false,
    'submission' => null,
];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User is not logged in.';
    echo json_encode($response);
    exit();
}
if (empty($_GET['id'])) {
    $response['message'] = 'Submission ID is required.';
    echo json_encode($response);
    exit();
}

$submissionId = intval($_GET['id']);

// Fetch submission record
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    $response['message'] = 'Database connection failed: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

$stmt = $conn->prepare(
    "SELECT sid, fileName, studentName, status, grade, score, comments, aid 
     FROM submissions 
     WHERE sid = ?"
);
$stmt->bind_param('i', $submissionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Build a same‑origin proxy URL
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['PHP_SELF'];
    $proxyUrl = "{$scheme}://{$host}{$script}"
              . "?id=" . urlencode($submissionId)
              . "&download=1";

    // Override the JSON field *fileName* only
    $row['fileName'] = $proxyUrl;
    $response['submission'] = $row;
    $response['success']    = true;
} else {
    $response['message'] = 'Submission not found or access denied.';
}

$stmt->close();
$conn->close();

echo json_encode($response);
