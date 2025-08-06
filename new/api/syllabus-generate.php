<?php
// Show errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../load_env.php';

// Increase PHP execution time limit
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);

// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once 'c.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if user is authenticated using session
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "Unauthorized - Please log in"));
    exit();
}

// Enable more detailed error logging
error_log("Syllabus generation request started at: " . date('Y-m-d H:i:s'));
error_log("Request data: " . json_encode($data));
error_log("Session user_id: " . $_SESSION['user_id']);

// Validate required data
if (empty($data) || !isset($data->class_id)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Missing required data: class_id is required",
        "received_data" => $data
    ));
    exit();
}

// Make sure data is not empty
if (!empty($data->class_id)) {
    try {
        // Get class name from the database
        $class_name = "Course";
        try {
            $stmt = $conn->prepare("SELECT name FROM classes WHERE cid = ?");
            $stmt->bind_param("i", $data->class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $class_name = $row['name'];
            }
        } catch (Exception $ex) {
            error_log("Error getting class name: " . $ex->getMessage());
        }
        
        // Get the prompt from the request or use a default one
        $prompt = isset($data->prompt) ? $data->prompt : "Generate a comprehensive syllabus for a course titled '{$class_name}'";
        
        // Set your OpenAI API key
        $apiKey = getenv('OPEN_SECRET_KEY'); // Replace with your OpenAI API key
        
        // Use error_log instead of file_put_contents for logging
        error_log("=== Syllabus Generation Started ===\n");
        error_log("Time: " . date('Y-m-d H:i:s'));
        error_log("Class ID: {$data->class_id}");
        error_log("Class Name: {$class_name}");
        error_log("Prompt: {$prompt}");
        
        // Prepare API request data - Model after rubric_create.php
        $apiData = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that creates detailed course syllabi for educators. Always format your response with clear markdown sections using ## headers. Use consistent section names: Course Description, Learning Objectives, Required Materials, Grading Policy, Weekly Schedule, and Course Policies.'
                ],
                [
                    'role' => 'user',
                    'content' => "Generate a comprehensive syllabus for the following course:\nCourse Title: {$class_name}\n{$prompt}\n\nPlease structure your response with these exact sections:\n## Course Description\n## Learning Objectives\n(List 4-6 objectives using bullet points starting with -)\n## Required Materials\n## Grading Policy\n(List items with percentages like: - Assignments: 30%)\n## Weekly Schedule\n(Format as: ### Week 1: Topic Name)\n## Course Policies\n(Include subsections for Attendance Policy, Late Work Policy, Academic Integrity Policy, and Accommodations Policy)"
                ]
            ]
        ];
        
        // Start timer
        $start_time = microtime(true);
        error_log("Request started at: " . date('Y-m-d H:i:s'));
        
        // Initialize cURL
        $ch = curl_init();
        
        // Set cURL options - Model after rubric_create.php
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Enable SSL verification
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiData));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds timeout
        
        // Log request using error_log
        error_log("API Request: " . json_encode($apiData));
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Check if response is valid
        if (empty($response)) {
            $api_error = "Empty response from DeepSeek API";
            error_log($api_error);
        }
        
        // End timer
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        error_log("Request completed at: " . date('Y-m-d H:i:s'));
        error_log("Execution time: " . $execution_time . " seconds");
        
        // Log response using error_log
        error_log("API Response Code: {$httpCode}");
        // Don't log the full response as it might be too large
        error_log("API Response received (length: " . strlen($response) . " bytes)");
        
        // Initialize generated syllabus variable
        $generated_syllabus = null;
        $api_error = null;
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $api_error = "cURL error: " . curl_error($ch);
            error_log($api_error);
        } else if ($httpCode !== 200) {
            $api_error = "HTTP Error: " . $httpCode;
            error_log($api_error);
        } else {
            // Process the response - check if it contains HTML tags which might cause JSON parsing issues
            if (strpos($response, '<') !== false) {
                // Log the problematic response
                error_log("Response contains HTML tags which might cause JSON parsing issues");
                
                // Try to clean up the response - remove any HTML tags
                $cleaned_response = preg_replace('/<[^>]*>/', '', $response);
                error_log("Cleaned response (length: " . strlen($cleaned_response) . " bytes)");
                
                // Try parsing the cleaned response
                $result = json_decode($cleaned_response, true);
            } else {
                // Normal JSON parsing
                $result = json_decode($response, true);
            }
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $api_error = "JSON parsing error: " . json_last_error_msg() . " - Response: " . substr($response, 0, 100) . "...";
                error_log($api_error);
            } else {
                error_log("Response successfully decoded as JSON");
                
                if (isset($result['choices'][0]['message']['content'])) {
                    $generated_syllabus = $result['choices'][0]['message']['content'];
                    error_log("Successfully extracted content from API response");
                } else {
                    $api_error = "Error: Invalid response format from OpenAI API";
                    error_log($api_error);
                }
            }
        }
        
        // Close cURL
        curl_close($ch);
        
        // If we couldn't generate a syllabus with the API, use the template
        if (!isset($generated_syllabus)) {
            $generated_syllabus = generateTemplateSyllabus($class_name);
            $api_used = false;
        } else {
            $api_used = true;
            error_log("Using AI-generated content: " . substr($generated_syllabus, 0, 100) . "...");
        }
        
        // Process the syllabus - attempt to extract structured data from AI content
        $courseDescription = "This course provides students with a comprehensive understanding of the subject matter, focusing on key concepts and practical applications.";
        $learningObjectives = array(
            "Understand fundamental principles of the subject",
            "Develop critical thinking and analytical skills",
            "Apply theoretical knowledge to practical scenarios",
            "Demonstrate proficiency in subject-specific techniques"
        );
        
        // Try to extract course description from AI content
        if ($api_used && preg_match('/## Course Description\s*\n([^#]+)/s', $generated_syllabus, $matches)) {
            $courseDescription = trim($matches[1]);
            error_log("Extracted course description: " . substr($courseDescription, 0, 100) . "...");
        }
        
        // Try to extract learning objectives from AI content
        $aiObjectives = array();
        if ($api_used && preg_match('/## Learning Objectives\s*\n([^#]+)/s', $generated_syllabus, $matches)) {
            $objectivesText = $matches[1];
            preg_match_all('/- ([^\n]+)/', $objectivesText, $objectiveMatches);
            if (!empty($objectiveMatches[1])) {
                $aiObjectives = array_map('trim', $objectiveMatches[1]);
                error_log("Extracted " . count($aiObjectives) . " learning objectives");
            }
        }
        
        // Use AI objectives if found, otherwise use defaults
        $finalObjectives = !empty($aiObjectives) ? $aiObjectives : $learningObjectives;
        
        // Try to extract grading policy from AI content
        $defaultGradingPolicy = array(
            "Assignments" => array("percentage" => 30, "description" => "Assignments"),
            "Quizzes" => array("percentage" => 20, "description" => "Quizzes"),
            "Midterm Exam" => array("percentage" => 20, "description" => "Midterm Exam"),
            "Final Exam" => array("percentage" => 30, "description" => "Final Exam")
        );
        
        $gradingPolicy = $defaultGradingPolicy;
        if ($api_used && preg_match('/## Grading Policy\s*\n([^#]+)/s', $generated_syllabus, $matches)) {
            $gradingText = $matches[1];
            preg_match_all('/- ([^:]+):\s*(\d+)%/', $gradingText, $gradingMatches);
            
            if (!empty($gradingMatches[1]) && !empty($gradingMatches[2])) {
                $customGradingPolicy = array();
                for ($i = 0; $i < count($gradingMatches[1]); $i++) {
                    $category = trim($gradingMatches[1][$i]);
                    $percentage = intval($gradingMatches[2][$i]);
                    $customGradingPolicy[$category] = array(
                        "percentage" => $percentage,
                        "description" => $category
                    );
                }
                
                if (!empty($customGradingPolicy)) {
                    $gradingPolicy = $customGradingPolicy;
                    error_log("Extracted " . count($gradingPolicy) . " grading categories");
                }
            }
        }
        
        // Try to extract weekly schedule from AI content
        $defaultWeeklySchedule = array(
            array("week" => 1, "topic" => "Introduction to Key Concepts", "readings" => "", "assignments" => ""),
            array("week" => 3, "topic" => "Fundamental Principles", "readings" => "", "assignments" => ""),
            array("week" => 5, "topic" => "Advanced Topics", "readings" => "", "assignments" => ""),
            array("week" => 7, "topic" => "Practical Applications", "readings" => "", "assignments" => ""),
            array("week" => 9, "topic" => "Case Studies", "readings" => "", "assignments" => ""),
            array("week" => 11, "topic" => "Final Projects and Review", "readings" => "", "assignments" => "")
        );
        
        $weeklySchedule = $defaultWeeklySchedule;
        if ($api_used && preg_match('/## Weekly Schedule\s*\n([^#]+)/s', $generated_syllabus, $matches)) {
            $scheduleText = $matches[1];
            preg_match_all('/### Week (\d+)[^:]*:\s*([^\n]+)/', $scheduleText, $scheduleMatches);
            
            if (!empty($scheduleMatches[1]) && !empty($scheduleMatches[2])) {
                $customSchedule = array();
                for ($i = 0; $i < count($scheduleMatches[1]); $i++) {
                    $week = intval($scheduleMatches[1][$i]);
                    $topic = trim($scheduleMatches[2][$i]);
                    $customSchedule[] = array(
                        "week" => $week,
                        "topic" => $topic,
                        "readings" => "",
                        "assignments" => ""
                    );
                }
                
                if (!empty($customSchedule)) {
                    $weeklySchedule = $customSchedule;
                    error_log("Extracted " . count($weeklySchedule) . " weekly schedule items");
                }
            }
        }
        
        // Create the syllabus data structure with extracted values
        $syllabusData = array(
            "title" => $class_name,
            "instructor" => "Instructor Name",
            "term" => "Current Term",
            "courseDescription" => $courseDescription,
            "learningObjectives" => $finalObjectives,
            "requiredMaterials" => array(
                array(
                    "title" => "Primary textbook (details to be provided)",
                    "author" => "",
                    "publisher" => "",
                    "year" => "",
                    "required" => true
                ),
                array(
                    "title" => "Additional readings as assigned",
                    "author" => "",
                    "publisher" => "",
                    "year" => "",
                    "required" => true
                )
            ),
            "gradingPolicy" => $gradingPolicy,
            "weeklySchedule" => $weeklySchedule,
            "policies" => array(
                "attendance" => "Regular attendance is expected",
                "lateWork" => "Assignments submitted late will incur a penalty",
                "academicIntegrity" => "Plagiarism and cheating will not be tolerated",
                "accommodations" => "Students requiring accommodations should contact the instructor"
            )
        );
        
        // Return the generated or template syllabus
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Syllabus generated successfully",
            "content" => $generated_syllabus,
            "data" => $syllabusData,
            "api_used" => $api_used
        ));
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to generate syllabus. Data is incomplete.",
        "required" => array("class_id"),
        "received" => json_encode($data)
    ));
}

// Add this function at the end of the file, before the closing ?>
function generateTemplateSyllabus($class_name) {
    return "## Course Description\nThis course provides students with a comprehensive understanding of {$class_name}, focusing on key concepts and practical applications. Students will engage with course material through lectures, discussions, assignments, and hands-on activities.\n\n## Learning Objectives\nBy the end of this course, students will be able to:\n- Understand fundamental principles and concepts\n- Develop critical thinking and analytical skills\n- Apply theoretical knowledge to practical scenarios\n- Demonstrate proficiency in subject-specific techniques\n- Communicate effectively about course topics\n- Work collaboratively on course projects\n\n## Required Materials\n- Primary textbook (details to be provided)\n- Additional readings as assigned\n- Access to course learning management system\n\n## Grading Policy\n- Assignments: 30%\n- Quizzes: 20%\n- Midterm Exam: 20%\n- Final Exam: 30%\n\n## Weekly Schedule\n### Week 1: Introduction and Course Overview\n### Week 2: Fundamental Concepts\n### Week 3: Core Principles\n### Week 4: Advanced Topics\n### Week 5: Practical Applications\n### Week 6: Case Studies\n### Week 7: Review and Assessment\n\n## Course Policies\n\n### Attendance Policy\nRegular attendance is expected. Students who miss more than two classes may be withdrawn from the course.\n\n### Late Work Policy\nAssignments submitted late will incur a penalty of 10% per day unless prior arrangements are made.\n\n### Academic Integrity Policy\nPlagiarism and cheating will not be tolerated. All work must be original and properly cited.\n\n### Accommodations Policy\nStudents requiring accommodations should contact the instructor within the first two weeks of class.";
}
?>
