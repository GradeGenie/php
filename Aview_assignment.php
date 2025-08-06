<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Albert Sans', sans-serif;
        }
        .modal {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #5d5d5d;
            width: 500px;
            display: none;
            z-index: 2000;
            position: fixed;
            left: 50%;
            top: 30%;
            transform: translate(-50%, -30%);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            margin: 0;
        }
        .closeModal {
            cursor: pointer;
            font-size: 1.5em;
        }
        .drag-drop-box {
            border: 2px dashed #28a745;
            border-radius: 5px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            background-color: #f9f9f9;
            transition: background-color 0.3s ease;
        }
        .drag-drop-box.drag-over {
            background-color: #e8f5e9;
        }
        .drag-drop-box p {
            font-size: 18px;
            color: #555;
            margin: 0;
        }
        .drag-drop-box:hover {
            background-color: #e8f5e9;
        }
        .modalInput {
            display: none;
        }
        .formCTA {
            background: #28a745;
            text-align: center;
            padding: 10px 20px;
            color: #fff;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            margin: 20px 0 0 0;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div id="mainContent">
        <h2 id="assignmentName" class="pageHeading">Assignment Name</h2>
        <p class="headingSubtitle" id="assignmentDetails">Assignment Details</p> 
        <div class="formCTA" onclick="showUploadModal()">Upload files</div>
    </div>

    <!-- Upload Modal -->
    <div class="modal" id="uploadModal">
        <div class="modal-header">
            <h2>Upload Files</h2>
            <span class="closeModal" onclick="closeUploadModal()">&times;</span>
        </div>
        <div id="dragDropBox" class="drag-drop-box">
            Drag & drop files here or <a href="#" onclick="openFileInput()">click here</a> to select files
            <input type="file" id="fileInput" name="files[]" multiple class="modalInput">
        </div>
        <div class="formCTA" id="uploadButton">Upload</div>
    </div>

    <script>
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var assignmentId = urlParams.get('id') || 1; // Use a default assignmentId if missing

        if (assignmentId) {
            fetchAssignmentDetails(assignmentId);
        } else {
            alert('Assignment ID is missing.');
        }

        function fetchAssignmentDetails(assignmentId) {
            $.ajax({
                type: 'GET',
                url: 'api/fetch_assignment.php',
                data: { id: assignmentId },
                success: function(response) {
                    if (response.success) {
                        $('#assignmentName').text(response.assignment.name);
                        $('#assignmentDetails').text(response.assignment.details);
                    } else {
                        alert('Failed to load assignment details: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while fetching the assignment details.');
                }
            });
        }

        function uploadFiles() {
            var fileInput = document.getElementById('fileInput');
            if (!fileInput) {
                console.error('File input element not found.');
                alert('File input element not found.');
                return;
            }

            var formData = new FormData();
            for (var i = 0; i < fileInput.files.length; i++) {
                formData.append('files[]', fileInput.files[i]);
            }
            formData.append('assignmentId', assignmentId);

            if (formData.getAll('files[]').length === 0) {
                alert('No files selected.');
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'api/start_grading.php',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        alert('Files uploaded successfully.');
                        closeUploadModal();
                    } else {
                        alert('Failed to upload files: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                    alert('An error occurred while uploading the files.');
                }
            });
        }

        $('#uploadButton').on('click', function() {
            uploadFiles();
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

            const dataTransfer = new DataTransfer();
            for (let file of files) {
                dataTransfer.items.add(file);
            }

            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.files = dataTransfer.files;
            } else {
                console.error('File input element not found.');
                alert('File input element not found.');
            }
        }
    });

    function showUploadModal() {
        $('#uploadModal').show();
    }

    function closeUploadModal() {
        $('#uploadModal').hide();
    }

    function openFileInput() {
        $('#fileInput').click();
    }
    </script>
</body>
</html>
