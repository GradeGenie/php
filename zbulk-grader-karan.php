<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genie - Bulk Grading Tool</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    
    <div id="mainContent">
        <div class="container">
            <h1>Bulk Grading</h1>

            <!-- Step 1: Create Assignment -->
            <div class="section">
                <h2>Bulk Grading</h2>
                <form id="assignmentForm" action="api/create_assignment.php" method="POST">
                    <div class="form-group">
                    <h3>Let's choose an assignment title</h3>
                    <input type="text" id="assignmentTitle" placeholder="Amazing Assignment Title" name="assignmentTitle" required>
                    <div class="spacer"></div>
                        <label for="assignmentTitle">Now, let's choose a rubric</label>
                        <table>
                            <tr>
                                <td>
                                    <h4><span class="highlightedNumber">1</span> Choose a Rubric</h4>
                                    <select id="rubricOption" name="rubricOption" required>
                                        <option value="">Choose a Rubric...</option>
                                    </select>
                                </td>
                                <td>
                                    <h4><span class="highlightedNumber">2</span> Upload a Rubric</h4>
                                    <input type="file" id="rubricFile" name="rubricFile" accept="application/pdf">
                                </td>
                            </tr>
                        </table>
                        
                    </div>
                    <button type="submit" class="button">Next: Upload Assignments</button>
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

            <!-- Grading Progress
            <div class="section">
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
            </div> -->
        </div>
    </div>

    <script>
        // JavaScript to handle the step-by-step flow
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('assignmentForm').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('api/create_assignment.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('uploadSection').classList.remove('hidden');
                        document.getElementById('assignmentForm').classList.add('hidden');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });

            document.getElementById('uploadForm').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('api/upload_assignments.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('rubricSection').classList.remove('hidden');
                        document.getElementById('uploadForm').classList.add('hidden');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });

            document.getElementById('rubricOption').addEventListener('change', function() {
                const value = this.value;
                document.getElementById('existingRubric').classList.add('hidden');
                document.getElementById('uploadRubric').classList.add('hidden');
                document.getElementById('createRubric').classList.add('hidden');
                if (value === 'existing') {
                    document.getElementById('existingRubric').classList.remove('hidden');
                } else if (value === 'upload') {
                    document.getElementById('uploadRubric').classList.remove('hidden');
                } else if (value === 'create') {
                    document.getElementById('createRubric').classList.remove('hidden');
                }
            });

            // Fetch and display grading progress
            fetch('api/show_progress.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const tableBody = document.getElementById('gradingProgressTable');
                        tableBody.innerHTML = '';
                        data.data.forEach(assignment => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${assignment.assignment_title}</td>
                                <td>
                                    <div class="progress-bar">
                                        <span class="progress" style="width: ${assignment.progress}%;"></span>
                                    </div>
                                </td>
                                <td class="progress-status">${assignment.status}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    } else {
                        console.error('Failed to fetch assignments:', data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
