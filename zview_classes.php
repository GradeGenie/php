<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<style>
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
    margin-top: -78px;
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
</style>
<body>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <div id="overlay" onclick="closeModals();"></div>
    
    <div class="modal" id="createClassModal">
    <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Create a Class</h2>
        <label for="className">Class Name</label><br>
        <input type="text" id="className" class="modalInput" name="className" placeholder="Geography 9A" required>
        <br>
        <label for="level">Level</label><br>
        <select id="level" class="modalInput" name="level">
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
        <input type="text" id="custom_level" class="modalInput hidden" name="custom_level" placeholder="Enter custom level">
        <br>
        <label for="subject">Subject</label><br>
        <select name="subject" id="subject" class="modalInput" required="">
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
        <input type="text" id="custom_subject" class="modalInput hidden" name="custom_subject" placeholder="Enter custom subject">
        
        <div class="formCTA" onclick="createNewClass()">Create Class</div>
    </div>

    <div id="mainContent">
    <h2>My Classes</h2>
    <p class="headingSubtitle">Here are the classes you are teaching</p>
    <div id="createClassBtn" class="rightCTA" onclick="showCreateModal()">+ New Class</div>
    <div id="classCardParent">
        <!-- Classes will be dynamically loaded here -->
    </div>
    </div>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            showRegistrationModal();
        </script>
    <?php endif; ?>

<script>
$(document).ready(function() {
    fetchClasses();

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

function showCreateModal() {
    $('#overlay').show();
    $('#className').focus();
    $('#createClassModal').show();
}

function closeModals(){
    $('#overlay').hide();
    $('.modal').hide();
}

function fetchClasses() {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_classes.php',
        success: function(response) {
            if (response.success) {
                $('#classCardParent').empty(); // Clear existing class cards
                response.classes.forEach(function(classItem) {
                    var classCard = '<a href="view_assignments.php?id='+ classItem.cid +'"><div class="classCard"><span class="classCard_name">' + classItem.name + '</span><br><span class="classCard_level">' + classItem.level + '</span> <span class="classCard_subject">' + classItem.subject + '</span></div></a>';
                    $('#classCardParent').append(classCard);
                });
            } else {
                alert('Failed to load classes: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the classes.');
        }
    });
}

function createNewClass() {
    var className = $('#className').val();
    var level = $('#level').val();
    var customLevel = $('#custom_level').val();
    var subject = $('#subject').val();
    var customSubject = $('#custom_subject').val();
    subject = subject === "Other" ? customSubject : subject;
    level = level === "Other" ? customLevel : level;

    if (className === "" || (level === "Other" && customLevel === "") || (subject === "Other" && customSubject === "")) {
        alert("Please fill all required fields!");
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'api/create_class.php',
        data: { 
            className: className,
            level: level,
            subject: subject
        },
        success: function(response) {
            if (response.success) {
                closeModals();
                var classId = response.classId; // Get the class ID from the response
                populateNewClass(classId, className, level, subject);
            } else {
                alert('Failed to create class: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while creating the class.');
        }
    });
}

function populateNewClass(classId, className, level, subject) {
    var newClassCard = '<a href="view_assignments.php?id=' + classId + '"><div class="classCard"><span class="classCard_name">' + className + '</span><br><span class="classCard_level">' + level + '</span> <span class="classCard_subject">' + subject + '</span></div></a>';
    $('#classCardParent').append(newClassCard);
}
</script>
</body>
</html>
