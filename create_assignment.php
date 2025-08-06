<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
<?php include 'menu.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assignment | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
</head>
<style>
img#refreshBtn {
    width: 11px;
    opacity: 0.6;
    margin-left: 4px;
    cursor: pointer;
}
img#refreshBtn:hover,img#refreshBtn:active {
    opacity: 0.8;
}
a {
    color: #19A37E;
}
.standardBtn {
    color: #fff;
    background: #19A37E;
    border: none;
    padding: 7px 12px;
    border-radius: 6px;
    cursor: pointer;
}
h3 {
    margin: 5px 0px;
}
</style>
<body>
  

    <?php if (!isset($_SESSION['user_id'])): ?>
        <?php include 'registration_modal.php'; ?>
        <script>
            showRegistrationModal();
        </script>
    <?php endif; ?>
    
    <div id="mainContent">
        <div class="container"> 
            <!-- Step 1: Create Assignment -->
            <div class="section">
                <h1>Create Assignment</h1>
                <form id="assignmentForm">
                    <div class="form-group">
                        <h3>Choose an Assignment Title*</h3>
                        <input type="text" id="assignmentTitle" placeholder="Amazing Assignment Title" name="assignmentTitle" required>
                        <div class="spacer"></div>
                        <label for="assignmentInstructions">Assignment Instructions (Optional)</label>
                        <textarea id="assignmentInstructions" name="assignmentInstructions" required placeholder="e.g. Write a 500-word essay on the importance of recycling"></textarea>

                        <h3>Choose a Class*</h3>
                        <select id="classOption" name="classOption" required>
                            <option value="">Choose a Class...</option>
                        </select>

                        <div class="spacer"></div>
                        <label for="assignmentTitle">Choose a Rubric*</label>
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
                        <label for="gradingInstructions">Grading Instructions (Optional)</label>
                        <textarea id="gradingInstructions" name="gradingInstructions" placeholder="e.g. Don't write more than 50 words, make sure language is kind and supportive"></textarea>
                        <div class="spacer"></div>
                        <label for="gradingStyle">Feedback Style*</label>
                        <select id="gradingStyle" name="gradingStyle" required>
                            <option value="">Select...</option>
                            <option value="comprehensive">Comprehensive</option>
                            <option value="brief">Brief</option>
                        </select>

                    </div>
                    <button type="button" onclick="createAssignment()" class="button">Create Assignment</button>
                </form>
            </div>

        </div>
    </div>

    <script>
        function refreshRubrics(){
            // make sure we also have "Choose a Rubric..." option
            document.getElementById('rubricOption').innerHTML = '<option value="">Choose a Rubric...</option>';
            fetch('api/fetch_rubrics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const rubricSelect = document.getElementById('rubricOption');
                        data.rubrics.forEach(rubric => {
                            const option = document.createElement('option');
                            option.value = rubric.rid;  // Ensure your JSON has the 'rid' key
                            option.textContent = rubric.title;  // Ensure your JSON has the 'name' key
                            rubricSelect.appendChild(option);
                        });
                    } else {
                        console.error('No rubrics found.');
                    }
                })
                .catch(error => console.error('Error fetching rubrics:', error));
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Populate rubrics in the select element
            fetch('api/fetch_rubrics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const rubricSelect = document.getElementById('rubricOption');
                        data.rubrics.forEach(rubric => {
                            const option = document.createElement('option');
                            option.value = rubric.rid;  // Ensure your JSON has the 'rid' key
                            option.textContent = rubric.title;  // Ensure your JSON has the 'name' key
                            rubricSelect.appendChild(option);
                        });
                    } else {
                        console.error('No rubrics found.');
                    }
                })
                .catch(error => console.error('Error fetching rubrics:', error));

            // Populate classes in the select element
            fetch('api/fetch_classes.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const classSelect = document.getElementById('classOption');
                        data.classes.forEach(cls => {
                            const option = document.createElement('option');
                            option.value = cls.cid;  // Ensure your JSON has the 'cid' key
                            option.textContent = cls.name;  // Ensure your JSON has the 'name' key
                            classSelect.appendChild(option);
                        });
                        // Automatically select class if classId param exists in the URL
                        const urlParams = new URLSearchParams(window.location.search);
                        const classId = urlParams.get('classId');
                        if (classId) {
                            classSelect.value = classId;
                        }
                    } else {
                        console.error('No classes found.');
                    }
                })
                .catch(error => console.error('Error fetching classes:', error));

            

            document.getElementById('assignmentForm').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);
                fetch('api/create_assignment.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const classId = document.getElementById('classOption').value;
                        window.location.href = `view_assignment.php?id=${data.assignmentId}&classId=${classId}`;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        function createAssignment() {
            const formData = new FormData(document.getElementById('assignmentForm'));
            fetch('api/create_assignment.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const classId = document.getElementById('classOption').value;
                    window.location.href = `view_assignment.php?id=${data.assignmentId}&classId=${classId}`;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
