<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');
session_start();

// 1) Include DB credentials & connect
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// 2) Ensure inline_comments table exists
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
        'message' => 'Could not create inline_comments table: ' . $conn->error
    ]);
    exit();
}

// 3) Validate input
$submissionId = isset($_GET['submissionId']) ? (int) $_GET['submissionId'] : 0;
if ($submissionId < 1) {
    echo json_encode([
        'success' => false,
        'message' => 'A valid submissionId is required.'
    ]);
    exit();
}

// 4) Fetch comments
$stmt = $conn->prepare("
    SELECT
      id,
      submission_id,
      user_id,
      comment_text,
      highlighted_text,
      created_at
    FROM inline_comments
    WHERE submission_id = ?
    ORDER BY created_at ASC
");
$stmt->bind_param("i", $submissionId);

if (! $stmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . $stmt->error
    ]);
    exit();
}

$result = $stmt->get_result();
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'id'               => (int)$row['id'],
        'submission_id'    => (int)$row['submission_id'],
        'user_id'          => $row['user_id'] !== null ? (int)$row['user_id'] : null,
        'comment_text'     => $row['comment_text'],
        'highlighted_text' => $row['highlighted_text'],
        'created_at'       => $row['created_at']
    ];
}

// 5) Return JSON
echo json_encode([
    'success'  => true,
    'comments' => $comments
]);

// 6) Cleanup
$stmt->close();
$conn->close();
