<?php
// Check if class_id is provided
if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    http_response_code(400);
    echo "Error: Missing class ID";
    exit();
}

$class_id = intval($_GET['class_id']);

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session first, before any output or includes
session_start();

// Include database connection
require 'api/c.php';

// Get class details
try {
    // Create database connection using the variables from c.php
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get class details
    $stmt = $conn->prepare("SELECT * FROM classes WHERE cid = ? AND owner = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $class_id, $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(403);
        echo "Error: Class not found or you don't have permission to access it";
        exit();
    }
    
    $class = $result->fetch_assoc();
    $class_name = htmlspecialchars($class['name']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Syllabus | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Add jQuery and other required libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Albert Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #28a745;
            text-decoration: none;
            font-weight: bold;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
            flex: 1;
            text-align: center;
            font-weight: bold;
        }
        
        .tab.active {
            background-color: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        
        .tab-content {
            display: none;
            background-color: white;
            padding: 20px;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content.active {
            display: block;
        }
        
        .ai-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: #19A37E;
        }
        
        .ai-header i {
            margin-right: 10px;
            font-size: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 100px;
            font-family: 'Albert Sans', sans-serif;
        }
        
        .info-box {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .course-details {
            background-color: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .btn-primary {
            background-color: #19A37E;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #ddd;
        }
        
        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .syllabus-preview {
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .syllabus-preview h1 {
            color: #28a745;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .syllabus-preview h2 {
            color: #2c3e50;
            margin-top: 20px;
        }
        
        .syllabus-section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    
    <div id="mainContent" class="container">
        <a href="view_assignments.php?id=<?php echo $class_id; ?>" class="back-link">&laquo; Back to Class</a>
        
        <h1>Generate Syllabus for <?php echo $class_name; ?></h1>
        
        <div class="tabs">
            <div class="tab active" data-tab="generate">Generate</div>
            <div class="tab" data-tab="edit">Edit</div>
            <div class="tab" data-tab="preview">Preview</div>
        </div>
        
        <div id="generate" class="tab-content active" style="background-color: #fff!important;">
            <div class="ai-header">
                <i class="fas fa-robot"></i>
                <h2>AI-Assisted Syllabus Generation</h2>
            </div>
            <p>Let our AI help you create a comprehensive syllabus based on your course details.</p>
            
            <form id="generationForm">
                <input type="hidden" id="classId" name="classId" value="<?php echo $class_id; ?>">
                <input type="hidden" id="className" name="className" value="<?php echo $class_name; ?>">
                
                <div class="form-group">
                    <label for="prompt">Generation Prompt</label>
                    <textarea id="prompt" name="prompt" rows="5" class="form-control" placeholder="Create a comprehensive syllabus for...">Create a comprehensive syllabus for <?php echo $class_name; ?> class. Include course description, learning objectives, required materials, grading policy, weekly schedule, and course policies.</textarea>
                    <p class="form-hint">This prompt will guide the AI in generating your syllabus. Feel free to modify it.</p>
                </div>
                
                <div class="form-group">
                    <label for="additionalInfo">Additional Information (Optional)</label>
                    <textarea id="additionalInfo" name="additionalInfo" rows="4" class="form-control" placeholder="Specific textbooks, grading policies, or other details you'd like to include..."></textarea>
                </div>
                
                <div class="course-details">
                    <h3>Course Details (from previous step)</h3>
                    <p><strong>Course Name:</strong> <?php echo $class_name; ?></p>
                    <p><strong>Instructor:</strong> <?php echo $_SESSION['name'] ?? 'Not specified'; ?></p>
                </div>
                
                <div class="actions">
                    <button type="button" id="backToDetailsBtn" class="btn btn-primary">Back to Details</button>
                    <button type="button" id="generateSyllabusBtn" class="btn btn-primary">Generate Syllabus</button>
                </div>
            </form>
        </div>
        
        <div id="edit" class="tab-content" style="background-color: #fff!important;">
            <h2>Edit Your Syllabus</h2>
            <p>Review and edit the AI-generated syllabus to fit your specific needs.</p>
            
            <form id="editForm">
                <div class="form-group">
                    <label for="courseTitle">Course Title</label>
                    <input type="text" id="courseTitle" name="courseTitle" value="<?php echo $class_name; ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="instructorInfo">Instructor Information</label>
                    <textarea id="instructorInfo" name="instructorInfo" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="courseDescription">Course Description</label>
                    <textarea id="courseDescription" name="courseDescription" rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="learningObjectives">Learning Objectives</label>
                    <textarea id="learningObjectives" name="learningObjectives" rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="requiredMaterials">Required Materials</label>
                    <textarea id="requiredMaterials" name="requiredMaterials" rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="gradingPolicy">Grading Policy</label>
                    <textarea id="gradingPolicy" name="gradingPolicy" rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="courseSchedule">Course Schedule</label>
                    <textarea id="courseSchedule" name="courseSchedule" rows="10"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="policies">Course Policies</label>
                    <textarea id="policies" name="policies" rows="5"></textarea>
                </div>
                
                <div class="actions">
                    <button type="button" id="backToGenerateBtn" class="btn btn-primary">Back to Generate</button>
                    <button type="button" id="previewSyllabusBtn" class="btn btn-primary">Preview Syllabus</button>
                </div>
            </form>
        </div>
        
        <div id="preview" class="tab-content" style="background-color: #fff!important;">
            <h2>Preview Your Syllabus</h2>
            <p>Review your syllabus before saving it to your class.</p>
            
            <div id="syllabusPreview" class="syllabus-preview">
                <!-- Syllabus content will be inserted here -->
            </div>
            
            <div class="actions">
                <button type="button" id="backToEditBtn" class="btn btn-primary">Back to Edit</button>
                <div>
                    <button type="button" id="downloadPdfBtn" class="btn btn-secondary">Download PDF</button>
                    <button type="button" id="saveSyllabusBtn" class="btn btn-primary">Save & Attach to Class</button>
                </div>
            </div>
        </div>
            
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script>
        // Tab functionality
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                });
                
                // Show the selected tab content
                document.getElementById(tabId).classList.add('active');
                tab.classList.add('active');
                
                // If switching to preview tab, generate preview
                if (tabId === 'preview') {
                    generateSyllabusPreview();
                }
            });
        });
        
        // Function to convert rubric HTML to syllabus format
        function convertRubricToSyllabus(htmlContent, className) {
            console.log('Converting rubric HTML to syllabus format');
            
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlContent;
            
            // Extract text content from the HTML
            const textContent = tempDiv.textContent || tempDiv.innerText;
            
            // Create a markdown syllabus from the content
            const syllabus = `# ${className} Syllabus

## Course Information
- **Course Title:** ${className}
- **Instructor:** Instructor Name
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
- **Accommodations:** Students requiring accommodations should contact the instructor`;
            
            return syllabus;
        }
        
        // Function to programmatically switch tabs
        function switchTab(tabId) {
            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to the selected tab
            document.querySelector(`.tab[data-tab="${tabId}"]`).classList.add('active');
            
            // If switching to preview tab, generate preview
            if (tabId === 'preview') {
                generateSyllabusPreview();
            }
        }
        
        // Global variables
        const classId = document.getElementById('classId').value;
        const className = document.getElementById('className').value;
        let syllabusContent = ''; // Will store the formatted syllabus content
        
        // Button event listeners
        document.getElementById('backToDetailsBtn').addEventListener('click', () => {
            switchTab('generate');
        });
        
        document.getElementById('generateSyllabusBtn').addEventListener('click', function() {
            // Show loading indicator
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            this.disabled = true;
            
            // Get form data
            const prompt = document.getElementById('prompt').value;
            const additionalInfo = document.getElementById('additionalInfo').value;
            
            // Combine prompt with additional info
            let fullPrompt = prompt;
            if (additionalInfo.trim()) {
                fullPrompt += '\n\nAdditional Information: ' + additionalInfo;
            }
            
            // Add debug timestamp
            console.log('Starting syllabus generation at:', new Date().toISOString());
            
            // Create request payload
            const requestPayload = {
                prompt: fullPrompt,
                class_id: parseInt(classId)
            };
            
            console.log('Request payload:', requestPayload);
            
            // Add a status message to show progress
            const generateBtn = document.getElementById('generateSyllabusBtn');
            let dots = 0;
            const loadingInterval = setInterval(() => {
                dots = (dots + 1) % 4;
                const dotString = '.'.repeat(dots);
                generateBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Generating${dotString.padEnd(3, ' ')}`;
            }, 500);
            
            // Call the appropriate API endpoint with increased timeout
            $.ajax({
                url: 'api/syllabus-generate.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(requestPayload),
                timeout: 180000, // 3 minutes timeout
                beforeSend: function() {
                    console.log('AJAX request starting...');
                },
                success: function(response) {
                    // Clear the loading interval
                    clearInterval(loadingInterval);
                    
                    console.log('AJAX success response received at:', new Date().toISOString());
                    console.log('Response:', response);
                    
                    if (response.success) {
                        // Handle syllabus-generate.php response (JSON)
                        console.log('Syllabus generated successfully, API used:', response.api_used);
                        // Populate the edit form with the generated syllabus data
                        populateEditForm(response.data, response.content);
                        
                        // Switch to edit tab
                        console.log('Switching to edit tab');
                        switchTab('edit');
                    } else {
                        console.error('Error in response:', response.message || 'Unknown error');
                        alert('Error generating syllabus: ' + (response.message || 'Unknown error'));
                    }
                    
                    // Reset button
                    document.getElementById('generateSyllabusBtn').innerHTML = 'Generate Syllabus';
                    document.getElementById('generateSyllabusBtn').disabled = false;
                },
                error: function(xhr, status, error) {
                    // Clear the loading interval
                    clearInterval(loadingInterval);
                    
                    console.error('AJAX error at:', new Date().toISOString());
                    console.error('Status:', status);
                    console.error('Error:', error);
                    
                    try {
                        const responseText = xhr.responseText;
                        console.error('Error response text:', responseText);
                        
                        try {
                            const jsonResponse = JSON.parse(responseText);
                            console.error('Parsed error response:', jsonResponse);
                        } catch (e) {
                            console.error('Response is not valid JSON');
                        }
                    } catch (e) {
                        console.error('Could not access response text:', e);
                    }
                    
                    alert('An error occurred while generating the syllabus: ' + error);
                    
                    // Reset button
                    document.getElementById('generateSyllabusBtn').innerHTML = 'Generate Syllabus';
                    document.getElementById('generateSyllabusBtn').disabled = false;
                }
            });
        });
        
        document.getElementById('backToGenerateBtn').addEventListener('click', () => {
            switchTab('generate');
        });
        
        document.getElementById('previewSyllabusBtn').addEventListener('click', () => {
            switchTab('preview');
        });
        
        document.getElementById('backToEditBtn').addEventListener('click', () => {
            switchTab('edit');
        });
        
        document.getElementById('downloadPdfBtn').addEventListener('click', downloadPDF);
        document.getElementById('saveSyllabusBtn').addEventListener('click', saveSyllabusToClass);
        
        // Auto-save functionality for edit form
        const editFormInputs = document.getElementById('editForm').querySelectorAll('input, textarea');
        editFormInputs.forEach(input => {
            input.addEventListener('change', saveToLocalStorage);
            input.addEventListener('keyup', saveToLocalStorage);
        });
        
        // Load saved data if available
        loadFromLocalStorage();
        
        // Functions
        function simulateAIGeneration() {
            const generationPrompt = document.getElementById('generationPrompt').value;
            const additionalInfo = document.getElementById('additionalInfo').value;
            
            // Show loading state
            document.getElementById('generateSyllabusBtn').textContent = 'Generating...';
            document.getElementById('generateSyllabusBtn').disabled = true;
            
            // Simulate API call delay
            setTimeout(() => {
                // Generate content based on class name
                const courseTitle = document.getElementById('courseTitle');
                const instructorInfo = document.getElementById('instructorInfo');
                const courseDescription = document.getElementById('courseDescription');
                const learningObjectives = document.getElementById('learningObjectives');
                const requiredMaterials = document.getElementById('requiredMaterials');
                const gradingPolicy = document.getElementById('gradingPolicy');
                const courseSchedule = document.getElementById('courseSchedule');
                const policies = document.getElementById('policies');
                
                // Fill in the edit form with "AI-generated" content
                courseTitle.value = className;
                instructorInfo.value = `${document.querySelector('.course-details p:nth-child(2)').textContent.replace('Instructor:', '').trim()}\nEmail: ${document.querySelector('.course-details p:nth-child(2)').textContent.replace('Instructor:', '').trim()}@example.com\nOffice Hours: Monday and Wednesday, 2:00 PM - 4:00 PM`;
                
                // Save to localStorage
                saveToLocalStorage();
                
                // Generate preview
                generateSyllabusPreview();
            }, 2000);
        }
        
        function populateEditForm(syllabusData, rawContent) {
            // Store the raw content for later use
            syllabusContent = rawContent;
            
            // Get form elements
            const courseTitle = document.getElementById('courseTitle');
            const instructorInfo = document.getElementById('instructorInfo');
            const courseDescription = document.getElementById('courseDescription');
            const learningObjectives = document.getElementById('learningObjectives');
            const requiredMaterials = document.getElementById('requiredMaterials');
            const gradingPolicy = document.getElementById('gradingPolicy');
            const courseSchedule = document.getElementById('courseSchedule');
            const policies = document.getElementById('policies');
            
            // Fill in the edit form with AI-generated content
            courseTitle.value = syllabusData.title || className;
            
            // Format instructor info
            let instructorText = syllabusData.instructor || document.querySelector('.course-details p:nth-child(2)').textContent.replace('Instructor:', '').trim();
            instructorText += '\nEmail: ' + (syllabusData.instructorEmail || instructorText + '@example.com');
            instructorText += '\nOffice Hours: ' + (syllabusData.officeHours || 'Monday and Wednesday, 2:00 PM - 4:00 PM');
            instructorInfo.value = instructorText;
            
            // Course description
            courseDescription.value = syllabusData.courseDescription || '';
            
            // Learning objectives
            if (syllabusData.learningObjectives && Array.isArray(syllabusData.learningObjectives)) {
                learningObjectives.value = syllabusData.learningObjectives.map((obj, index) => `${index + 1}. ${obj}`).join('\n');
            } else {
                learningObjectives.value = '';
            }
            
            // Required materials
            if (syllabusData.requiredMaterials && Array.isArray(syllabusData.requiredMaterials)) {
                let materialsText = '';
                syllabusData.requiredMaterials.forEach(material => {
                    if (typeof material === 'object') {
                        materialsText += material.title || material;
                        if (material.author) materialsText += ` by ${material.author}`;
                        if (material.publisher) materialsText += ` (${material.publisher})`;
                        if (material.year) materialsText += ` ${material.year}`;
                        materialsText += '\n';
                    } else {
                        materialsText += material + '\n';
                    }
                });
                requiredMaterials.value = materialsText.trim();
            } else {
                requiredMaterials.value = '';
            }
            
            // Grading policy
            if (syllabusData.gradingPolicy && typeof syllabusData.gradingPolicy === 'object') {
                let gradingText = '';
                
                for (const [category, details] of Object.entries(syllabusData.gradingPolicy)) {
                    if (typeof details === 'object' && details.percentage) {
                        gradingText += `${category}: ${details.percentage}%\n`;
                    } else {
                        gradingText += `${category}: ${details}\n`;
                    }
                }
                
                gradingPolicy.value = gradingText.trim();
            } else {
                gradingPolicy.value = '';
            }
            
            // Course schedule
            if (syllabusData.weeklySchedule && Array.isArray(syllabusData.weeklySchedule)) {
                let scheduleText = '';
                syllabusData.weeklySchedule.forEach(week => {
                    scheduleText += `Week ${week.week}: ${week.topic}\n`;
                    if (week.readings) scheduleText += `Readings: ${week.readings}\n`;
                    if (week.assignments) scheduleText += `Assignments: ${week.assignments}\n`;
                    scheduleText += '\n';
                });
                
                courseSchedule.value = scheduleText.trim();
            } else {
                courseSchedule.value = '';
            }
            
            // Policies
            if (syllabusData.policies && typeof syllabusData.policies === 'object') {
                let policiesText = '';
                
                if (syllabusData.policies.attendance) {
                    policiesText += 'Attendance Policy:\n';
                    policiesText += syllabusData.policies.attendance + '\n\n';
                }
                
                if (syllabusData.policies.lateWork) {
                    policiesText += 'Late Work Policy:\n';
                    policiesText += syllabusData.policies.lateWork + '\n\n';
                }
                
                if (syllabusData.policies.academicIntegrity) {
                    policiesText += 'Academic Integrity Policy:\n';
                    policiesText += syllabusData.policies.academicIntegrity + '\n\n';
                }
                
                if (syllabusData.policies.accommodations) {
                    policiesText += 'Accommodations Policy:\n';
                    policiesText += syllabusData.policies.accommodations;
                }
                
                policies.value = policiesText.trim();
            } else {
                policies.value = '';
            }
            
            // Save to localStorage
            saveToLocalStorage();
            
            // Generate preview
            generateSyllabusPreview();
        }
        
        function generateSyllabusPreview() {
            const courseTitle = document.getElementById('courseTitle').value;
            const instructorInfo = document.getElementById('instructorInfo').value;
            const courseDescription = document.getElementById('courseDescription').value;
            const learningObjectives = document.getElementById('learningObjectives').value;
            const requiredMaterials = document.getElementById('requiredMaterials').value;
            const gradingPolicy = document.getElementById('gradingPolicy').value;
            const courseSchedule = document.getElementById('courseSchedule').value;
            const policies = document.getElementById('policies').value;
            
            // Format the content with proper HTML
            let html = `
                <h1>${courseTitle || className}</h1>
                
                <div class="syllabus-section">
                    <h2>Instructor Information</h2>
                    <p>${instructorInfo.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Course Description</h2>
                    <p>${courseDescription.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Learning Objectives</h2>
                    <p>${learningObjectives.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Required Materials</h2>
                    <p>${requiredMaterials.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Grading Policy</h2>
                    <p>${gradingPolicy.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Course Schedule</h2>
                    <p>${courseSchedule.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="syllabus-section">
                    <h2>Course Policies</h2>
                    <p>${policies.replace(/\n/g, '<br>')}</p>
                </div>
            `;
            
            // Insert the HTML into the preview div
            document.getElementById('syllabusPreview').innerHTML = html;
        }
        
        function downloadPDF() {
            const element = document.getElementById('syllabusPreview');
            const opt = {
                margin: 1,
                filename: `${className}_Syllabus.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
        
        function saveSyllabusToClass() {
            // Get the syllabus HTML content
            const syllabusContent = document.getElementById('syllabusPreview').innerHTML;
            
            // Get form data from the edit form
            const editForm = document.getElementById('editForm');
            const formData = new FormData(editForm);
            const formDataObj = {};
            for (const [key, value] of formData.entries()) {
                formDataObj[key] = value;
            }
            
            // Create JSON data for the API request
            const jsonData = {
                title: document.getElementById('courseTitle').value || className,
                content: syllabusContent,
                form_data: JSON.stringify(formDataObj),
                class_id: parseInt(classId)
            };
            
            // Show saving indicator
            const saveBtn = document.getElementById('saveSyllabusBtn');
            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;
            
            // Send AJAX request to save the syllabus
            $.ajax({
                url: 'api/syllabus-save.php',
                type: 'POST',
                data: JSON.stringify(jsonData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        alert('Syllabus saved and attached to class successfully!');
                        // Redirect back to class page
                        window.location.href = `view_assignments.php?id=${classId}&syllabus_saved=true`;
                    } else {
                        alert('Error saving syllabus: ' + (response.message || 'Unknown error'));
                        saveBtn.textContent = 'Save & Attach to Class';
                        saveBtn.disabled = false;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error details:', xhr.responseText);
                    alert('An error occurred while saving the syllabus: ' + error);
                    saveBtn.textContent = 'Save & Attach to Class';
                    saveBtn.disabled = false;
                }
            });
        }
        
        function saveToLocalStorage() {
            const formData = new FormData(document.getElementById('editForm'));
            const formValues = {};
            for (const [key, value] of formData.entries()) {
                formValues[key] = value;
            }
            
            // Also save the generation form data
            const generationFormData = new FormData(document.getElementById('generationForm'));
            for (const [key, value] of generationFormData.entries()) {
                formValues[`generation_${key}`] = value;
            }
            
            localStorage.setItem(`syllabus_draft_${classId}`, JSON.stringify(formValues));
        }
        
        function loadFromLocalStorage() {
            const savedData = localStorage.getItem(`syllabus_draft_${classId}`);
            if (savedData) {
                try {
                    const formValues = JSON.parse(savedData);
                    
                    // Fill edit form
                    for (const key in formValues) {
                        if (!key.startsWith('generation_')) {
                            const input = document.getElementById(key);
                            if (input) {
                                input.value = formValues[key];
                            }
                        }
                    }
                    
                    // Fill generation form
                    for (const key in formValues) {
                        if (key.startsWith('generation_')) {
                            const originalKey = key.replace('generation_', '');
                            const input = document.getElementById(originalKey);
                            if (input) {
                                input.value = formValues[key];
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error loading saved data:', e);
                }
            }
        }
    </script>
</body>
</html>
