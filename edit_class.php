<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
</head>
<style>
/* Include your existing styles */
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
</style>
<body>
    <div id="mainContent">
        <a href="view_assignments.php?id=<?php echo $_GET['id']; ?>" class="topLink">&laquo; Back to Class</a>
        <h2>Edit Class</h2>
        
        <form id="editClassForm">
            <label for="className">Class Name</label><br>
            <input type="text" id="className" name="className" class="modalInput" required><br>
            
            <label for="level">Level</label><br>
            <select id="level" name="level" class="modalInput" required>
                <option value="">Select...</option>
                <option value="Grade 1">Grade 1</option>
                <option value="Grade 2">Grade 2</option>
                <option value="Grade 3">Grade 3</option>
                <option value="Grade 4">Grade 4</option>
                <option value="Grade 5">Grade 5</option>
                <option value="Grade 6">Grade 6</option>
                <option value="Grade 7">Grade 7</option>
                <option value="Grade 8">Grade 8</option>
                <option value="Grade 9">Grade 9</option>
                <option value="Grade 10">Grade 10</option>
                <option value="Grade 11">Grade 11</option>
                <option value="Grade 12">Grade 12+</option>
                <option value="College">College</option>
                <option value="Graduate">Graduate</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="custom_level" class="modalInput hidden" name="custom_level" placeholder="Enter custom level"><br>
            
            <label for="subject">Subject</label><br>
            <select id="subject" name="subject" class="modalInput" required>
                <option value="">Select...</option>
                <option value="Math">Math</option>
                <option value="Science">Science</option>
                <option value="Literature">Literature</option>
                <option value="History">History</option>
                <option value="Geography">Geography</option>
                <option value="Art">Art</option>
                <option value="Music">Music</option>
                <option value="Physical Education">Physical Education</option>
                <option value="Biology">Biology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="Physics">Physics</option>
                <option value="Economics">Economics</option>
                <option value="Political Science">Political Science</option>
                <option value="Sociology">Sociology</option>
                <option value="Psychology">Psychology</option>
                <option value="Philosophy">Philosophy</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Engineering">Engineering</option>
                <option value="Environmental Science">Environmental Science</option>
                <option value="Health">Health</option>
                <option value="Other">Other</option>
            </select>
            <input type="text" id="custom_subject" class="modalInput hidden" name="custom_subject" placeholder="Enter custom subject"><br>
            
            <button type="submit" class="formCTA">Save Changes</button>
        </form>

        <div class="dangerZone">
            <h3>Danger Zone</h3>
            <p>Deleting this class will remove all associated data and cannot be undone. Please proceed with caution.</p>
            <button class="deleteButton" onclick="confirmDelete()">Delete Class</button>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var classId = urlParams.get('id');
        
        if (classId) {
            fetchClassDetails(classId);
        } else {
            alert('Class ID is missing.');
        }

        $('#editClassForm').submit(function(e) {
            e.preventDefault();
            updateClassDetails(classId);
        });

        $('#level').change(function() {
            if ($(this).val() === 'Other') {
                $('#custom_level').removeClass('hidden');
            } else {
                $('#custom_level').addClass('hidden');
            }
        });

        $('#subject').change(function() {
            if ($(this).val() === 'Other') {
                $('#custom_subject').removeClass('hidden');
            } else {
                $('#custom_subject').addClass('hidden');
            }
        });
    });

    function fetchClassDetails(classId) {
        $.ajax({
            type: 'GET',
            url: 'api/fetch_class.php',
            data: { id: classId },
            success: function(response) {
                if (response.success) {
                    $('#className').val(response.class.name);
                    $('#level').val(response.class.level);
                    $('#subject').val(response.class.subject);
                    
                    if (response.class.level === 'Other') {
                        $('#custom_level').removeClass('hidden').val(response.class.custom_level);
                    }
                    if (response.class.subject === 'Other') {
                        $('#custom_subject').removeClass('hidden').val(response.class.custom_subject);
                    }
                } else {
                    alert('Failed to load class details: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while fetching the class details.');
            }
        });
    }

    function updateClassDetails(classId) {
        var data = {
            id: classId,
            name: $('#className').val(),
            level: $('#level').val(),
            custom_level: $('#custom_level').val(),
            subject: $('#subject').val(),
            custom_subject: $('#custom_subject').val()
        };

        $.ajax({
            type: 'POST',
            url: 'api/edit_class.php',
            data: data,
            success: function(response) {
                if (response.success) {
                    alert('Class details updated successfully.');
                    window.location.href = 'view_assignments.php?id=' + classId;
                } else {
                    alert('Failed to update class details: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the class details.');
            }
        });
    }

    function confirmDelete() {
        if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
            deleteClass();
        }
    }

    function deleteClass() {
        var urlParams = new URLSearchParams(window.location.search);
        var classId = urlParams.get('id');

        $.ajax({
            type: 'POST',
            url: 'api/delete_class.php',
            data: { id: classId },
            success: function(response) {
                if (response.success) {
                    alert('Class deleted successfully.');
                    window.location.href = 'classes.php';
                } else {
                    alert('Failed to delete class: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting the class.');
            }
        });
    }
    </script>
</body>
</html>
