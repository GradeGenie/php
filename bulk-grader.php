<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instant Grader | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
        img#refreshBtn {
            width: 15px;
            opacity: 0.6;
            margin-left: 4px;
            cursor: pointer;
        }
        img#refreshBtn:hover,img#refreshBtn:active {
            opacity: 0.8;
        }
        .standardBtn {
            color: #fff;
            background: #12bf1a;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        h3 {
            margin: 5px 0px;
        }
        .error {
            color: red;
            font-size: 1em;
            margin-top: 10px;
        }
        #overlay {
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
            width: 650px!important;
            max-width: 1000px;
            display: none;
            z-index: 20000;
            position: fixed;
            box-sizing: border-box;
            margin: auto;
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
        @media (min-width: 500px) {
            .modal {
                max-width: 700px;
             
            }
        }
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            showRegistrationModal();
        </script>
    <?php endif; ?>
    
    <div id="mainContent">
        <div class="container"> 
            <!-- Step 1: Choose Existing Class -->
            <div class="section">
                <h1>Instant Grader</h1>
                <h4>Do you have your class and assignment ready? These are essential for ensuring accurate grading with the right context.</h4>
<form id="graderForm">
                    <div class="form-group">
                        <h3>Choose an Existing Class</h3>
                        <div style="display: flex; align-items: center;">
                            <select id="classOption" name="classOption" required>
                                <option value="">Choose a Class...</option>
                            </select>
                            <img src="https://static-00.iconduck.com/assets.00/gui-refresh-icon-2048x2048-xgbnerm5.png" onclick="refreshClasses()" id="refreshBtn">
                        </div>
                        <button type="button" class="standardBtn" onclick="showCreateClassModal()">Create a New Class</button>

                        <div class="spacer"></div>
                        <h3>Choose an Existing Assignment</h3>
                        <h4>All assignments belonging to the chosen class are displayed below. The assignment's rubric, instructions, and feedback preferences are automatically applied towards grading.</h4>
                        <div style="display: flex; align-items: center;">
                            <select id="assignmentOption" name="assignmentOption" required>
                                <option value="">Choose an Assignment...</option>
                            </select>
                            <img src="https://static-00.iconduck.com/assets.00/gui-refresh-icon-2048x2048-xgbnerm5.png" onclick="refreshAssignments()" id="refreshBtn">
                        </div>
                        <button type="button" class="standardBtn" onclick="showCreateAssignmentModal()">Create a New Assignment</button>
                        
                        <div class="spacer"></div>
                        <label for="submissions">Upload Submissions</label>
                        <input type="file" id="submissions" name="submissions[]" multiple required accept=".docx,.pdf">


                        <div class="error" id="error-message"></div>

                    </div>
                    <button type="button" onclick="submitGraderForm()" class="button">Start Grading</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="overlay" onclick="closeModals();"></div>
    
    <!-- Create Class Modal -->
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
        
        <button type="button" onclick="createNewClass()" class="button">Create Class</button>
    </div>

    <!-- Create Assignment Modal -->
    <div class="modal" id="createAssignmentModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Create Assignment</h2>
        <form id="assignmentForm">
            <div class="form-group">
                <h3>Assignment Title</h3>
                <input type="text" id="assignmentTitle" placeholder="Assignment Title" name="assignmentTitle" required>
                <div class="spacer"></div>
                <label for="assignmentInstructions">Assignment Instructions</label>
                <textarea id="assignmentInstructions" name="assignmentInstructions" required placeholder="e.g. Write a 500-word essay on the importance of recycling"></textarea>

                <h3>Choose a Class</h3>
                <select id="classOptionAssignment" name="classOption" required>
                    <option value="">Choose a Class...</option>
                </select>

                <div class="spacer"></div>
                <label for="assignmentTitle">Choose a Rubric</label>
                <table>
                    <tr>
                        <td>
                            <h4><span class="highlightedNumber">1</span> Choose a Rubric <img src="https://static-00.iconduck.com/assets.00/gui-refresh-icon-2048x2048-xgbnerm5.png" onclick="refreshRubrics()" id="refreshBtn"></h4>
                            <select id="rubricOption" name="rubricOption" required>
                                <option value="">Choose a Rubric...</option>
                            </select>
                        </td>
                        <td>
                            <h4><span class="highlightedNumber">2</span> Create or Upload a Rubric</h4>
                            <a href="javascript:void(0);" onclick="window.open('upload_rubric.php', '_blank', 'width=600,height=800')"><button class="standardBtn">Upload Rubric</button></a><br><br>
                            <a href="create_rubric.php" target="_blank">Or, Launch AI Rubric Creator &raquo;</a>
                        </td>
                    </tr>
                </table>
                
                <div class="spacer"></div>
                <div class="form-group">
                <label for="gradingInstructions">Grading Instructions</label>
                <textarea id="gradingInstructions" name="gradingInstructions" placeholder="e.g. Don't write more than 50 words, make sure language is kind and supportive"></textarea>
                <div class="spacer"></div>
                <label for="gradingStyle">Feedback Style</label>
                <select id="gradingStyle" name="gradingStyle" required>
                    <option value="">Select Feedback Style...</option>
                    <option value="comprehensive">Comprehensive</option>
                    <option value="brief">Brief</option>
                </select>

            </div>
            <button type="button" onclick="createAssignment()" class="button">Create Class</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            refreshClasses();
            refreshAssignments();
            refreshRubrics();

            // Populate assignments in the select element based on the chosen class
            document.getElementById('classOption').addEventListener('change', function() {
                refreshAssignments();
            });

            // Populate assignment instructions and refresh rubrics based on the chosen assignment
            document.getElementById('assignmentOption').addEventListener('change', function() {
                const assignmentId = this.value;
                fetch(`api/fetch_assignment.php?id=${assignmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('assignmentInstructions').value = data.assignment.instructions;
                            refreshRubrics(); // Refresh rubrics when assignment changes
                        } else {
                            document.getElementById('assignmentInstructions').value = '';
                            console.error('No instructions found.');
                        }
                    })
                    .catch(error => console.error('Error fetching instructions:', error));
            });
        });

        function refreshClasses() {
            fetch('api/fetch_classes.php')
                .then(response => response.json())
                .then(data => {
                    const classSelect = document.getElementById('classOption');
                    const classSelectAssignment = document.getElementById('classOptionAssignment');
                    classSelect.innerHTML = '<option value="">Choose a Class...</option>';
                    classSelectAssignment.innerHTML = '<option value="">Choose a Class...</option>';
                    if (data.success) {
                        data.classes.forEach(cls => {
                            const option = document.createElement('option');
                            option.value = cls.cid;  // Ensure your JSON has the 'cid' key
                            option.textContent = cls.name;  // Ensure your JSON has the 'name' key
                            classSelect.appendChild(option);
                            classSelectAssignment.appendChild(option.cloneNode(true)); // clone for assignment modal
                        });
                    } else {
                        console.error('No classes found.');
                    }
                })
                .catch(error => console.error('Error fetching classes:', error));
        }

        function refreshAssignments() {
            const classId = document.getElementById('classOption').value;
            const assignmentSelect = document.getElementById('assignmentOption');
            assignmentSelect.innerHTML = '<option value="">Choose an Assignment...</option>';
            if (!classId) return;

            fetch(`api/fetch_assignments.php?classId=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.assignments.forEach(assignment => {
                            const option = document.createElement('option');
                            option.value = assignment.aid;  // Ensure your JSON has the 'aid' key
                            option.textContent = assignment.name;  // Ensure your JSON has the 'name' key
                            assignmentSelect.appendChild(option);
                        });
                    } else {
                        console.error('No assignments found.');
                    }
                })
                .catch(error => console.error('Error fetching assignments:', error));
        }

        function refreshRubrics() {
            const rubricSelect = document.getElementById('rubricOption');
            rubricSelect.innerHTML = '<option value="">Choose a Rubric...</option>';
            
            fetch('api/fetch_rubrics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.rubrics.forEach(rubric => {
                            const option = document.createElement('option');
                            option.value = rubric.rid;  // Ensure your JSON has the 'rid' key
                            option.textContent = rubric.title;  // Ensure your JSON has the 'title' key
                            rubricSelect.appendChild(option);
                        });
                    } else {
                        console.error('No rubrics found.');
                    }
                })
                .catch(error => console.error('Error fetching rubrics:', error));
        }

        function submitGraderForm() {
    const assignmentOption = document.getElementById('assignmentOption').value;
    const submissions = document.getElementById('submissions').files;
    const errorMessage = document.getElementById('error-message');

    // Check if all required fields are filled
    if (!assignmentOption || submissions.length === 0) {
        errorMessage.textContent = 'Please fill all mandatory fields!';
        return;
    }

    // Validate file types (only DOCX and PDF allowed)
    for (const file of submissions) {
        const fileType = file.name.split('.').pop().toLowerCase();
        if (!['docx', 'pdf'].includes(fileType)) {
            errorMessage.textContent = 'Only DOCX and PDF files are allowed!';
            return;
        }
    }

    // Create FormData object from the form
    const formData = new FormData(document.getElementById('graderForm'));

    // Send the form data using fetch
    fetch('api/start_grading_instant.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('button').textContent = 'Grading has started. Loading...';
            document.querySelector('button').disabled = true;

            // Wait 3 seconds then redirect
            setTimeout(() => {
                window.location.href = `view_assignment.php?id=${assignmentOption}&classId=${document.getElementById('classOption').value}`;
            }, 3000);
        } else {
            errorMessage.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorMessage.textContent = 'An error occurred. Please try again.';
    });
}


        function showCreateClassModal() {
            $('#overlay').show();
            $('#className').focus();
            $('#createClassModal').show();
        }

        function showCreateAssignmentModal() {
            $('#overlay').show();
            $('#createAssignmentModal').show();
        }

        function closeModals(){
            $('#overlay').hide();
            $('.modal').hide();
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
                        refreshClasses(); // Refresh class lists
                    } else {
                        alert('Failed to create class: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while creating the class.');
                }
            });
        }

        function createAssignment() {
    // Get the form element and retrieve the required field values
    const formElement = document.getElementById('assignmentForm');
const assignmentTitle = formElement.querySelector('#assignmentTitle').value.trim();
const rubricOption = formElement.querySelector('#rubricOption').value.trim();
const classOptionAssignment = formElement.querySelector('#classOptionAssignment').value.trim();
const feedbackStyle = formElement.querySelector('#gradingStyle').value.trim();

console.log({assignmentTitle, rubricOption, classOptionAssignment, feedbackStyle});  // Add this line to debug output


    // Check if all required fields have values
    if (!assignmentTitle || !rubricOption || !classOptionAssignment || !feedbackStyle) {
        alert("Please fill out all required fields: Assignment Title, Rubric Selection, Class Selection, and Feedback Style.");
        return; // Prevent form submission if validation fails
    }

    // Create a new FormData object with the form element
    const formData = new FormData(formElement);

    fetch('api/create_assignment.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeModals();
            refreshAssignments(); // Refresh assignments list
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}



    </script>
</body>
</html>
