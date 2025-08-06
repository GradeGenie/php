<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

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
            padding: 10px 20px; /* Adjust padding for uniform size */
            color: #fff;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            margin: 0 5px; /* Uniform margin for spacing */
            text-decoration: none;
            box-shadow: 0 0 10px #e2e2e2;
            transition: background-color 0.3s;
            border: none; /* Remove borders for the button */
            outline: none; /* Remove focus outline for the button */
        }

        .formCTA:hover {
            background-color: #218838;
        }
        div#classCardParent {
            margin-left: -10px;
        }
        p.headingSubtitle {
            margin-top: -10px;
            color: #777;
        }
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
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
        .modalInput {
            margin-top: 5px;
            width: 100%;
        }
        .hidden {
            display: none;
        }
        .closeModal {
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
            vertical-align: top;
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
        .topLink {
            color: #28a745;
            text-decoration: none;
            display: block;
        }
        .secondaryHeading {
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
        .pageHeading {
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
            display: none;
        }
        .actionButtons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment {
            max-height: 40px;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .comment.expanded {
            max-height: none;
        }

        .showLink {
            color: #28a745;
            cursor: pointer;
            text-decoration: underline;
        }
        .deleteButton {
            background-color: #dc3545; /* Red color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            display: none; /* Initially hidden */
        }

        .moreMenu {
            cursor: pointer;
            font-size: 20px; /* Adjust size as needed */
            display: inline-block;
        }

        .loading-wheel {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 5px;
            vertical-align: middle;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .reload-status {
            cursor: pointer;
            color: #3498db;
            margin-left: 5px;
        }

    </style>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
</head>
<body>
    <div id="mainContent">
        <a href="view_assignments.php?id=<?php echo $_GET['classId']; ?>" class="topLink">&laquo; Back to Class</a>
        <h2 id="assignmentName" class="pageHeading">Assignment Name</h2>
        <a class="buttonLink" href="edit_assignment.php?id=<?php echo $_GET['id']; ?>&class=<?php echo $_GET['classId']; ?>"><span class="editBtn">EDIT</span></a>
        <p class="headingSubtitle" id="assignmentDetails">Assignment Details</p> 

        <h3 class="secondaryHeading">Overview</h3>
        <div class="overviewBox">Submissions graded<br><span id="submissionsGraded">0/0</span></div>
        <div class="overviewBox">Average<br><span id="meanGrade">-</span></div>
        <div class="overviewBox">Median<br><span id="medianGrade">-</span></div>

        <h3 class="secondaryHeading">Submissions</h3>
        <div class="actionButtons">
            <button class="formCTA" onclick="location.href='bulk_upload.php?assignmentId=<?php echo isset($_GET['id']) ? $_GET['id'] : 1; ?>&classId=<?php echo isset($_GET['classId']) ? $_GET['classId'] : 1; ?>'">Upload Files</button>
            <button class="formCTA" onclick="exportCSV()">Export All</button>
        </div>

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
                    <th>Comments</th>
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

        <!-- Overlay and Modal -->
        <div id="overlay" onclick="closeUploadModal()"></div>
        
        <div class="modal" id="uploadModal">
            <span class="closeModal" onclick="closeUploadModal()">&times;</span>
            <h2>Upload Files</h2>
            <form id="uploadForm">
                <div id="dragDropBox" class="drag-drop-box">
                    Drag & drop files here or <a href="#" onclick="openFileInput()">click here</a> to select files
                    <input type="file" id="fileInput" name="files[]" multiple class="modalInput hidden">
                </div>
                <div class="formCTA" id="uploadButton">Upload</div>
            </form>
        </div>

    </div>

    <script>
    function exportCSV() {
        var assignmentId = window.assignmentId;
        var form = $('<form method="POST" action="api/export_csv.php"></form>');
        form.append($('<input type="hidden" name="assignmentId" value="' + assignmentId + '">'));
        $('body').append(form);
        form.submit();
    }

    function reloadStatus(submissionId) {
        var statusCell = $('tr[data-sid="' + submissionId + '"] td:eq(3)');
        var commentsCell = $('tr[data-sid="' + submissionId + '"] td:eq(2)');
        statusCell.html('Checking... <span class="loading-wheel"></span>');

        $.ajax({
            type: 'GET',
            url: 'api/check_pending_submissions.php',
            data: { id: window.assignmentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var submissionData = response.submissions[submissionId];
                    if (submissionData) {
                        statusCell.html(submissionData.status +
                            (submissionData.status === 'Pending Grading' ? 
                                '<span class="loading-wheel"></span>' +
                                '<span class="reload-status" onclick="reloadStatus(' + submissionId + ')"><i class="fas fa-sync-alt"></i></span>'
                            : '')
                        );
                        if (submissionData.comments) {
                            commentsCell.html('<div class="comment" id="comment-' + submissionId + '">' + 
                                submissionData.comments +
                            '</div>' +
                            '<span class="showLink">Show all</span>');
                        }
                    } else {
                        statusCell.html('Error: Submission not found');
                    }
                } else {
                    statusCell.html('Error: ' + response.message);
                }
            },
            error: function() {
                statusCell.html('Error checking status');
            }
        });
    }

    function deleteSubmission(submissionId) {
        if (confirm('Are you sure you want to delete this submission?')) {
            $.ajax({
                type: 'POST',
                url: 'api/delete_submission.php',
                data: { submissionId: submissionId },
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            alert('Submission deleted successfully.');
                            location.reload(); // Refresh the page to reflect changes
                        } else {
                            alert('Failed to delete submission: ' + result.message);
                        }
                    } catch (e) {
                        alert('Submission deleted successfully.');
                        location.reload(); // Refresh the page to reflect changes
                        console.error('Error parsing JSON: ', e);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error communicating with the server.');
                    console.error('Error: ', status, error);
                }
            });
        }
    }

    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var assignmentId = urlParams.get('id');
        var classId = urlParams.get('class');

        if (assignmentId) {
            fetchAssignmentDetails(assignmentId);
        } else {
            alert('Assignment ID is missing.');
        }

        // Ensure assignmentId is accessible
        window.assignmentId = assignmentId;

        // Toggle visibility for delete button when moreMenu is clicked
        $(document).on('click', '.moreMenu', function() {
            $(this).find('.deleteButton').toggle(); // Toggle visibility of the delete button
        });

        // Define the uploadFiles function within the scope where assignmentId is accessible
        function uploadFiles() {
            var formElement = document.getElementById('uploadForm');
            var formData = new FormData(formElement);
            formData.append('aid', assignmentId); // Ensure assignmentId is defined and correct

            if (formData.getAll('files[]').length === 0) {
                alert('No files selected.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'api/upload_files.php',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response); // Log the response for debugging
                    if (response.success) {
                        alert('Files uploaded successfully.');
                        closeUploadModal();
                    } else {
                        alert('Failed to upload files: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText); // Log the error response for debugging
                    alert('An error occurred while uploading the files.');
                }
            });
        }

        // Attach uploadFiles function to the upload button
        $('#uploadButton').on('click', function() {
            uploadFiles();
        });

        // Add this new function
        var checkCount = 0;
        var MAX_CHECKS = 50;

        function checkPendingSubmissions() {
            console.log('Checking pending submissions... (Check #' + (checkCount + 1) + ')');
            var assignmentId = window.assignmentId;
            $.ajax({
                type: 'GET',
                url: 'api/check_pending_submissions.php',
                data: { id: assignmentId },
                dataType: 'json',
                success: function(response) {
                    console.log('Received response:', response);
                    if (response.success) {
                        var allGraded = true;
                        var updatedCount = 0;
                        var pendingCount = 0;

                        $('#submissionsTable tr').each(function() {
                            var $row = $(this);
                            var statusCell = $row.find('td:eq(3)');
                            var commentsCell = $row.find('td:eq(2)');
                            var submissionId = $row.data('sid');
                            var submissionData = response.submissions[submissionId];

                            if (submissionData) {
                                var newStatus = submissionData.status;
                                var newComments = submissionData.comments;
                                var currentStatus = statusCell.text().trim();

                                if (newStatus !== currentStatus) {
                                    statusCell.html(newStatus +
                                        (newStatus === 'Pending Grading' ? 
                                            '<span class="loading-wheel"></span>' +
                                            '<span class="reload-status" onclick="reloadStatus(' + submissionId + ')"><i class="fas fa-sync-alt"></i></span>'
                                        : '')
                                    );
                                    updatedCount++;
                                }

                                if (commentsCell.find('.comment').text().trim() === '' && newComments) {
                                    commentsCell.html('<div class="comment" id="comment-' + submissionId + '">' + newComments + '</div>' +
                                        '<span class="showLink">Show all</span>');
                                }

                                if (newStatus === 'Pending Grading') {
                                    allGraded = false;
                                    pendingCount++;
                                }
                            }
                        });

                        console.log('Updated ' + updatedCount + ' submissions.');
                        console.log('Pending submissions: ' + pendingCount);
                        console.log('All graded: ' + allGraded);

                        checkCount++;
                        if (allGraded || checkCount >= MAX_CHECKS) {
                            clearInterval(window.statusCheckInterval);
                            console.log('Stopped checking. Reason: ' + (allGraded ? 'All graded' : 'Max checks reached'));
                        } else {
                            console.log('Continuing to check for pending submissions.');
                        }
                    } else {
                        console.error('Failed to check pending submissions:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('An error occurred while checking pending submissions:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

        function startCheckingSubmissions() {
            console.log('startCheckingSubmissions called');
            if (window.statusCheckInterval) {
                console.log('Clearing existing interval');
                clearInterval(window.statusCheckInterval);
            }
            checkCount = 0;
            console.log('Setting new interval');
            window.statusCheckInterval = setInterval(checkPendingSubmissions, 15000);
            console.log('Running initial check');
            checkPendingSubmissions();
        }

        // Modify the fetchAssignmentDetails function
        function fetchAssignmentDetails(assignmentId) {
            $.ajax({
                type: 'GET',
                url: 'api/fetch_assignment.php',
                data: { id: assignmentId },
                success: function(response) {
                    console.log('Fetched assignment details:', response);
                    if (response.success) {
                        // Check if there are any submissions
                        if (response.submissions.length === 0) {
                            $("#noSubmissions").show();
                        } else {
                            $('#assignmentName').text(response.assignment.name);
                            $('#assignmentDetails').text(response.assignment.details);
                            $('#submissionsGraded').text(response.stats.submissionsGraded);
                            $('#submissionsApproved').text(response.stats.submissionsApproved);
                            $('#meanGrade').text(response.stats.meanScore); // Changed from meanScore to meanGrade
                            $('#medianGrade').text(response.stats.medianScore); // Changed from medianScore to medianGrade

                            $('#submissionsTable').empty(); // Clear existing submissions
                            var hasPendingSubmissions = false;
                            var pendingCount = 0;
                            response.submissions.forEach(function(submission) {
                                var submissionRow = '<tr data-sid="' + submission.sid + '">' +
                                    '<td>' + submission.studentName + '</td>' +
                                    '<td>' + submission.score + '</td>' + // Changed from submission.grade to submission.score
                                    '<td>' +
                                        '<div class="comment" id="comment-' + submission.sid + '">' + 
                                            submission.comments +
                                        '</div>' +
                                        '<span class="showLink" onclick="toggleComments(' + submission.sid + ')">Show all</span>' +
                                    '</td>' +
                                    '<td>' + submission.status + 
                                        (submission.status === 'Pending Grading' ? 
                                            '<span class="loading-wheel"></span>' +
                                            '<span class="reload-status" onclick="reloadStatus(' + submission.sid + ')"><i class="fas fa-sync-alt"></i></span>'
                                        : '') +
                                    '</td>' +
                                    '<td><a href="view_submission.php?id=' + submission.sid + '&assignmentId=' + assignmentId + '&classId=' + response.assignment.class + '">View Submission</a></td>' +
                                    '<td>' +
                                        '<span class="moreMenu" style="display:none"><i class="fas fa-ellipsis-v"></i>' +
                                            '<button class="deleteButton" onclick="deleteSubmission(' + submission.sid + ')">Delete</button>' +
                                        '</span>' +
                                    '</td>' +
                                    '</tr>';
                                $('#submissionsTable').append(submissionRow);
                                if (submission.status === 'Pending Grading') {
                                    hasPendingSubmissions = true;
                                    pendingCount++;
                                }
                            });

                            console.log('Has pending submissions:', hasPendingSubmissions);
                            console.log('Number of pending submissions:', pendingCount);
                            if (hasPendingSubmissions) {
                                console.log('Found pending submissions. Starting interval checks.');
                                startCheckingSubmissions();
                            } else {
                                console.log('No pending submissions found.');
                            }
                        }
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

        function openFileInput() {
            $('#fileInput').click();
        }

        function deleteSubmission(submissionId) {
        if(confirm('Are you sure you want to delete this submission?')) {
            $.ajax({
                type: 'POST',
                url: 'api/delete_submission.php',
                data: { submissionId: submissionId },
                success: function(response) {
                    try {
                        var result = JSON.parse(response);
                        if (result.success) {
                            alert('Submission deleted successfully.');
                            location.reload(); // Refresh the page or update the UI accordingly
                        } else {
                            alert('Failed to delete submission: ' + result.message);
                        }
                    } catch(e) {
                        alert('Submission deleted successfully.');
                        location.reload(); // Refresh the page or update the UI accordingly
                        console.error('Error parsing JSON: ', e);
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error communicating with the server.');
                    console.error('Error: ', status, error);
                }
            });
        }
    }

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

            // Create a DataTransfer object to hold the files
            const dataTransfer = new DataTransfer();
            for (let file of files) {
                dataTransfer.items.add(file);
            }

            // Ensure the fileInput is not null before setting its files property
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.files = dataTransfer.files;
            } else {
                console.error('File input element not found.');
            }
        }

        // Use event delegation for the "Show all" / "Show less" functionality
        $(document).on('click', '.showLink', function() {
            var commentDiv = $(this).prev('.comment');
            if (commentDiv.hasClass('expanded')) {
                commentDiv.removeClass('expanded');
                $(this).text('Show all');
            } else {
                commentDiv.addClass('expanded');
                $(this).text('Show less');
            }
        });
    });
    </script>
</body>
</html>
