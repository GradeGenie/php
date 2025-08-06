<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Rubric | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <style>
        .hidden {
            display: none;
        }
        .formField {
            margin-bottom: 10px;
        }
        .formField label {
            display: block;
            margin-bottom: 5px;
        }
        .formField input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .button {
            background: #28a745;
            color: #fff;
            font-weight: bold;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            float: left;
        }
        .pageHeading {
            margin-bottom: 20px;
        }
        .processingMessage {
            display: none;
            color: #888;
            font-style: italic;
        }
        .topLink{
            color: #28a745;
            text-decoration: none;
            display: block;
        }
        textarea {
            resize: none;
            width: 450px;
        }
        @media (max-width: 700px) {
            nav {
                display: none!important;
            }
            .topLink {
                display: none!important;
            }
        }
    </style>
</head>
<body>
    <div id="mainContent">
        <a href="view_rubrics.php" class="topLink">&laquo; Back to Rubrics</a>
        <h1 class="pageHeading">Upload Rubric</h1>
        <form id="uploadForm" method="post" enctype="multipart/form-data">
            <div class="formField">
                <label for="file">Upload a DOCX or PDF file:</label>
                <input type="file" id="file" name="file" accept=".pdf,.docx" required>
            </div>
            <button type="submit" class="button">Upload and Process</button>
            <div class="processingMessage">Processing... Please wait... This may take up to 1-2 minutes...</div>
        </form>
        <div id="response"></div>
    </div>
    <script>
        $(document).ready(function() {
            $('#uploadForm').on('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $('.button').hide();
                $('.processingMessage').show();

                $.ajax({
                    type: 'POST',
                    url: 'api/upload_rubric.php',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#uploadForm').hide();
                        $('#response').html(response);
                    },
                    error: function() {
                        alert('An error occurred while processing the rubric.');
                    }
                });
            });
        });
    </script>
</body>
</html>
