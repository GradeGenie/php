<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        .modalInput {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .formCTA {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 20px;
        }
        .dangerZone {
            margin-top: 50px;
            padding: 20px;
            border: 1px solid #ff4c4c;
            background-color: #ffe6e6;
            border-radius: 5px;
        }
        .dangerZone h3 {
            color: #ff4c4c;
        }
        .deleteButton {
            background-color: #ff4c4c;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .topLink{
            color: #28a745;
            text-decoration: none;
            display: block;
        }
    </style>
</head>
<body>
    
    
    <div id="mainContent">
        <a href="view_assignment.php?id=<?php echo $_GET['id']; ?>&classId=<?php echo $_GET['class']; ?>" class="topLink">&laquo; Back to Assignment</a>
        <h2>Edit Assignment</h2>
        
        <form id="editAssignmentForm">
            <label for="assignmentName">Assignment Title</label><br>
            <input type="text" id="assignmentName" name="assignmentName" class="modalInput" required><br>
            
            <label for="details">Assignment Instructions / Details</label><br>
            <textarea id="details" name="details" class="modalInput"></textarea><br>
            
            <label for="instructions">Grading Instructions</label><br>
            <textarea id="instructions" name="instructions" class="modalInput"></textarea><br>
            
            <label for="rubric">Choose a rubric</label><br>
            <select id="rubric" name="rubric" class="modalInput" required>
                <!-- Rubrics will be populated here -->
            </select><br>

            <label for="style">Feedback Style</label><br>
            <select id="style" name="style" class="modalInput" required>
                <option value="">Select...</option>
                <option value="brief">Brief</option>
                <option value="comprehensive">Comprehensive</option>
            </select><br>

            <label for="extra_details">Extra Details</label><br>
            <textarea id="extra_details" name="extra_details" class="modalInput"></textarea><br>
            
            <button type="submit" class="formCTA">Save Changes</button>
        </form>

        <div class="dangerZone">
            <h3>Danger Zone</h3>
            <p>Deleting this assignment will remove all associated data and cannot be undone. Please proceed with caution.</p>
            <button class="deleteButton" onclick="confirmDelete()">Delete Assignment</button>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var assignmentId = urlParams.get('id');
        var classId = urlParams.get('class');
        
        if (assignmentId && classId) {
            fetchAssignmentDetails(assignmentId, function() {
                fetchRubrics(function() {
                    $('#rubric').val($('#rubric').attr('data-selected'));
                });
            });
        } else {
            alert('Assignment ID or Class ID is missing.');
        }

        $('#editAssignmentForm').submit(function(e) {
            e.preventDefault();
            updateAssignmentDetails(assignmentId, classId);
        });
    });

    function fetchAssignmentDetails(assignmentId, callback) {
        $.ajax({
            type: 'GET',
            url: 'api/fetch_assignment.php',
            data: { id: assignmentId },
            dataType: 'json', // Ensure we receive JSON response
            success: function(response) {
                if (response.success) {
                    $('#assignmentName').val(response.assignment.name);
                    $('#details').val(response.assignment.details);
                    $('#instructions').val(response.assignment.instructions);
                    $('#rubric').attr('data-selected', response.assignment.rubric); // Store the selected rubric value
                    $('#style').val(response.assignment.style.toLowerCase()); // Ensure it matches the options in the dropdown
                    $('#extra_details').val(response.assignment.extra_details);
                    if (callback) callback();
                } else {
                    alert('Failed to load assignment details: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while fetching the assignment details.');
            }
        });
    }

    function fetchRubrics(callback) {
        document.getElementById('rubric').innerHTML = '<option value="">Choose a Rubric...</option>';
        fetch('api/fetch_rubrics.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const rubricSelect = document.getElementById('rubric');
                    data.rubrics.forEach(rubric => {
                        const option = document.createElement('option');
                        option.value = rubric.rid;  // Ensure your JSON has the 'rid' key
                        option.textContent = rubric.title;  // Ensure your JSON has the 'name' key
                        rubricSelect.appendChild(option);
                    });
                    if (callback) callback();
                } else {
                    console.error('No rubrics found.');
                }
            })
            .catch(error => console.error('Error fetching rubrics:', error));
    }

    function updateAssignmentDetails(assignmentId, classId) {
        var data = {
            id: assignmentId,
            name: $('#assignmentName').val(),
            details: $('#details').val(),
            instructions: $('#instructions').val(),
            rubric: $('#rubric').val(),
            style: $('#style').val(),
            extra_details: $('#extra_details').val()
        };

        $.ajax({
            type: 'POST',
            url: 'api/edit_assignment.php',
            data: data,
            dataType: 'json', // Specify that we're expecting JSON
            success: function(response) {
                if (response.success) {
                    alert('Assignment details updated successfully.');
                    window.location.href = 'view_assignment.php?id=' + assignmentId + '&classId=' + classId;
                } else {
                    alert('Failed to update assignment details: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the assignment details.');
            }
        });
    }

    function confirmDelete() {
        if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
            deleteAssignment();
        }
    }

    function deleteAssignment() {
        var urlParams = new URLSearchParams(window.location.search);
        var assignmentId = urlParams.get('id');
        var classId = urlParams.get('class');

        $.ajax({
            type: 'POST',
            url: 'api/delete_assignment.php',
            data: { id: assignmentId },
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    alert('Assignment deleted successfully.');
                    window.location.href = 'view_assignments.php?id=' + classId;
                } else {
                    alert('Failed to delete assignment: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting the assignment.');
            }
        });
    }
    </script>
</body>
</html>
