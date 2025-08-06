<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Add TinyMCE script -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
    color: #777;
    font-size: 16px;
    margin-top: -10px;
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
iframe {
    position: absolute;
    margin-left: -30px;
    border: none;
    width: 600px;
    height: 856px;
    margin-top: 20px;
}
div#assignmentDetailsRight {
    width: calc(100% - 600px);
    float: right;
}
div#gradeParent {
    position: absolute;
    background: #2fa94b;
    right: 20px;
    top: 15px;
    padding: 15px 20px;
    text-align: center;
    border-radius: 20px;
    color: #fff;
    text-align: left;
}
div#grade {
    font-size: 30px;
    font-weight: bold;
}
div#score {
    font-size: 14px;
}
</style>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>

    <div id="mainContent">
        <a href="view_assignment.php?id=<?php echo $_GET['assignmentId']; ?>&classId=<?php echo $_GET['classId']; ?>" class="topLink">&laquo; Back to Assignments</a>
        <div id="fileViewer">
            <iframe id="submissionFile" src="" width="100%" height="800px"></iframe>
        </div>
            <div id="inlineCommentsBox">
                <h3 style="margin-bottom: 10px;">Inline Comments</h3>
                <div id="inlineCommentList" style="max-height: 800px; overflow-y: auto; background: #f9f9f9; padding: 10px; border-radius: 8px; box-shadow: 0 0 5px #ddd;">
                <!-- Inline comments injected here -->
                </div>
            </div>
        <div id="assignmentDetailsRight">
            <h2 id="fileName"></h2>
            <p class="secondaryHeading">Student <span id="studentName"></span></p>
            <div id="gradeParent">
                <div id="gradeEditor" contenteditable="true"></div>
                <div id="score"></div>
            </div>
            <div id="commentsEditor" contenteditable="true"></div>
            <button id="saveButton" class="formCTA">Save Changes</button>
        </div>
    </div>

<script>
$(document).ready(function() {
    tinymce.init({
        selector: '#commentsEditor', // Apply TinyMCE to this element
        plugins: 'autolink lists media table',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist',
    });

    var urlParams = new URLSearchParams(window.location.search);
    var submissionId = urlParams.get('id');
    if (submissionId) {
        fetchSubmissionDetails(submissionId);
    } else {
        alert('Submission ID is missing.');
    }

    $('#saveButton').click(function() {
        saveChanges(submissionId);
    });
});

function fetchSubmissionDetails(submissionId) {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_submission.php',
        data: { id: submissionId },
        success: function(response) {
            if (response.success) {
                $('#assignmentName').text(response.submission.aid);
                $('#submissionFile').attr('src', response.submission.fileName);
                $('#fileName').text(response.submission.fileName.split('/').pop());
                $('#studentName').text(response.submission.studentName);
                $('#gradeEditor').text(response.submission.grade);
                $('#score').text(response.submission.score);
                tinymce.get('commentsEditor').setContent(response.submission.comments);
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the submission details.');
        }
    });
}

function saveChanges(submissionId) {
    var grade = $('#gradeEditor').text();
    var comments = tinymce.get('commentsEditor').getContent();

    $.ajax({
        type: 'POST',
        url: 'api/update_submission.php',
        data: {
            id: submissionId,
            grade: grade,
            comments: comments
        },
        success: function(response) {
            if (response.success) {
                alert('Changes saved successfully.');
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('An error occurred while saving changes.');
        }
    });
}
</script>
</body>
</html>
