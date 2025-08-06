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

// Assuming you have the customer's user ID
$user_id = $_GET['id'] ?? 1; // Replace with actual user ID retrieval method

// Fetch classes created by the user
$classes_query = "SELECT * FROM classes WHERE owner = $user_id";
$classes_result = $conn->query($classes_query);

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer View</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .left-panel {
            width: 50%;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .right-panel {
            width: 50%;
            height: 100%;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <h2>Classes and Assignments</h2>
            <?php
            if ($classes_result->num_rows > 0) {
                while ($class = $classes_result->fetch_assoc()) {
                    echo "<h3>Class: {$class['name']}</h3>";
                    
                    // Fetch assignments for this class
                    $assignments_query = "SELECT * FROM assignments WHERE class = {$class['cid']}";
                    $assignments_result = $conn->query($assignments_query);
                    
                    if ($assignments_result->num_rows > 0) {
                        echo "<ul>";
                        while ($assignment = $assignments_result->fetch_assoc()) {
                            // Count submissions for this assignment
                            $submissions_query = "SELECT COUNT(*) as count FROM submissions WHERE aid = {$assignment['aid']}";
                            $submissions_result = $conn->query($submissions_query);
                            $submission_count = $submissions_result->fetch_assoc()['count'];
                            
                            echo "<li>{$assignment['name']} - 
                                  <a href='#' onclick='loadSubmissions({$assignment['aid']})'>
                                    Submissions: $submission_count
                                  </a></li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>No assignments found for this class.</p>";
                    }
                }
            } else {
                echo "<p>No classes found for this user.</p>";
            }
            ?>
        </div>
        <div class="right-panel">
            <iframe id="submissionsFrame" src=""></iframe>
        </div>
    </div>

    <script>
        function loadSubmissions(aid) {
            document.getElementById('submissionsFrame').src = `view_submissions.php?aid=${aid}`;
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>
