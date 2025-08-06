<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

// Include DB credentials
require 'c.php';

// 1) Connect
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// 2) Ensure table exists
$createTableSql = "
    CREATE TABLE IF NOT EXISTS `inline_comments` (
      `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `submission_id` INT UNSIGNED NOT NULL,
      `user_id` INT UNSIGNED NULL,
      `comment_text` TEXT NOT NULL,
      `highlighted_text` TEXT NOT NULL,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX (`submission_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (! $conn->query($createTableSql)) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not ensure inline_comments table: ' . $conn->error
    ]);
    exit();
}

// 3) Gather & validate inputs
$submissionId    = isset($_POST['submissionId'])    ? (int) $_POST['submissionId']    : null;
$highlightedText = isset($_POST['highlightedText']) ? trim($_POST['highlightedText']) : '';
$commentText     = isset($_POST['commentText'])     ? trim($_POST['commentText'])     : '';

// 3a) Check required fields
if (!$submissionId) {
    echo json_encode([
        'success' => false,
        'message' => 'Submission ID is required.'
    ]);
    exit();
}
if ($commentText === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Comment text cannot be empty.'
    ]);
    exit();
}

// 3b) (Optional) Authentication check
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'You must be logged in to comment.'
//     ]);
//     exit();
// }
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

// 4) Prepare & execute INSERT
$stmt = $conn->prepare("
    INSERT INTO inline_comments
      (submission_id, user_id, comment_text, highlighted_text)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param(
    "iiss",
    $submissionId,
    $userId,
    $commentText,
    $highlightedText
);

if (! $stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $stmt->error
    ]);
    exit();
}

// 5) Build success response
$newComment = [
    'id'              => $stmt->insert_id,
    'submission_id'   => $submissionId,
    'user_id'         => $userId,
    'comment_text'    => $commentText,
    'highlighted_text'=> $highlightedText,
    'created_at'      => date('Y-m-d H:i:s')
];

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'comment' => $newComment
]);
