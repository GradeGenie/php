<?php
// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'c.php';

// Check if the POST variables are set
if (isset($_POST['course_name']) && isset($_POST['course_description']) && isset($_POST['academic_level']) && 
    isset($_POST['duration']) && isset($_POST['instructor_name'])) {
    
    $course_name = $_POST['course_name'];
    $course_description = $_POST['course_description'];
    $academic_level = $_POST['academic_level'];
    $duration = $_POST['duration'];
    $instructor_name = $_POST['instructor_name'];
    
    // Optional parameters
    $learning_outcomes = isset($_POST['learning_outcomes']) ? $_POST['learning_outcomes'] : '';
    $textbooks = isset($_POST['textbooks']) ? $_POST['textbooks'] : '';
    $grading_policy = isset($_POST['grading_policy']) ? $_POST['grading_policy'] : '';
    $schedule = isset($_POST['schedule']) ? $_POST['schedule'] : '';
    
    $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key


    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a syllabus creator. People will give you course information, and you will generate a comprehensive syllabus. Please return the syllabus as HTML. Include course information, learning outcomes, weekly schedule, textbooks, grading policy, and other standard syllabus sections. Make it professional and comprehensive. ONLY return the HTML content, no other text or formatting.'
            ],
            [
                'role' => 'user',
                'content' => "Generate a syllabus for the following course:\n\nCourse Name: $course_name\nCourse Description: $course_description\nAcademic Level: $academic_level\nDuration: $duration\nInstructor Name: $instructor_name\nLearning Outcomes: $learning_outcomes\nTextbooks: $textbooks\nGrading Policy: $grading_policy\nSchedule: $schedule"
            ]
        ]
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(['status' => 'error', 'message' => curl_error($ch)]);
    } else {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            $syllabus_content = $result['choices'][0]['message']['content'];
            
            // Save to database if user is logged in
            if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                $user_id = $_POST['user_id'];
                $syllabus_title = $course_name . " Syllabus";
                $created_at = date('Y-m-d H:i:s');
                
                // Prepare SQL statement
                $stmt = $conn->prepare("INSERT INTO syllabi (user_id, title, content, course_name, academic_level, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssss", $user_id, $syllabus_title, $syllabus_content, $course_name, $academic_level, $created_at);
                
                if ($stmt->execute()) {
                    $syllabus_id = $conn->insert_id;
                    echo json_encode(['status' => 'success', 'syllabus' => $syllabus_content, 'syllabus_id' => $syllabus_id]);
                } else {
                    echo json_encode(['status' => 'success', 'syllabus' => $syllabus_content, 'db_error' => $stmt->error]);
                }
                
                $stmt->close();
            } else {
                // Return syllabus without saving to database
                echo json_encode(['status' => 'success', 'syllabus' => $syllabus_content]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid response from API.']);
        }
    }

    curl_close($ch);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
}
?>
