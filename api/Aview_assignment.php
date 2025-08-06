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
.drag-drop-box {
    border: 2px dashed #28a745;
    border-radius: 5px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    margin-top: 10px;
}
.drag-drop-box.drag-over {
    background-color: #e8f5e9;
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
    border: 1px solid #ccc;
    margin-bottom: 15px;
}
.overviewBox:last-child {
    margin-right: 0;
}
div#noSubmissions {
    background: #e3e3e3;
    padding: 10px 20px;
    border-radius: 23px;
    margin-bottom: 10px;
}
</style>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <div id="mainContent">
        <a href="view_class.php?id=<?php echo $_GET['classId']; ?>" class="topLink">&laquo; Back to Class</a>
        <h2 id="assignmentName" class="pageHeading">Assignment Name</h2>
        <a class="buttonLink" href="edit_assignment.php?id=<?php echo $_GET['id']; ?>&class=<?php echo $_GET['classId']; ?>"><span class="editBtn">EDIT</span></a>
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
                <option value="grade">Grade</option>
                <option value="status">Status</option>
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
        <div id="noSubmissions">
            <h2>Welcome to Your Assignment</h2>
            <p>There aren't any submissions in here yet, let's upload some to get grading</p>
        </div>
        <div class="formCTA" onclick="showUploadModal()">Upload files</div>
    </div>

    <!-- Overlay and Modal -->
    <div id="overlay"></div>
    <div class="modal" id="uploadModal">
        <span class="closeModal" onclick="closeUploadModal()">&times;</span>
        <h2>Upload Files</h2>
        <form id="uploadForm">
            <div id="dragDropBox" class="drag-drop-box">
                Drag & drop files here or click to select files
                <input type="file" id="fileInput" name="files[]" multiple class="modalInput hidden">
            </div>
            <div class="formCTA" onclick="uploadFiles()">Upload</div>
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
                $('#assignmentDetails').text(response.assignment.details);

                $('#submissionsTable').empty(); // Clear existing submissions
                response.submissions.forEach(function(submission) {
                    var submissionRow = '<tr>' +
                        '<td>' + submission.studentName + '</td>' +
                        '<td>' + submission.grade + ' ('+ submission.score +')</td>' +
                        '<td>' + submission.status + '</td>' +
                        '<td><a href="view_submission.php?id=' + submission.sid + '&assignmentId=' + assignmentId + '&classId=' + response.assignment.class + '">View Submission</a></td>' +
                        '</tr>';
                    $('#submissionsTable').append(submissionRow);
                });
            } else {
                alert('Failed to load assignment details: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the assignment details.');
        }
    });
}

function showUploadModal() {
    $('#overlay').show();
    $('#uploadModal').show();
}

function closeUploadModal() {
    $('#overlay').hide();
    $('#uploadModal').hide();
}

$(document).on('click', '#dragDropBox', function() {
    $('#fileInput').click();
});

$('#fileInput').on('change', function(e) {
    var files = e.target.files;
    handleFiles(files);
});

$('#dragDropBox').on('dragover', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('drag-over');
});

$('#dragDropBox').on('dragleave', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('drag-over');
});

$('#dragDropBox').on('drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).removeClass('drag-over');
    var files = e.originalEvent.dataTransfer.files;
    handleFiles(files);
});

function handleFiles(files) {
    $('#dragDropBox').text(files.length + " file(s) selected");
    // Store the files for uploading
    $('#fileInput')[0].files = files;
}

function uploadFiles() {
    var urlParams = new URLSearchParams(window.location.search);
    var assignmentId = urlParams.get('id');
    if (!assignmentId) {
        alert('Assignment ID is missing.');
        return;
    }

    var formData = new FormData();
    formData.append('assignmentId', assignmentId);
    var files = $('#fileInput')[0].files;
    for (var i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    $.ajax({
        type: 'POST',
        url: 'api/start_grading.php',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.success) {
                alert('Files uploaded and grading started.');
                closeUploadModal();
                fetchAssignmentDetails(assignmentId); // Refresh the assignment details
            } else {
                alert('Failed to upload files: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while uploading the files.');
        }
    });
}
</script>
</body>
</html>
