<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();
include '../api/c.php';

// Check if the user is authorized
if (!isset($_SESSION['user_email']) || strpos($_SESSION['user_email'], 'getgradegenie') === false) {
    header('Location: ../index.php');
    exit();
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the assignment ID from the URL parameter
$aid = isset($_GET['aid']) ? intval($_GET['aid']) : 0;

if ($aid === 0) {
    die("Invalid assignment ID");
}

// Function to format date
function formatDate($date) {
    return date('j M \'y, g:ia', strtotime($date));
}

// Fetch assignment details
$assignment_query = "SELECT * FROM assignments WHERE aid = ?";
$stmt = $conn->prepare($assignment_query);
$stmt->bind_param("i", $aid);
$stmt->execute();
$assignment_result = $stmt->get_result();
$assignment = $assignment_result->fetch_assoc();

// Fetch submissions for this assignment
$submissions_query = "SELECT * FROM submissions WHERE aid = ? ORDER BY submission_time DESC";
$stmt = $conn->prepare($submissions_query);
$stmt->bind_param("i", $aid);
$stmt->execute();
$submissions_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions for <?php echo htmlspecialchars($assignment['name']); ?></title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .comment-short { max-height: 3em; overflow: hidden; }
        .comment-full { display: none; }
        .see-more, .see-less { color: blue; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Submissions for: <?php echo htmlspecialchars($assignment['name']); ?></h2>
    <?php if ($submissions_result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>SID</th>
                    <th>Status</th>
                    <th>Grade</th>
                    <th>Score</th>
                    <th>Submission Time</th>
                    <th>Comments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($submission['sid']); ?></td>
                        <td><?php echo $submission['status'] == 0 ? '❌ Not graded' : ($submission['status'] == 1 ? '✅ Graded' : 'In review'); ?></td>
                        <td><?php echo htmlspecialchars($submission['grade']); ?></td>
                        <td><?php echo htmlspecialchars($submission['score']); ?></td>
                        <td><?php echo formatDate($submission['submission_time']); ?></td>
                        <td>
                            <?php
                            $full_comment = $submission['comments'];
                            $short_comment = substr($full_comment, 0, 100);
                            if (strlen($full_comment) > 100) {
                                $short_comment .= '...';
                            }
                            ?>
                            <div class="comment-short"><?php echo $short_comment; ?></div>
                            <div class="comment-full"><?php echo $full_comment; ?></div>
                            <?php if (strlen($full_comment) > 100): ?>
                                <span class="see-more">See more</span>
                                <span class="see-less" style="display: none;">See less</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo htmlspecialchars($submission['fileName']); ?>" target="_blank">View Submission</a>
                            <?php if ($submission['status'] != 1): ?>
                                | <a href="../api/worker_manual.php?submissionId=<?php echo $submission['sid']; ?>" target="_blank">Grade</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No submissions found for this assignment.</p>
    <?php endif; ?>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('see-more') || e.target.classList.contains('see-less')) {
                var commentCell = e.target.closest('td');
                var shortComment = commentCell.querySelector('.comment-short');
                var fullComment = commentCell.querySelector('.comment-full');
                var seeMore = commentCell.querySelector('.see-more');
                var seeLess = commentCell.querySelector('.see-less');

                if (e.target.classList.contains('see-more')) {
                    shortComment.style.display = 'none';
                    fullComment.style.display = 'block';
                    seeMore.style.display = 'none';
                    seeLess.style.display = 'inline';
                } else {
                    shortComment.style.display = 'block';
                    fullComment.style.display = 'none';
                    seeMore.style.display = 'inline';
                    seeLess.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
