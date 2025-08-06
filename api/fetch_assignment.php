<?php
header('Content-Type: application/json');
session_start();

// Database connection
require 'c.php';
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $assignmentId = $_GET['id'];
    $userId = $_SESSION['user_id'];

    if (empty($assignmentId)) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
        exit;
    }

    // Fetch assignment details
    $stmt = $conn->prepare('SELECT * FROM assignments WHERE aid = ? AND owner = ?');
    $stmt->bind_param('ii', $assignmentId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $assignment = $result->fetch_assoc();

        // Fetch assignment submissions
        $stmt = $conn->prepare('SELECT * FROM submissions WHERE aid = ?');
        $stmt->bind_param('i', $assignmentId);
        $stmt->execute();
        $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Debugging: Output the retrieved submissions to check their contents
        error_log(print_r($submissions, true));
        // print_r($submissions); // This should show the correct array
        
        // Ensure that the `studentName` is correctly assigned and `status` is meaningful
        foreach ($submissions as $key => $submission) {
            if (empty($submission['studentName'])) {
                $fileName = $submission['fileName'];
                $fileName = explode('/', $fileName);
                $fileName = end($fileName);
                $submissions[$key]['studentName'] = $fileName;
            }

            // Replace status values with meaningful descriptions
            if ($submission['status'] == 0) {
                $submissions[$key]['status'] = "Pending Grading";
            } elseif ($submission['status'] == 1) {
                $submissions[$key]['status'] = "Graded";
            } elseif ($submission['status'] == 2) {
                $submissions[$key]['status'] = "Approved";
            }
        }
        
        // Debugging: Ensure the array is still correct before JSON encoding
        error_log(print_r($submissions, true));

        // Calculate statistics
        $gradedCount = 0;
        $approvedCount = 0;
        $grades = [];
        foreach ($submissions as $submission) {
            if ($submission['status'] == "Graded") {
                $gradedCount++;
            }
            if ($submission['status'] == "Approved") {
                $approvedCount++;
            }
            if (is_numeric($submission['score'])) {
                $grades[] = (int)$submission['score'];
            }
        }

        $totalSubmissions = count($submissions);
        $mean = $totalSubmissions > 0 ? number_format(array_sum($grades) / $totalSubmissions, 2) : null;
        $mode = null; // Calculate the mode only if there are grades
        if ($totalSubmissions > 0) {
            $values = array_count_values($grades);
            arsort($values);
            $mode = key($values);
        }

        $median = null;
        if ($totalSubmissions > 0) {
            sort($grades);
            $mid = floor($totalSubmissions / 2);
            $median = $totalSubmissions % 2 != 0 ? $grades[$mid] : ($grades[$mid - 1] + $grades[$mid]) / 2;
        }

        // Prepare statistics, ensuring no zero values are sent unless data is valid
        $gradedCount = $gradedCount > 0 ? $gradedCount : null;
        $approvedCount = $approvedCount > 0 ? $approvedCount : null;

        echo json_encode([
            'success' => true,
            'assignment' => $assignment,
            'submissions' => $submissions,
            'stats' => [
                'submissionsGraded' => $gradedCount,
                'submissionsApproved' => $approvedCount,
                'meanScore' => $mean,
                'modeScore' => $mode,
                'medianScore' => $median
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Assignment not found or access denied.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
