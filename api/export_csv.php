<?php
session_start();
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['assignmentId'];
    $selectedIds = isset($_POST['selectedIds']) ? $_POST['selectedIds'] : [];
    $userId = $_SESSION['user_id'];

    if (empty($assignmentId)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
        exit;
    }

    $idsCondition = "";
    if (!empty($selectedIds)) {
        $ids = implode(",", array_map('intval', $selectedIds));
        $idsCondition = "AND sid IN ($ids)";
    }

    $stmt = $conn->prepare("SELECT * FROM submissions WHERE aid = ? $idsCondition");
    $stmt->bind_param('i', $assignmentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="submissions.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // Add UTF-8 BOM for Excel compatibility

        fputcsv($output, ['Student Name', 'File Name', 'Score', 'Comments', 'Status', 'Submission Time']);

        while ($row = $result->fetch_assoc()) {
            // Convert status codes to text
            if ($row['status'] == 0) {
                $row['status'] = "Pending Grading";
            } elseif ($row['status'] == 1) {
                $row['status'] = "Graded";
            } elseif ($row['status'] == 2) {
                $row['status'] = "Approved";
            }
            
            // Preserve formatting, line breaks, and spacing
            $comments = $row['comments'];
            // Replace HTML line breaks with actual line breaks
            $comments = str_replace(['<br>', '<br/>', '<br />', '</p><p>'], "\n", $comments);
            // Replace list items with proper formatting
            $comments = preg_replace('/<li>(.*?)<\/li>/', "\n• $1", $comments);
            // Remove other HTML tags
            $comments = strip_tags($comments);
            // Ensure consistent line breaks
            $comments = str_replace(["\r\n", "\r"], "\n", $comments);
            // Add extra line break after main sections
            $comments = preg_replace('/(Strengths:|Improvement Areas:|Action Items:|Sub-Scores and Justification:)/', "$1\n", $comments);
            
            fputcsv($output, [
                $row['studentName'],
                $row['fileName'],
                $row['score'],
                $comments,
                $row['status'],
                $row['submission_time']
            ]);
        }
        fclose($output);
    } else {
        echo json_encode(['success' => false, 'message' => 'No submissions found.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
