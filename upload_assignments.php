<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Assignments | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <style>
        /* Additional CSS for drag-and-drop section */
        .drag-and-drop {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px; 
        }
        .drag-and-drop.dragover {
            border-color: #000;
        }
        .drag-and-drop p {
            margin: 0;
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>
    <div id="mainContent">
        <div class="container">
            <h1>Bulk Grading</h1>

            <!-- Step 1: Create Assignment -->
            <div class="section">
                <h2>Upload Assignments</h2>
                <form id="assignmentForm" action="api/create_assignment.php" method="POST" enctype="multipart/form-data">
                    <div class="drag-and-drop" id="dropZone">
                        <h4>Drag and drop your assignments here</h4>
                        <p class="subtitle">Assignments should either be in PDF or DOCX formats</p>
                        <input type="file" id="fileInput" name="assignments[]" accept=".pdf,.docx" multiple style="display: none;">
                        <p>or click to select files</p>
                    </div>
                    <button type="submit" class="button">Next: Start Grading!</button>
                </form>
            </div>

            <!-- Step 2: Upload Assignments -->
            <div class="section hidden" id="uploadSection">
                <h2>Upload Assignments</h2>
                <form id="uploadForm" action="api/upload_assignments.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="assignments">Upload Assignments (ZIP):</label>
                        <input type="file" id="assignments" name="assignments" accept=".zip" required>
                    </div>
                    <button type="submit" class="button">Next: Select/Create Rubric</button>
                </form>
            </div>

            <!-- Step 3: Select/Create Rubric -->
            <div class="section hidden" id="rubricSection">
                <h2>Select/Create Rubric</h2>
                <form id="rubricForm" action="api/select_rubric.php" method="POST">
                    <div class="form-group">
                        <label for="rubricOption">Rubric Option</label>
                        <select id="rubricOption" name="rubricOption" required>
                            <option value="existing">Select Existing Rubric</option>
                            <option value="upload">Upload Rubric (PDF)</option>
                            <option value="create">Create New Rubric</option>
                        </select>
                    </div>
                    <div class="form-group hidden" id="existingRubric">
                        <label for="existingRubricSelect">Select Existing Rubric</label>
                        <select id="existingRubricSelect" name="existingRubricSelect">
                            <!-- Dynamically populated with existing rubrics -->
                        </select>
                    </div>
                    <div class="form-group hidden" id="uploadRubric">
                        <label for="rubricFile">Upload Rubric (PDF)</label>
                        <input type="file" id="rubricFile" name="rubricFile" accept="application/pdf">
                    </div>
                    <div class="form-group hidden" id="createRubric">
                        <button type="button" class="button" onclick="location.href='rubric.create.php'">Create New Rubric</button>
                    </div>
                    <button type="submit" class="button">Finish</button>
                </form>
            </div>

            <!-- Grading Progress -->
            <div class="section hidden" id="gradingProgressSection">
                <h2>Grading Progress</h2>
                <p class="left-align">Track the progress of your bulk grading below.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="gradingProgressTable">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to handle drag-and-drop and file selection
        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');

            dropZone.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (event) => {
                handleFiles(event.target.files);
            });

            dropZone.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropZone.classList.add('dragover');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('dragover');
            });

            dropZone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropZone.classList.remove('dragover');
                handleFiles(event.dataTransfer.files);
            });

            function handleFiles(files) {
                const validFiles = Array.from(files).filter(file => {
                    return file.type === 'application/pdf' || file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                });
                if (validFiles.length > 0) {
                    // Assuming formData will handle the files for uploading
                    const formData = new FormData();
                    validFiles.forEach(file => {
                        formData.append('assignments[]', file);
                    });
                    // Additional logic to handle form submission
                    document.getElementById('assignmentForm').submit();
                } else {
                    alert('Please upload only PDF or DOCX files.');
                }
            }
        });
    </script>
</body>
</html>
