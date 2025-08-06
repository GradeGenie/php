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
    max-width: 600px;
    width: 100%;
    box-sizing: border-box;
    display: none;
    position: relative;
    z-index: 20000;
    margin: auto;
}

.modal h2 {
    margin-top: 0;
    font-size: 1.5em;
    text-align: center;
}

.modalInput {
    margin-top: 10px;
    width: calc(100% - 20px); /* Prevent overflow */
    padding: 5px;
    font-size: 1em;
}

.modal select, .modal input, .modal textarea {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.standardBtn {
    background-color: #12bf1a;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    width: 100%;
}

.closeModal {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 1.5em;
    cursor: pointer;
}

img#refreshBtn {
            width: 15px;
            opacity: 0.6;
            margin-left: 4px;
            cursor: pointer;
        }
        img#refreshBtn:hover, img#refreshBtn:active {
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
            margin: 5px 0;
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
            width: 100%;
            max-width: 650px;
            margin: auto;
            display: none;
            z-index: 20000;
            position: relative;
            box-sizing: border-box;
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
    </style>
</head>
<body>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            $(document).ready(function() {
                showRegistrationModal();
            });
        </script>
    <?php endif; ?>
    
    <div id="mainContent">
        <div class="container">
            <!-- Step 1: Choose Existing Class -->
            <div class="section">
                <h1>Instant Grader</h1>
                <h4>Do you have your class & assignment ready? You will need them for Instant Grader to work.</h4>
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
                        <input type="file" id="submissions" name="submissions[]" multiple required>

                        <div class="error" id="error-message"></div>

                    </div>
                    <button type="button" onclick="submitGraderForm()" class="standardBtn">Start Grading</button>
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
            <!-- Add more options here -->
            <option value="Other">Other</option>
        </select>
        <input type="text" id="custom_level" class="modalInput hidden" name="custom_level" placeholder="Enter custom level">
        <br>
        <label for="subject">Subject</label><br>
        <select name="subject" id="subject" class="modalInput" required>
            <option value="">Select...</option>
            <option value="Math">Math</option>
            <!-- Add more options here -->
            <option value="Other">Other</option>
        </select>
        <input type="text" id="custom_subject" class="modalInput hidden" name="custom_subject" placeholder="Enter custom subject">
        
        <button type="button" onclick="createNewClass()" class="standardBtn">Create Class</button>
    </div>

    <!-- Create Assignment Modal -->
    <div class="modal" id="createAssignmentModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2>Create Assignment</h2>
        <form id="assignmentForm">
            <div class="form-group">
                <h3>Assignment Title</h3>
                <input type="text" id="assignmentTitle" class="modalInput" name="assignmentTitle" placeholder="Assignment Title" required>
                <div class="spacer"></div>
                <label for="assignmentInstructions">Assignment Instructions</label>
                <textarea id="assignmentInstructions" class="modalInput" name="assignmentInstructions" required placeholder="e.g. Write a 500-word essay on the importance of recycling"></textarea>

                <h3>Choose a Class</h3>
                <select id="classOptionAssignment" class="modalInput" name="classOption" required>
                    <option value="">Choose a Class...</option>
                </select>

                <div class="spacer"></div>
                <h4>Choose a Rubric</h4>
                <div style="display: flex; align-items: center;">
                    <select id="rubricOption" class="modalInput" name="rubricOption" required>
                        <option value="">Choose a Rubric...</option>
                    </select>
                    <img src="https://static-00.iconduck.com/assets.00/gui-refresh-icon-2048x2048-xgbnerm5.png" onclick="refreshRubrics()" id="refreshBtn">
                </div>
                <a href="javascript:void(0);" onclick="window.open('upload_rubric.php', '_blank', 'width=600,height=800')"><button type="button" class="standardBtn">Upload Rubric</button></a><br><br>
                <a href="create_rubric.php" target="_blank">Or, Launch AI Rubric Creator &raquo;</a>
                
                <div class="spacer"></div>
                <label for="gradingInstructions">Grading Instructions</label>
                <textarea id="gradingInstructions" class="modalInput" name="gradingInstructions" placeholder="e.g. Don't write more than 50 words, make sure language is kind and supportive"></textarea>
                <div class="spacer"></div>
                <label for="gradingStyle">Feedback Style</label>
                <select id="gradingStyle" class="modalInput" name="gradingStyle" required>
                    <option value="">Select Feedback Style...</option>
                    <option value="comprehensive">Comprehensive</option>
                    <option value="brief">Brief</option>
                </select>

            </div>
            <button type="button" onclick="createAssignment()" class="standardBtn">Create Assignment</button>
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

            if (!assignmentOption || submissions.length === 0) {
                document.getElementById('error-message').textContent = 'Please fill all mandatory fields!';
                return;
            }

            const formData = new FormData(document.getElementById('graderForm'));

            fetch('api/start_grading_instant.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelector('.standardBtn').textContent = 'Grading has started. Loading...';
                    document.querySelector('.standardBtn').disabled = true;
                    // wait 3 seconds then redirect them
                    setTimeout(() => {
                        window.location.href = `view_assignment.php?id=${assignmentOption}&classId=${document.getElementById('classOption').value}`;
                        
                    }, 3000);
                } else {
                    document.getElementById('error-message').textContent = data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('error-message').textContent = 'An error occurred. Please try again.';
            });
        }

        function showCreateClassModal() {
            $('#overlay').show();
            $('#createClassModal').css('display', 'flex').hide().fadeIn();
            $('#className').focus();
        }

        function showCreateAssignmentModal() {
            $('#overlay').show();
            $('#createAssignmentModal').css('display', 'flex').hide().fadeIn();
        }

        function closeModals(){
            $('#overlay').fadeOut();
            $('.modal').fadeOut();
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
            const formElement = document.getElementById('assignmentForm');
            const assignmentTitle = formElement.querySelector('#assignmentTitle').value.trim();
            const rubricOption = formElement.querySelector('#rubricOption').value.trim();
            const classOptionAssignment = formElement.querySelector('#classOptionAssignment').value.trim();
            const feedbackStyle = formElement.querySelector('#gradingStyle').value.trim();

            if (!assignmentTitle || !rubricOption || !classOptionAssignment || !feedbackStyle) {
                alert("Please fill out all required fields.");
                return;
            }

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
