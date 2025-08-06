<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<style>
/* Include your existing styles */
.classCard {
    width: 250px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    display: inline-block;
    height: 100px;
    margin: 5px;
    box-shadow: 0 0 10px #e2e2e2;
    cursor: pointer;
}
.rightCTA {
    background: #28a745;
    width: 120px;
    text-align: center;
    padding: 10px 5px;
    color: #fff;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    float: right;
    margin-top: -45px;
}
.formCTA {
    background: #28a745;
    text-align: center;
    padding: 10px 5px;
    color: #fff;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    font-size:16px;
    display: inline-block;
    margin: 10px 5px;
}
div#classCardParent {
    margin-left: -10px;
}
p.headingSubtitle {
    margin-top: -10px;
    color: #777;
}
#overlay{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal {
    background-color: #fefefe;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px #5d5d5d;
    width: 400px;
    display: none;
    z-index: 20000;
    position: fixed;
    left: calc(50% - 200px);
    top: 30%;
}
.modalInput{
    margin-top: 5px;
    width: 100%;
}
.hidden {
    display: none;
}
.closeModal{
    float: right;
    cursor: pointer;
}
span.classCard_level, span.classCard_subject {
    color: #CCC;
    font-size: 14px;
}
span.classCard_name {
    font-weight: bold;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
table, th, td {
    border: 1px solid #ddd;
}
th, td {
    padding: 8px;
    text-align: left;
}
th {
    background-color: #f2f2f2;
}
.viewButton {
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    text-align: center;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.topLink{
    color: #28a745;
    text-decoration: none;
    display: block;
}
.secondaryHeading{
    margin-top: 40px;
}
.editBtn {
    color: #898989;
    font-size: 13px;
    background: #d4d4d4;
    padding: 5px 7px;
    border-radius: 21px;
    vertical-align: 3px;
    margin-left: 5px;
    cursor: pointer;
}
.pageHeading{
    display: inline-block;
}
.buttonLink {
    text-decoration: none;
}
.overviewBox {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 10px;
    display: inline-block;
    width: 23%;
    margin-right: 2%;
    text-align: center;
    box-shadow: 0 0 10px #e2e2e2;
    border: 1px solid #ccc; /* Thin outline */
}
.overviewBox:last-child {
    margin-right: 0;
}
</style>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <div id="mainContent">
        <a href="view_assignments.php?id=<?php echo $_GET['class_id']; ?>" class="topLink">&laquo; Back </a>
        <h2 id="assignmentName" class="pageHeading">Assignment Name</h2>
        <a class="buttonLink" href="javascript:void(0)" onclick="showEditAssignmentModal()"><span class="editBtn">EDIT</span></a>
        <p class="headingSubtitle" id="assignmentDetails">Assignment Details</p> 

        <h3 class="secondaryHeading">Overview</h3>
        <div class="overviewBox">Submissions graded<br><span id="submissionsGraded">0/0</span></div>
        <div class="overviewBox">Submissions approved<br><span id="submissionsApproved">0/0</span></div>
        <div class="overviewBox">Mean<br><span id="meanScore">-</span></div>
        <div class="overviewBox">Mode<br><span id="modeScore">-</span></div>
        <div class="overviewBox">Median<br><span id="medianScore">-</span></div>

        <h3 class="secondaryHeading">Submissions</h3>
        <div>
            <span>Sort by: </span>
            <select id="sortBy">
                <option value="firstName">Grade</option>
                <option value="lastName">Status</option>
            </select>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Grade</th>
                    <th>Status</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody id="submissionsTable">
                <!-- Submissions will be dynamically loaded here -->
            </tbody>
        </table>
        <div class="formCTA" onclick="showImportFilesModal()">Import files</div>
        <div class="formCTA" onclick="showAddSubmissionModal()">Add submission manually</div>
        <div class="formCTA" onclick="exportSubmissions()">Export</div>
    </div>

    <div id="overlay" onclick="closeModals();"></div>

    <div class="modal" id="editAssignmentModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Edit Assignment</h2>
        <form id="editAssignmentForm" enctype="multipart/form-data">
            <label for="editAssignmentName">Assignment Name</label><br>
            <input type="text" id="editAssignmentName" class="modalInput" name="assignmentName" placeholder="Assignment 1" required><br>
            
            <label for="editGradingInstructions">Grading Instructions</label><br>
            <textarea id="editGradingInstructions" class="modalInput" name="gradingInstructions"></textarea><br>

            <label for="editRubric">Choose an existing rubric</label><br>
            <select id="editRubric" name="rubric" class="modalInput">
                <!-- Populate with rubrics from database -->
            </select><br>

            <label>Or upload a new rubric</label><br>
            <input type="file" id="uploadRubric" name="uploadRubric" class="modalInput"><br>

            <label for="editFeedbackStyle">Feedback Style</label><br>
            <select id="editFeedbackStyle" name="feedbackStyle" class="modalInput" required>
                <option value="Brief">Brief</option>
                <option value="Comprehensive">Comprehensive</option>
            </select><br>

            <label for="editExtraInstructions">Extra Instructions</label><br>
            <textarea id="editExtraInstructions" class="modalInput" name="extraInstructions"></textarea><br>

            <div class="formCTA" onclick="updateAssignment()">Update Assignment</div>
        </form>
    </div>

    <div class="modal" id="importFilesModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Import Files</h2>
        <form id="importFilesForm" enctype="multipart/form-data">
            <label for="importFiles">Select Files</label><br>
            <input type="file" id="importFiles" class="modalInput" name="importFiles[]" multiple required><br>
            <div class="formCTA" onclick="importFiles()">Import Files</div>
        </form>
    </div>

    <div class="modal" id="addSubmissionModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Add Submission Manually</h2>
        <form id="addSubmissionForm">
            <label for="studentName">Student Name</label><br>
            <input type="text" id="studentName" class="modalInput" name="studentName" required><br>
            
            <label for="grade">Grade</label><br>
            <input type="text" id="grade" class="modalInput" name="grade" required><br>
            
            <label for="status">Status</label><br>
            <input type="text" id="status" class="modalInput" name="status" required><br>

            <label for="review">Review</label><br>
            <textarea id="review" class="modalInput" name="review"></textarea><br>

            <div class="formCTA" onclick="addSubmission()">Add Submission</div>
        </form>
    </div>

    <script>
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    if (assignmentId) {
        fetchAssignmentDetails(assignmentId);
    } else {
        alert('Assignment ID is missing.');
    }
});

function fetchAssignmentDetails(assignmentId) {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_assignment.php',
        data: { id: assignmentId },
        success: function(response) {
            if (response.success) {
                $('#assignmentName').text(response.assignment.name);
                $('#assignmentDetails').text('Details: ' + response.assignment.details + 
                                             '\nInstructions: ' + response.assignment.instructions + 
                                             '\nRubric: ' + (response.assignment.rubric ? response.assignment.rubric : 'None') + 
                                             '\nFeedback Style: ' + response.assignment.style + 
                                             '\nExtra Instructions: ' + response.assignment.extra_details);
                $('#submissionsGraded').text(response.stats.submissionsGraded);
                $('#submissionsApproved').text(response.stats.submissionsApproved);
                $('#meanScore').text(response.stats.meanScore);
                $('#modeScore').text(response.stats.modeScore);
                $('#medianScore').text(response.stats.medianScore);

                $('#submissionsTable').empty(); // Clear existing submissions
                response.submissions.forEach(function(submission) {
                    var submissionRow = '<tr>' +
                        '<td>' + submission.studentName + '</td>' +
                        '<td>' + submission.grade + '</td>' +
                        '<td>' + submission.status + '</td>' +
                        '<td>' + submission.review + '</td>' +
                        '</tr>';
                    $('#submissionsTable').append(submissionRow);
                });

                // Pre-fill edit form
                $('#editAssignmentName').val(response.assignment.name);
                $('#editGradingInstructions').val(response.assignment.instructions);
                $('#editRubric').val(response.assignment.rubric);
                $('#editFeedbackStyle').val(response.assignment.style);
                $('#editExtraInstructions').val(response.assignment.extra_details);
            } else {
                alert('Failed to load assignment details: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the assignment details.');
        }
    });
}

function showImportFilesModal() {
    $('#overlay').show();
    $('#importFilesModal').show();
}

function showAddSubmissionModal() {
    $('#overlay').show();
    $('#addSubmissionModal').show();
}

function closeModals() {
    $('#overlay').hide();
    $('.modal').hide();
}

function updateAssignment() {
    var formData = new FormData(document.getElementById('editAssignmentForm'));
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    formData.append('assignmentId', assignmentId);

    $.ajax({
        type: 'POST',
        url: 'api/edit_assignment.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                closeModals();
                fetchAssignmentDetails(assignmentId); // Refresh assignment details
            } else {
                alert('Failed to update assignment: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while updating the assignment.');
        }
    });
}

function importFiles() {
    var formData = new FormData(document.getElementById('importFilesForm'));
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    formData.append('assignmentId', assignmentId);

    $.ajax({
        type: 'POST',
        url: 'api/import_files.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                closeModals();
                fetchAssignmentDetails(assignmentId); // Refresh assignment details
            } else {
                alert('Failed to import files: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while importing files.');
        }
    });
}

function addSubmission() {
    var formData = new FormData(document.getElementById('addSubmissionForm'));
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    formData.append('assignmentId', assignmentId);

    $.ajax({
        type: 'POST',
        url: 'api/add_submission.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                closeModals();
                fetchAssignmentDetails(assignmentId); // Refresh assignment details
            } else {
                alert('Failed to add submission: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while adding the submission.');
        }
    });
}

function exportSubmissions() {
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    window.location.href = 'api/export_submissions.php?id=' + assignmentId;
}
</script>

</body>
</html>
