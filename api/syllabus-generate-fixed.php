<?php
// Required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get database connection
require_once 'c.php';

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Check if user is authenticated using session
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("success" => false, "message" => "Unauthorized - Please log in"));
    exit();
}

// Make sure data is not empty
if (
    !empty($data->prompt) &&
    !empty($data->class_id)
) {
    try {
        // DeepSeek API configuration
        $apiKey = getenv('DEEPSEEK_SECRET_KEY');; // DeepSeek API key
        $apiUrl = "https://api.deepseek.com/chat/completions";
        
        // Log API configuration for debugging
        error_log("Using DeepSeek API URL: {$apiUrl}");
        
        // Log the API request for debugging
        error_log("Generating syllabus with DeepSeek API for class ID: {$data->class_id}");

        // Construct a detailed prompt for generating a high-quality syllabus
        $systemPrompt = "You are an expert curriculum designer and educator with years of experience creating detailed, professional course syllabi. Your task is to create a comprehensive, well-structured syllabus that follows academic best practices and includes all essential components.

Your syllabus should include:
1. Course title and code
2. Instructor information
3. Term/semester details
4. Course description (detailed and engaging)
5. Learning objectives (specific and measurable)
6. Required materials and textbooks (with complete citation information)
7. Grading policy (detailed breakdown with percentages)
8. Weekly schedule (comprehensive 10-16 week outline with topics, readings, and assignments)
9. Course policies (attendance, late work, academic integrity, accommodations)

Format the syllabus professionally using markdown with clear section headers, bullet points for lists, and proper spacing. Make it comprehensive yet concise, and ensure all components are logically organized.";
    
        // Create the API request payload with enhanced parameters
        $payload = array(
            "model" => "deepseek-chat",
            "messages" => array(
                array(
                    "role" => "system",
                    "content" => $systemPrompt
                ),
                array(
                    "role" => "user",
                    "content" => $data->prompt
                )
            ),
            "temperature" => 0.7,  // Balanced creativity
            "max_tokens" => 4000,  // Allow for detailed output
            "top_p" => 0.95       // Slightly more focused response
        );
        
        // Log the payload for debugging
        error_log("API Payload: " . json_encode($payload));

        // Initialize cURL session
        $ch = curl_init($apiUrl);
        
        // Set cURL options with timeout
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set 30-second timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Set 10-second connection timeout
        
        // Execute cURL request with detailed logging
        error_log("Sending request to DeepSeek API...");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("DeepSeek API response code: {$httpCode}");
        
        // Log the raw response for debugging
        error_log("DeepSeek API raw response: {$response}");
        
        // Check for cURL errors
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
            error_log("DeepSeek API cURL error: {$curlError}");
            throw new Exception("DeepSeek API request failed: {$curlError}");
        }
        
        // Close cURL session
        curl_close($ch);
        
        // Parse the API response with error handling
        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg());
            error_log("Raw response: " . substr($response, 0, 1000) . "..."); // Log first 1000 chars
            throw new Exception("Failed to parse DeepSeek API response: " . json_last_error_msg());
        }
        
        // Log the decoded response for debugging
        error_log("DeepSeek API decoded response: " . json_encode($responseData));
        
        // Check for HTTP errors
        if ($httpCode != 200) {
            error_log("DeepSeek API HTTP error: {$httpCode}");
            if (isset($responseData['error'])) {
                $errorMsg = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Unknown error';
                $errorType = isset($responseData['error']['type']) ? $responseData['error']['type'] : 'Unknown type';
                error_log("DeepSeek API error: {$errorType} - {$errorMsg}");
                throw new Exception("DeepSeek API error: {$errorMsg}");
            }
            throw new Exception("DeepSeek API returned HTTP error: {$httpCode}");
        }
        
        // Check if the response contains the expected data
        if (!isset($responseData['choices'][0]['message']['content'])) {
            error_log("Unexpected API response structure: " . json_encode($responseData));
            
            // Check for specific error messages
            if (isset($responseData['error'])) {
                $errorMsg = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Unknown error';
                $errorType = isset($responseData['error']['type']) ? $responseData['error']['type'] : 'Unknown type';
                error_log("DeepSeek API error: {$errorType} - {$errorMsg}");
                throw new Exception("DeepSeek API error: {$errorMsg}");
            }
            
            throw new Exception("Unexpected response structure from DeepSeek API");
        }
        
        // Extract the generated syllabus content
        $syllabusContent = $responseData['choices'][0]['message']['content'];
        error_log("Successfully generated syllabus content of length: " . strlen($syllabusContent));
        
        // Process the syllabus content to extract structured data
        $syllabusData = processGeneratedSyllabus($syllabusContent);
        
        // Return success response with the generated syllabus
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Syllabus generated successfully",
            "content" => $syllabusContent,
            "data" => $syllabusData,
            "model" => isset($responseData['model']) ? $responseData['model'] : "deepseek-chat"
        ));
        
    } catch (Exception $e) {
        error_log("Exception in syllabus generation: " . $e->getMessage());
        
        // Log the error for debugging
        error_log("DeepSeek API error: " . $e->getMessage());
        
        // Get class name from the database using class_id
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
        
        // Fallback solution: Return a template syllabus
        $templateSyllabus = "# {$class_name} Syllabus

## Course Information
- **Course Title:** {$class_name}
- **Instructor:** " . (isset($_SESSION['name']) ? $_SESSION['name'] : 'Instructor') . "
- **Term:** Current Term

## Course Description
This course provides students with a comprehensive understanding of the subject matter, focusing on key concepts and practical applications.

## Learning Objectives
- Understand fundamental principles of the subject
- Develop critical thinking and analytical skills
- Apply theoretical knowledge to practical scenarios
- Demonstrate proficiency in subject-specific techniques

## Required Materials
- Primary textbook (details to be provided)
- Additional readings as assigned
- Access to online resources

## Grading Policy
- Assignments: 30%
- Quizzes: 20%
- Midterm Exam: 20%
- Final Exam: 30%

## Weekly Schedule
### Week 1-2: Introduction to Key Concepts
### Week 3-4: Fundamental Principles
### Week 5-6: Advanced Topics
### Week 7-8: Practical Applications
### Week 9-10: Case Studies
### Week 11-12: Final Projects and Review

## Course Policies
- **Attendance:** Regular attendance is expected
- **Late Work:** Assignments submitted late will incur a penalty
- **Academic Integrity:** Plagiarism and cheating will not be tolerated
- **Accommodations:** Students requiring accommodations should contact the instructor";
        
        // Process the template syllabus
        $syllabusData = processGeneratedSyllabus($templateSyllabus);
        
        // Return the fallback template
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Syllabus generated using template (API unavailable)",
            "content" => $templateSyllabus,
            "data" => $syllabusData
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => "Unable to generate syllabus. Data is incomplete.",
        "required" => array("prompt", "class_id"),
        "received" => json_encode($data)
    ));
}

// Process the raw generated syllabus text into structured data
// 
// @param string $content The raw syllabus content from the AI
// @return array Structured syllabus data
function processGeneratedSyllabus($content) {
    // Initialize structured data
    $syllabusData = array(
        "title" => "",
        "instructor" => "",
        "term" => "",
        "courseDescription" => "",
        "learningObjectives" => array(),
        "requiredMaterials" => array(),
        "gradingPolicy" => array(),
        "weeklySchedule" => array(),
        "policies" => array(
            "attendance" => "",
            "lateWork" => "",
            "academicIntegrity" => "",
            "accommodations" => ""
        )
    );
    
    // Extract title (look for main heading)
    if (preg_match('/^#\s*(.*?)(\n|$)/m', $content, $matches)) {
        $syllabusData["title"] = trim($matches[1]);
    }
    
    // Extract instructor (look for "Instructor" or "Professor")
    if (preg_match('/([Ii]nstructor|[Pp]rofessor):?\s*(.*?)(\n|$)/', $content, $matches)) {
        $syllabusData["instructor"] = trim($matches[2]);
    }
    
    // Extract term (look for "Term", "Semester", or "Quarter")
    if (preg_match('/([Tt]erm|[Ss]emester|[Qq]uarter):?\s*(.*?)(\n|$)/', $content, $matches)) {
        $syllabusData["term"] = trim($matches[2]);
    }
    
    // Extract course description
    if (preg_match('/[Cc]ourse\s*[Dd]escription:?\s*(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $syllabusData["courseDescription"] = trim($matches[1]);
    }
    
    // Extract learning objectives
    if (preg_match('/[Ll]earning\s*[Oo]bjectives:?.*?\n(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $objectivesText = $matches[1];
        // Look for bullet points or numbered lists
        preg_match_all('/[\*\-\d\.]\s*(.*?)(\n|$)/', $objectivesText, $objectiveMatches);
        if (!empty($objectiveMatches[1])) {
            $syllabusData["learningObjectives"] = array_map('trim', $objectiveMatches[1]);
        }
    }
    
    // Extract required materials
    if (preg_match('/([Rr]equired\s*[Mm]aterials|[Tt]extbooks):?.*?\n(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $materialsText = $matches[2];
        preg_match_all('/[\*\-\d\.]\s*(.*?)(\n|$)/', $materialsText, $materialMatches);
        if (!empty($materialMatches[1])) {
            foreach ($materialMatches[1] as $material) {
                $syllabusData["requiredMaterials"][] = array(
                    "title" => trim($material),
                    "author" => "",
                    "publisher" => "",
                    "year" => "",
                    "required" => true
                );
            }
        }
    }
    
    // Extract grading policy
    if (preg_match('/[Gg]rading(\s*[Pp]olicy)?:?.*?\n(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $gradingText = $matches[2];
        preg_match_all('/[\*\-\d\.]\s*(.*?):\s*(\d+)%/i', $gradingText, $gradingMatches);
        if (!empty($gradingMatches[1])) {
            for ($i = 0; $i < count($gradingMatches[1]); $i++) {
                $component = trim($gradingMatches[1][$i]);
                $percentage = intval($gradingMatches[2][$i]);
                $syllabusData["gradingPolicy"][$component] = array(
                    "percentage" => $percentage,
                    "description" => $component
                );
            }
        }
    }
    
    // Extract weekly schedule
    if (preg_match('/([Ww]eekly\s*[Ss]chedule|[Cc]ourse\s*[Ss]chedule):?.*?\n(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $scheduleText = $matches[2];
        preg_match_all('/[Ww]eek\s*(\d+)[\s\:]*([^\n]*?)(\n|$)/', $scheduleText, $weekMatches);
        if (!empty($weekMatches[1])) {
            for ($i = 0; $i < count($weekMatches[1]); $i++) {
                $weekNumber = intval($weekMatches[1][$i]);
                $topic = trim($weekMatches[2][$i]);
                $syllabusData["weeklySchedule"][] = array(
                    "week" => $weekNumber,
                    "topic" => $topic,
                    "readings" => "",
                    "assignments" => ""
                );
            }
        }
    }
    
    // Extract policies
    if (preg_match('/[Aa]ttendance\s*[Pp]olicy:?\s*(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $syllabusData["policies"]["attendance"] = trim($matches[1]);
    }
    
    if (preg_match('/([Ll]ate\s*[Ww]ork|[Ll]ate\s*[Aa]ssignments)\s*[Pp]olicy:?\s*(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $syllabusData["policies"]["lateWork"] = trim($matches[2]);
    }
    
    if (preg_match('/([Aa]cademic\s*[Ii]ntegrity|[Pp]lagiarism)\s*[Pp]olicy:?\s*(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $syllabusData["policies"]["academicIntegrity"] = trim($matches[2]);
    }
    
    if (preg_match('/([Aa]ccommodations|[Dd]isability)\s*[Pp]olicy:?\s*(.*?)(\n\n|\n#|\n\*\*[A-Z]|$)/s', $content, $matches)) {
        $syllabusData["policies"]["accommodations"] = trim($matches[2]);
    }
    
    return $syllabusData;
}
?>
