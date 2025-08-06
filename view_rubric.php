<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rubric | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/o30xlm0ugayndmrm2jug7wui2p17qjcdfp9im13nqma21k5l/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <style>
        .buttonContainer {
            float: right;
            margin-top: -45px;
        }
        .deleteButton, .editButton {
            color: #fff;
            font-weight: bold;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .deleteButton {
            background: #dc3545;
            margin-right: 10px;
        }
        .editButton {
            background: #007bff;
        }
        .rubricDetails {
            margin-bottom: 20px;
        }
        .rubricDetails span {
            display: block;
            margin-bottom: 5px;
        }
        .topLink {
            color: #28a745;
            text-decoration: none;
            display: block;
        }
        .hidden {
            display: none;
        }
        .modalInput {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        #editContent {
            height: 200px; /* Increase the height of the content text box */
        }
    </style>
</head>
<body>
    <div id="mainContent">
        <a href="view_rubrics.php" class="topLink">&laquo; Back to Rubrics</a>
        <h2 class="pageHeading" id="rubricTitle">Rubric Title</h2>
        <div class="buttonContainer">
            <button class="deleteButton" onclick="deleteRubric()">Delete</button>
            <button class="editButton" onclick="enterEditMode()">Edit</button>
        </div>
        <div class="rubricDetails">
            <span id="rubricDescription">Description</span>
            <span id="rubricLevel">Level</span>
            <span id="rubricSubject">Subject</span>
            <span id="rubricAssignmentType">Rubric Style</span>
        </div>
        <div id="rubricContent"></div>

        <div id="editRubricForm" class="hidden">
            <form id="editForm">
                <div class="form-group">
                    <label for="editTitle">Assignment Title</label>
                    <input type="text" id="editTitle" name="title" class="modalInput">
                </div>
                <div class="form-group">
                    <label for="editDescription">Description</label>
                    <textarea id="editDescription" name="description" class="modalInput"></textarea>
                </div>
                <div class="form-group">
                    <label for="editLevel">Level</label>
                    <input type="text" id="editLevel" name="level" class="modalInput">
                </div>
                <div class="form-group">
                    <label for="editSubject">Subject</label>
                    <input type="text" id="editSubject" name="subject" class="modalInput">
                </div>
                <div class="form-group">
                    <label for="editAssignmentType">Rubric Style</label>
                    <input type="text" id="editAssignmentType" name="assignment_type" class="modalInput">
                </div>
                <div class="form-group">
                    <label for="editContent">Content</label>
                    <textarea id="editContent" name="content" class="modalInput"></textarea>
                </div>
                <button type="button" class="formCTA" onclick="saveRubricChanges();">Save Changes</button>
                <button type="button" class="formCTA" onclick="exitEditMode()">Cancel</button>
            </form>
        </div>
    </div>

<script>
var rubricId;  // Declare rubricId globally

$(document).ready(function() {
    var urlParams = new URLSearchParams(window.location.search);
    rubricId = urlParams.get('id');  // Retrieve and store rubric ID when document is ready

    if (rubricId) {
        fetchRubric(rubricId);
    } else {
        alert('Rubric ID is missing.');
    }
});


function fetchRubric(rubricId) {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_rubric.php',
        data: { id: rubricId },
        success: function(response) {
            if (response.success) {
                $('#rubricTitle').text(response.rubric.title);
                $('#rubricDescription').text('Description: ' + response.rubric.description);
                $('#rubricLevel').text('Level: ' + response.rubric.level);
                $('#rubricSubject').text('Subject: ' + response.rubric.subject);
                $('#rubricAssignmentType').text('Rubric Style: ' + response.rubric.assignment_type);
                $('#rubricContent').html(response.rubric.content);

                // Populate edit form
                $('#editTitle').val(response.rubric.title);
                $('#editDescription').val(response.rubric.description);
                $('#editLevel').val(response.rubric.level);
                $('#editSubject').val(response.rubric.subject);
                $('#editAssignmentType').val(response.rubric.assignment_type);
                $('#editContent').val(response.rubric.content);
            } else {
                alert('Failed to load rubric: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the rubric.');
        }
    });
}

function deleteRubric() {
    var urlParams = new URLSearchParams(window.location.search);
    var rubricId = urlParams.get('id');
    if (confirm('Are you sure you want to delete this rubric?')) {
        $.ajax({
            type: 'POST',
            url: 'api/delete_rubric.php',
            data: { id: rubricId },
            success: function(response) {
                if (response.success) {
                    alert('Rubric deleted successfully.');
                    window.location.href = 'view_rubrics.php';
                } else {
                    alert('Failed to delete rubric: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting the rubric.');
            }
        });
    }
}

function enterEditMode() {
    $('#rubricContent').addClass('hidden');
    $('.rubricDetails').addClass('hidden');
    $('.editButton').addClass('hidden');
    $('.deleteButton').addClass('hidden');
    $('#editRubricForm').removeClass('hidden');

    tinymce.init({
        selector: '#editContent',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        entity_encoding: "raw", // Ensures that HTML entities are not encoded
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' },
        ],
        setup: function (editor) {
            editor.on('init', function () {
                // Load the HTML content into TinyMCE after the editor has initialized
                editor.setContent($('#rubricContent').html());
            });
        },
        ai_request: (request, respondWith) => respondWith.string(() => Promise.reject("See docs to implement AI Assistant")),
    });
}


function exitEditMode() {
    $('#rubricContent').removeClass('hidden');
    $('.rubricDetails').removeClass('hidden');
    $('.editButton').removeClass('hidden');
    $('.deleteButton').removeClass('hidden');
    $('#editRubricForm').addClass('hidden');
    tinymce.remove('#editContent');
}

function saveRubricChanges() {
    var formData = {
        id: rubricId,  // Make sure rubricId is defined and correctly scoped
        title: $('#editTitle').val(),
        description: $('#editDescription').val(),
        level: $('#editLevel').val(),
        subject: $('#editSubject').val(),
        assignment_type: $('#editAssignmentType').val(),
        content: tinymce.get('editContent').getContent()
    };

    $.ajax({
        type: 'POST',
        url: 'api/update_rubric.php',
        data: formData,
        dataType: 'json', // Ensures jQuery expects JSON response
        success: function(response) {
            if (response.success) {
                alert('Rubric updated successfully.');
                location.reload();
                fetchRubric(rubricId);  // Reload rubric details to reflect changes
                
            } else {
                alert('Failed to update rubric: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error: ', status, error);
            console.error('Response Text: ', xhr.responseText);
            alert('An error occurred while updating the rubric: ' + xhr.responseText);
        }
    });
}

</script>

</body>
</html>
