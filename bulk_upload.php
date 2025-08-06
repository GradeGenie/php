<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Upload | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <!-- Include Dropzone.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.js"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
</head>
<body>

    <div id="mainContent">
        <h2 id="assignmentName" class="pageHeading">Bulk Upload</h2>
        <div id="noSubmissions">
            <h2>Upload Your Submissions</h2>
            <p>Drag & drop files below or click to select files to upload. Please upload DOCX or PDF files only.</p>
        </div>

        <!-- Dropzone Form -->
        <form action="api/start_grading.php?assignmentId=<?php echo $_GET['assignmentId']; ?>" class="dropzone" id="fileUploadDropzone">
            <div class="dz-message">Drop files here or click to upload</div>
            <input type="hidden" name="assignmentId" value="<?php echo $_GET['assignmentId']; ?>">
        </form><br>
        <button id="uploadButton" class="formCTA">Upload</button>
    </div>

    <script>
        // Initialize Dropzone
        Dropzone.options.fileUploadDropzone = {
            paramName: "files[]", // The name that will be used to transfer the file
            maxFilesize: 10, // MB
            parallelUploads: 200,
            autoProcessQueue: false, // Prevent automatic upload
            acceptedFiles: ".docx,.pdf", // Accept only DOCX and PDF files
            addRemoveLinks: true,
            init: function() {
                var submitButton = document.querySelector("#uploadButton");
                var myDropzone = this;

                submitButton.addEventListener("click", function() {
                    myDropzone.processQueue(); // Start uploading files
                });

                this.on("sending", function(file, xhr, formData) {
                    // Append the assignmentId to the formData
                    formData.append('assignmentId', "<?php echo $_GET['assignmentId']; ?>");
                });

                this.on("queuecomplete", function() {
                    // All files have been uploaded
                    var assignmentId = "<?php echo $_GET['assignmentId']; ?>";
                    var classId = "<?php echo $_GET['classId']; ?>";
                    submitButton.textContent = "Grading in progress... Loading...";
                    submitButton.disabled = true;

                    setTimeout(function() {
                        window.location.href = `view_assignment.php?id=${assignmentId}&classId=${classId}`;
                    }, 5000);
                });

                this.on("success", function(file, response) {
                    console.log(response);
                    if (!response.success) {
                        alert('Failed to upload files: ' + response.message);
                    }
                });

                this.on("error", function(file, response) {
                    console.log(response);
                    if (response == "You can't upload files of this type.") {
                        alert('Invalid file type. Please upload only DOCX or PDF files.');
                    } else {
                        alert('An error occurred while uploading the files. Please try again.');
                    }
                });

                this.on("addedfile", function(file) {
                    // Validate file type on the client side before upload
                    if (!['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf'].includes(file.type)) {
                        this.removeFile(file);
                        alert('Invalid file type detected. Only DOCX and PDF files are allowed.');
                    }
                });
            }
        };
    </script>
</body>
</html>
