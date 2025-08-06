<?php
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
        
        // Create a template syllabus
        $templateSyllabus = "# " . $class_name . " Syllabus

## Course Information
- **Course Title:** " . $class_name . "
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
        $syllabusData = array(
            "title" => $class_name,
            "instructor" => isset($_SESSION['name']) ? $_SESSION['name'] : 'Instructor',
            "term" => "Current Term",
            "courseDescription" => "This course provides students with a comprehensive understanding of the subject matter, focusing on key concepts and practical applications.",
            "learningObjectives" => array(
                "Understand fundamental principles of the subject",
                "Develop critical thinking and analytical skills",
                "Apply theoretical knowledge to practical scenarios",
                "Demonstrate proficiency in subject-specific techniques"
            ),
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
            "gradingPolicy" => array(
                "Assignments" => array(
                    "percentage" => 30,
                    "description" => "Assignments"
                ),
                "Quizzes" => array(
                    "percentage" => 20,
                    "description" => "Quizzes"
                ),
                "Midterm Exam" => array(
                    "percentage" => 20,
                    "description" => "Midterm Exam"
                ),
                "Final Exam" => array(
                    "percentage" => 30,
                    "description" => "Final Exam"
                )
            ),
            "weeklySchedule" => array(
                array(
                    "week" => 1,
                    "topic" => "Introduction to Key Concepts",
                    "readings" => "",
                    "assignments" => ""
                ),
                array(
                    "week" => 3,
                    "topic" => "Fundamental Principles",
                    "readings" => "",
                    "assignments" => ""
                ),
                array(
                    "week" => 5,
                    "topic" => "Advanced Topics",
                    "readings" => "",
                    "assignments" => ""
                ),
                array(
                    "week" => 7,
                    "topic" => "Practical Applications",
                    "readings" => "",
                    "assignments" => ""
                ),
                array(
                    "week" => 9,
                    "topic" => "Case Studies",
                    "readings" => "",
                    "assignments" => ""
                ),
                array(
                    "week" => 11,
                    "topic" => "Final Projects and Review",
                    "readings" => "",
                    "assignments" => ""
                )
            ),
            "policies" => array(
                "attendance" => "Regular attendance is expected",
                "lateWork" => "Assignments submitted late will incur a penalty",
                "academicIntegrity" => "Plagiarism and cheating will not be tolerated",
                "accommodations" => "Students requiring accommodations should contact the instructor"
            )
        );
        
        // Return the template
        http_response_code(200);
        echo json_encode(array(
            "success" => true,
            "message" => "Syllabus generated successfully",
            "content" => $templateSyllabus,
            "data" => $syllabusData
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
?>
