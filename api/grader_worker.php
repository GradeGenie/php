<?php
require 'c.php';
require 'vendor/autoload.php'; // Include necessary libraries
use Pheanstalk\Pheanstalk;
use OpenAI\Client;

$pheanstalk = Pheanstalk::create('127.0.0.1');

// Define a function to map scores to letter grades
function mapScoreToLetterGrade($score) {
    if ($score >= 90) {
        return 'A';
    } elseif ($score >= 80) {
        return 'B';
    } elseif ($score >= 70) {
        return 'C';
    } elseif ($score >= 60) {
        return 'D';
    } else {
        return 'F';
    }
}

while (true) {
    $job = $pheanstalk->watch('grading')->reserve();

    $jobData = json_decode($job->getData(), true);
    $assignmentId = $jobData['assignmentId'];
    $fileName = $jobData['fileName'];
    $userId = $jobData['userId'];
    $rubricId = $jobData['rubricId'];
    $gradingInstructions = $jobData['gradingInstructions'];
    $gradingStyle = $jobData['gradingStyle'];

    // Fetch the assignment, rubric details, and file contents
    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $assignmentQuery = $conn->query("SELECT * FROM assignments WHERE aid = {$assignmentId}");
    $assignment = $assignmentQuery->fetch_assoc();
    $rubricQuery = $conn->query("SELECT * FROM rubrics WHERE rid = {$rubricId}");
    $rubric = $rubricQuery->fetch_assoc();

    $filePath = __DIR__ . "/../assignments/{$fileName}";
    $fileContents = file_get_contents($filePath);

    // OpenAI prompt to grade the assignment
    $openAi = new Client('your-openai-api-key');
    $prompt = "
        You are an accurate, objective, & consistent grader.
        Use 2nd person perspective & human language.
        Given rubric:
        {$rubric['content']}
        Assignment Instructions:
        {$assignment['instructions']}
        Grading Instructions:
        {$gradingInstructions}, pls grade Submission: {$fileContents}.
        Pls
        - Give clear, logical sub-scores w/ specific feedback given each criterion's weighting & max score,then calculate final grade.
        - Overall comments w/ strengths, areas for improvement, & action items- Feedback Style: {$gradingStyle}.
        Be evidence-based.
    ";

    $response = $openAi->completions()->create([
        'model' => 'text-davinci-003',
        'prompt' => $prompt,
        'max_tokens' => 800,
        'temperature' => 0, // Ensure deterministic responses
    ]);

    $gradingResult = json_decode($response['choices'][0]['text'], true);

    if (!empty($gradingResult['score'])) {
        $numericScore = floatval($gradingResult['score']);
        $letterGrade = mapScoreToLetterGrade($numericScore);
    } else {
        $letterGrade = 'F'; // Default to 'F' if score is missing
    }

    // Update the submission with the grading result
    $stmt = $conn->prepare("UPDATE submissions SET status = ?, grade = ?, score = ?, comments = ? WHERE fileName = ?");
    $status = 1; // Mark as graded
    $stmt->bind_param('issss', $status, $letterGrade, $gradingResult['score'], $gradingResult['comments'], $fileName);
    if ($stmt->execute()) {
        echo "Submission graded successfully.";
    } else {
        echo "Error updating submission: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    $pheanstalk->delete($job);
}
?>
