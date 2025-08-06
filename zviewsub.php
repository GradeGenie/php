<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/7rkju2yimg2hz9q7rv4g161e217rt04y5iy3bayvpbjscuwu/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
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
#editButton {
    color: #898989;
    font-size: 13px;
    background: #d4d4d4;
    padding: 5px 7px;
    border-radius: 21px;
    vertical-align: 3px;
    margin-left: 5px;
    cursor: pointer;
    width: auto;
    padding: 5px 10px;
    font-size: 13px;
    margin-left: 10px;
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
div#scoreParent {
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
div#score {
    font-size: 30px;
    font-weight: bold;
}
    
</style>
<body>
    <div id="mainContent">
        <a href="view_assignment.php?id=<?php echo $_GET['assignmentId']; ?>&classId=<?php echo $_GET['classId']; ?>" class="topLink">&laquo; Back to Assignments</a>
        <div id="fileViewer">
            <iframe id="submissionFile" src="" width="100%" height="800px"></iframe>
        </div>
        <div id="assignmentDetailsRight">
            <h2 id="fileName"></h2>
            <p class="secondaryHeading">Student <span id="studentName"></span> <button id="editButton">Edit</button> </p>
            <div id="scoreParent">
                <div id="score"></div>
            </div>
            <div id="comments"></div>
            <div id="editFields" class="hidden"> <!-- Hidden by default -->
                <input type="text" id="editScore" class="modalInput" placeholder="Edit Score">
                <textarea id="editComments" class="modalInput hidden" placeholder="Edit Comments"></textarea>
                <button id="saveButton" class="formCTA hidden">Save</button> <!-- Save Button -->
                <button id="cancelButton" class="formCTA cancel hidden">Cancel</button>
            </div>
        </div>
    </div>

<script>
$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var submissionId = urlParams.get('id');
    if (submissionId) {
        fetchSubmissionDetails(submissionId);
    } else {
        alert('Submission ID is missing.');
    }

    // Initialize TinyMCE only in edit mode
    let tinymceEditor;

    $('#editButton').on('click', function() {
        $('#editFields').removeClass('hidden');
        $('#editScore').val($('#score').text());
        $('#comments').addClass('hidden');
        $('#editComments').removeClass('hidden').val($('#comments').html());
        $('#saveButton, #cancelButton').removeClass('hidden');
        $('#editButton').addClass('hidden');

        // Initialize TinyMCE
        tinymceEditor = tinymce.init({
            selector: '#editComments',
            plugins: 'link image code',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | code',
            menubar: false
        });
    });

    $('#saveButton').on('click', function() {
        var updatedScore = $('#editScore').val();
        var updatedComments = tinymce.get('editComments').getContent();

        $.ajax({
            type: 'POST',
            url: 'api/update_submission.php',
            data: {
                id: submissionId,
                score: updatedScore,
                comments: updatedComments
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#score').text(updatedScore);
                    $('#comments').html(updatedComments);
                    exitEditMode();
                    alert(response.message || 'Submission updated successfully.');
                } else {
                    alert(response.message || 'An unknown error occurred.');
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while updating the submission.');
            }
        });
    });

    $('#cancelButton').on('click', function() {
        exitEditMode();
    });

    function exitEditMode() {
        $('#editFields').addClass('hidden');
        $('#comments').removeClass('hidden');
        $('#editComments').addClass('hidden');
        $('#saveButton, #cancelButton').addClass('hidden');
        $('#editButton').removeClass('hidden');

        // Remove TinyMCE
        if (tinymceEditor) {
            tinymce.remove('#editComments');
        }
    }

    function fetchSubmissionDetails(submissionId) {
        $.ajax({
            type: 'GET',
            url: 'api/fetch_submission.php',
            data: { id: submissionId },
            dataType: 'json', // Ensure we expect a JSON response
            success: function(response) {
                if (response.success) {
                    $('#assignmentName').text(response.submission.aid);
                    $('#submissionFile').attr('src', response.submission.fileName);
                    $('#fileName').text(response.submission.fileName.split('/').pop());
                    $('#studentName').text(response.submission.studentName);
                    $('#score').text(response.submission.score);
                    $('#comments').html(response.submission.comments);
                } else {
                    alert(response.message || 'Error fetching submission details.');
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while fetching the submission details.');
            }
        });
    }
});
</script>
</body>
</html>
