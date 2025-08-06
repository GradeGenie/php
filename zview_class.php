<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Class | GradeGenie</title>
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
</style>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    
    <div id="mainContent">
        <!-- put in a back button -->
        <a href="classes.php" class="topLink">&laquo; Back to Classes</a>
        <h2 id="className" class="pageHeading">Class Name</h2> <a class="buttonLink" href="edit_class.php?id=<?php echo $_GET['id']; ?>"><span class="editBtn">EDIT</span></a>
        <p class="headingSubtitle" id="classDetails">Grade 2 Mathematics</p> 

        <h3 class="secondaryHeading">Assignments</h3>
        <div class="rightCTA" onclick="showCreateAssignmentModal()" style="width:170px;margin-top: -45px;">+ Create Assignment</div>
        <table>
            <thead>
                <tr>
                    <th>Assignment Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="assignmentsTable">
                <!-- Assignments will be dynamically loaded here -->
            </tbody>
        </table>
    </div>

    <div id="overlay" onclick="closeModals();"></div>

    <div class="modal" id="createAssignmentModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Create an Assignment</h2>
        <label for="assignmentName">Assignment Name</label><br>
        <input type="text" id="assignmentName" class="modalInput" name="assignmentName" placeholder="Assignment 1" required>
        <div class="formCTA" onclick="createNewAssignment()">Create Assignment</div>
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
});

function fetchClassDetails(classId) {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_class.php',
        data: { id: classId },
        success: function(response) {
            if (response.success) {
                $('#className').text(response.class.name);
                $('#classDetails').text(response.class.level + ' ' + response.class.subject);
                $('#assignmentsTable').empty(); // Clear existing assignments
                
                response.assignments.forEach(function(assignment) {
                    var assignmentRow = '<tr>' +
                        '<td><a href="view_assignment.php?id=' + assignment.aid + '&classId=' + classId + '">' + assignment.name + '</a></td>' +
                        '<td><button class="viewButton" onclick="viewAssignment(' + assignment.aid + ')">View</button></td>' +
                        '</tr>';
                    $('#assignmentsTable').append(assignmentRow);
                });
            } else {
                alert('Failed to load class details: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the class details.');
        }
    });
}

function showCreateAssignmentModal() {
    $('#overlay').show();
    $('#assignmentName').focus();
    $('#createAssignmentModal').show();
}

function closeModals() {
    $('#overlay').hide();
    $('.modal').hide();
}

function createNewAssignment() {
    var urlParams = new URLSearchParams(window.location.search);
    var classId = urlParams.get('id');
    var assignmentName = $('#assignmentName').val();

    if (assignmentName === "") {
        alert("Please fill all required fields!");
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'api/create_assignment.php',
        data: { 
            classId: classId,
            assignmentName: assignmentName
        },
        success: function(response) {
            if (response.success) {
                closeModals();
                fetchClassDetails(classId); // Refresh assignments list
            } else {
                alert('Failed to create assignment: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while creating the assignment.');
        }
    });
}

function viewAssignment(assignmentId) {
    // Redirect to the assignment view page
    window.location.href = 'view_assignment.php?id=' + assignmentId + '&classId=' + <?php echo $_GET['id']; ?>;
}
</script>
</body>
</html>
