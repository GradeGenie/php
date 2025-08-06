<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Class | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
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
    background: #19A37E;
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
    background: #19A37E;
    text-align: center;
    padding: 10px 12px;
    color: #fff;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    font-size:16px;
    display: inline-block;
    margin: 10px 5px;
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
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-height: 90%;
    overflow-y: auto;
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
    background-color: #19A37E;
    color: white;
    padding: 5px 10px;
    text-align: center;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.topLink{
    color: #19A37E;
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
    <div id="mainContent">
        <!-- put in a back button -->
        <a href="classes.php" class="topLink">&laquo; Back to Classes</a>
        <h2 id="className" class="pageHeading">Class Name</h2> <a class="buttonLink" href="edit_class.php?id=<?php echo $_GET['id']; ?>"><span class="editBtn">EDIT</span></a>
        <p class="headingSubtitle" id="classDetails">Grade 2 Mathematics</p> 

        <h3 class="secondaryHeading">Assignments</h3>
        <a href="create_assignment.php?classId=<?php echo $_GET['id']; ?>"><div class="rightCTA" style="width:190px;margin-top: -45px;">+ Create Assignment</div></a>
        
        <h3 class="secondaryHeading">Syllabus</h3>
        <div id="syllabus-actions">
            <div class="formCTA" onclick="generateNewSyllabus(<?php echo $_GET['id']; ?>)">Generate New Syllabus</div>
            <div class="formCTA" id="view-saved-syllabi" style="display:none;" onclick="viewSavedSyllabi(<?php echo $_GET['id']; ?>)">View Saved Syllabi</div>
        </div>
        <div id="syllabus-container" class="hidden"></div>
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
        <form id="createAssignmentForm" enctype="multipart/form-data">
            <label for="assignmentName">Assignment Name</label><br>
            <input type="text" id="assignmentName" class="modalInput" name="assignmentName" placeholder="Assignment 1" required><br>
            
            <label for="gradingInstructions">Grading Instructions</label><br>
            <textarea id="gradingInstructions" class="modalInput" name="gradingInstructions"></textarea><br>

            <label for="rubric">Choose an existing rubric</label><br>
            <select id="rubric" name="rubric" class="modalInput">
                <!-- Populate with rubrics from database -->
            </select><br>

            <label>Or upload a new rubric</label><br>
            <input type="file" id="uploadRubric" name="uploadRubric" class="modalInput"><br>

            <label for="feedbackStyle">Feedback Style</label><br>
            <select id="feedbackStyle" name="feedbackStyle" class="modalInput" required>
                <option value="Brief">Brief</option>
                <option value="Comprehensive">Comprehensive</option>
            </select><br>

            <label for="extraInstructions">Extra Instructions</label><br>
            <textarea id="extraInstructions" class="modalInput" name="extraInstructions"></textarea><br>

            <div class="formCTA" onclick="createNewAssignment()">Create Assignment</div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var classId = urlParams.get('id');
            if (classId) {
                fetchClassDetails(classId);
                fetchRubrics();
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

        function fetchRubrics() {
            $.ajax({
                type: 'GET',
                url: 'api/fetch_rubrics.php',
                success: function(response) {
                    if (response.success) {
                        console.log(response.rubrics); // Debug: log rubrics data
                        response.rubrics.forEach(function(rubric) {
                            var option = '<option value="' + rubric.id + '">' + rubric.name + '</option>';
                            $('#rubric').append(option);
                        });
                    } else {
                        // Silently handle the case when no rubrics are found
                        console.log('No rubrics found: ' + response.message);
                        // Add a default option
                        $('#rubric').append('<option value="">No rubrics available - create one first</option>');
                    }
                },
                error: function() {
                    console.log('An error occurred while fetching the rubrics.');
                    // Add a default option instead of showing an error
                    $('#rubric').append('<option value="">No rubrics available</option>');
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
            var formData = new FormData(document.getElementById('createAssignmentForm'));
            var urlParams = new URLSearchParams(window.location.search);
            var classId = urlParams.get('id');
            formData.append('classId', classId);

            $.ajax({
                type: 'POST',
                url: 'api/create_assignment.php',
                data: formData,
                contentType: false,
                processData: false,
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
        
        // Syllabus Functions
        function generateNewSyllabus(classId) {
            window.location.href = 'syllabus_generator.php?class_id=' + classId;
        }
        
        function viewSavedSyllabi(classId) {
            $('#syllabusModalTitle').text('Your Saved Syllabi');
            $('#syllabusModalContent').html('<p>Loading syllabi...</p>');
            $('#overlay').show();
            $('#syllabusModal').show();
            
            // Load available syllabi for this class
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?class_id=' + classId,

                success: function(response) {
                    if (response.length > 0) {
                        var syllabusListHTML = '<div class="syllabi-list">';
                        response.forEach(function(syllabus) {
                            var createdDate = new Date(syllabus.created_on).toLocaleDateString();
                            syllabusListHTML += `
                                <div class="syllabus-item" data-id="${syllabus.id}">
                                    <div class="syllabus-info">
                                        <h4>${syllabus.title}</h4>
                                        <p>Created: ${createdDate}</p>
                                    </div>
                                    <div class="syllabus-actions">
                                        <button class="viewButton" onclick="viewSyllabus(${syllabus.id})">View</button>
                                        <button class="viewButton" onclick="editSyllabus(${syllabus.id}, ${classId})">Edit</button>
                                        <button class="viewButton" onclick="deleteSyllabus(${syllabus.id}, ${classId})">Delete</button>
                                    </div>
                                </div>
                            `;
                        });
                        syllabusListHTML += '</div>';
                        $('#syllabusModalContent').html(syllabusListHTML);
                    } else {
                        $('#syllabusModalContent').html('<p>No syllabi found for this class. Please generate a new syllabus first.</p>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading syllabi:', xhr);
                    $('#syllabusModalContent').html('<p>Error loading syllabi. Please try again later.</p>');
                }
            });
        }
        
        function viewSyllabus(syllabusId) {
            // Fetch syllabus content and display in modal
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?id=' + syllabusId,

                success: function(syllabus) {
                    $('#syllabusModalTitle').text(syllabus.title);
                    $('#syllabusModalContent').html(`
                        <div class="syllabus-content">
                            ${syllabus.content}
                        </div>
                        <div class="modal-actions">
                            <button class="viewButton" onclick="printSyllabus(${syllabusId})">Print</button>
                            <button class="viewButton" onclick="editSyllabus(${syllabusId}, ${syllabus.class_id})">Edit</button>
                            <button class="viewButton" onclick="viewSavedSyllabi(${syllabus.class_id})">Back to List</button>
                        </div>
                    `);
                },
                error: function(xhr) {
                    console.error('Error loading syllabus:', xhr);
                    $('#syllabusModalContent').html('<p>Error loading syllabus. Please try again later.</p>');
                }
            });
        }
        
        function editSyllabus(syllabusId, classId) {
            // Redirect to syllabus generator with the syllabus ID for editing
            // Make sure classId is valid
            if (!classId || classId === 'undefined') {
                // Get the class ID from the URL if it's not provided
                var urlParams = new URLSearchParams(window.location.search);
                classId = urlParams.get('id');
            }
            
            // Ensure we have a valid class ID
            if (!classId || classId === 'undefined') {
                console.error('No valid class ID found for editing syllabus');
                alert('Error: Cannot edit syllabus without a valid class ID');
                return;
            }
            
            window.location.href = 'syllabus_generator.php?class_id=' + classId + '&syllabus_id=' + syllabusId;
        }
        
        function printSyllabus(syllabusId) {
            // Open syllabus in new window for printing
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?id=' + syllabusId,

                success: function(syllabus) {
                    const printWindow = window.open('', '_blank');
                    printWindow.document.write(`
                        <html>
                        <head>
                            <title>${syllabus.title}</title>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .syllabus-header { text-align: center; margin-bottom: 20px; }
                                .syllabus-section { margin-bottom: 20px; }
                                .syllabus-section h2 { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                                th { background-color: #f8f9fa; text-align: left; padding: 8px; }
                                td { padding: 8px; border-bottom: 1px solid #eee; }
                            </style>
                        </head>
                        <body>
                            ${syllabus.content}
                        </body>
                        </html>
                    `);
                    printWindow.document.close();
                    printWindow.print();
                },
                error: function(xhr) {
                    console.error('Error loading syllabus for printing:', xhr);
                    alert('Error loading syllabus. Please try again later.');
                }
            });
        }
        
        function attachSyllabus(syllabusId, classId) {
            // Here you would typically update the database to associate this syllabus with the class
            // For now, we'll just display it on the page
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?id=' + syllabusId,

                success: function(syllabus) {
                    closeModals();
                    $('#syllabus-container').removeClass('hidden');
                    $('#syllabus-container').html(`
                        <div class="attached-syllabus">
                            <h4>${syllabus.title}</h4>
                            <p>Last updated: ${new Date(syllabus.updated_on).toLocaleDateString()}</p>
                            <button class="viewButton" onclick="viewSyllabus(${syllabusId})">View Syllabus</button>
                            <button class="viewButton" onclick="editSyllabus(${syllabusId}, ${classId})">Edit Syllabus</button>
                        </div>
                    `);
                },
                error: function(xhr) {
                    console.error('Error attaching syllabus:', xhr);
                    alert('Error attaching syllabus. Please try again later.');
                }
            });
        }
        
        function editSyllabus(syllabusId, classId) {
            window.location.href = 'syllabus_generator.php?class_id=' + classId + '&syllabus_id=' + syllabusId;
        }
        
        function deleteSyllabus(syllabusId, classId) {
            // Make sure we have a valid class ID
            if (!classId || classId === 'undefined') {
                // Get the class ID from the URL if it's not provided
                var urlParams = new URLSearchParams(window.location.search);
                classId = urlParams.get('id');
            }
            
            // Use a more reliable confirmation dialog
            var confirmDelete = confirm('Are you sure you want to delete this syllabus? This action cannot be undone.');
            if (!confirmDelete) {
                return; // User canceled the deletion
            }
            
            console.log('Deleting syllabus ID:', syllabusId, 'for class ID:', classId);
            
            // Delete syllabus from API
            fetch('api/syllabus-delete.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: syllabusId
                })
            })
            .then(response => {
                console.log('Delete response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                if (data.message === 'Syllabus deleted successfully') {
                    // Reload syllabi list
                    viewSavedSyllabi(classId);
                } else {
                    alert('Error deleting syllabus: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting syllabus:', error);
                alert('Error deleting syllabus. Please try again later.');
            });
        }
        
        function loadSyllabi(classId) {
            console.log('Starting loadSyllabi function for class ID:', classId);
            
            // Load any attached syllabi for this class
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?class_id=' + classId,
                dataType: 'json',
                cache: false, // Prevent caching
                
                beforeSend: function() {
                    console.log('Sending request to fetch syllabi for class ID:', classId);
                },

                success: function(response) {
                    console.log('Raw API response:', response);
                    
                    if (response && Array.isArray(response) && response.length > 0) {
                        console.log('Found syllabi:', response);
                        
                        // Show the View Saved Syllabi button since we have syllabi
                        $('#view-saved-syllabi').show();
                        
                        // We have syllabi, but we'll just show the buttons and not display the syllabus directly
                        // Keep the syllabus container hidden
                        $('#syllabus-container').addClass('hidden');
                    } else {
                        console.log('No syllabi found for class ID:', classId);
                        // Hide the View Saved Syllabi button since there are no syllabi
                        $('#view-saved-syllabi').hide();
                        $('#syllabus-container').addClass('hidden');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading syllabi. Status:', status);
                    console.error('Error details:', error);
                    console.error('Response:', xhr.responseText);
                    
                    // If error is 401 Unauthorized, we'll just not show any syllabi
                    if (xhr.status !== 401) {
                        console.error('Error loading syllabi:', xhr);
                    }
                    
                    // Hide the View Saved Syllabi button since there was an error
                    $('#view-saved-syllabi').hide();
                    $('#syllabus-container').addClass('hidden');
                }
            });
        }
        
        // Function to directly check if a syllabus exists for debugging
        function checkSyllabus(classId) {
            console.log('Directly checking if syllabus exists for class ID:', classId);
            // Make a direct fetch request to the API
            fetch('api/syllabus-get.php?class_id=' + classId + '&debug=true&_=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            })
            .then(response => {
                console.log('Raw response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Raw API response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    return null;
                }
            })
            .then(data => {
                console.log('Parsed syllabus data:', data);
                if (data && Array.isArray(data) && data.length > 0) {
                    console.log('Syllabus found! ID:', data[0].id, 'Title:', data[0].title);
                    // Removed debug alert
                } else {
                    console.log('No syllabi found in direct check');
                    // Removed debug alert
                }
            })
            .catch(error => {
                console.error('Error in direct check:', error);
                // Removed debug alert
            });
        }
        
        // Function to force reload syllabi with cache busting
        function forceReloadSyllabi(classId) {
            console.log('Force reloading syllabi with cache busting');
            // First run a direct check
            checkSyllabus(classId);
            
            // Add a timestamp to prevent caching
            $.ajax({
                type: 'GET',
                url: 'api/syllabus-get.php?class_id=' + classId + '&_=' + new Date().getTime(),
                dataType: 'json',
                cache: false,
                success: function(response) {
                    console.log('Force reload response type:', typeof response);
                    console.log('Force reload response:', response);
                    if (response && Array.isArray(response) && response.length > 0) {
                        // Show the View Saved Syllabi button since we have syllabi
                        $('#view-saved-syllabi').show();
                        
                        // We have syllabi, but we'll just show the buttons and not display the syllabus directly
                        // Keep the syllabus container hidden
                        $('#syllabus-container').addClass('hidden');
                    } else {
                        console.log('No syllabi found after force reload');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error during force reload:', error);
                }
            });
        }
        
        // Load syllabi when page loads
        $(document).ready(function() {
            var urlParams = new URLSearchParams(window.location.search);
            var classId = urlParams.get('id');
            var syllabusSaved = urlParams.get('syllabus_saved');
            
            if (classId) {
                console.log('Loading syllabi for class ID:', classId);
                if (syllabusSaved === 'true') {
                    console.log('Syllabus was just saved, using force reload');
                    forceReloadSyllabi(classId);
                } else {
                    loadSyllabi(classId);
                }
            }
        });
    </script>
    <!-- Add the syllabus modal -->
    <div class="modal" id="syllabusModal">
        <span class="closeModal" onclick="closeModals();">&times;</span>
        <h2 id="syllabusModalTitle">Syllabus</h2>
        <div id="syllabusModalContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
    
    <style>
    /* Syllabus Styles */
    .syllabi-list {
        margin-top: 15px;
    }
    
    .syllabus-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        background-color: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .syllabus-info h4 {
        margin: 0 0 5px 0;
        color: #2c3e50;
    }
    
    .syllabus-info p {
        margin: 0;
        color: #7f8c8d;
        font-size: 0.9rem;
    }
    
    .syllabus-actions {
        display: flex;
        gap: 10px;
    }
    
    .syllabus-content {
        max-height: 60vh;
        overflow-y: auto;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .attached-syllabus {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #16a085;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .attached-syllabus h4 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 18px;
    }
    
    .syllabus-actions {
        display: flex;
        gap: 10px;
        margin: 15px 0;
    }
    
    .syllabus-info {
        color: #666;
        font-size: 14px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    </style>
</body>
</html>
