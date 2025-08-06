<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
    <title>Free Rubric Generator | GradeGenie</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
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
            <h1>Create Your Rubric</h1>
            <p>Creating Rubrics has never been easier, just tell us what you need it for.</p>
            <form id="rubricForm">
                <!-- Subject Information Section -->
                <div class="section">
                    <h2>Subject Information</h2>
                    <div class="form-group">
                        <label for="subject">Subject Area *</label>
                        <select name="subject" id="subject" required>
                            <option value="">Select...</option>
                            <?php
                            $subjects = [
                                'Math', 'Science', 'Literature', 'History', 'Geography', 'Art', 
                                'Music', 'Physical Education', 'Biology', 'Chemistry', 'Physics',
                                'Economics', 'Political Science', 'Sociology', 'Psychology', 'Philosophy',
                                'Computer Science', 'Engineering', 'Environmental Science', 'Health',
                                'Custom'
                            ];
                            foreach ($subjects as $subj) {
                                echo "<option value=\"$subj\">$subj</option>";
                            }
                            ?>
                        </select>
                        <input type="text" name="custom_subject" id="custom_subject" placeholder="Enter custom subject" class="hidden">
                        <span class="error" id="subjectError"></span>
                    </div>
                    <div class="form-group">
                        <label for="level">Grade Level *</label>
                        <select name="level" id="level" required>
                            <option value="">Select...</option>
                            <?php
                            $levels = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6', 'Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12', 'College', 'Graduate', 'Custom'];
                            foreach ($levels as $lvl) {
                                echo "<option value=\"$lvl\">$lvl</option>";
                            }
                            ?>
                        </select>
                        <input type="text" name="custom_level" id="custom_level" placeholder="Enter custom grade level" class="hidden">
                        <span class="error" id="levelError"></span>
                    </div>
                </div>

                <!-- Rubric Details Section -->
                <div class="section">
                    <h2>Rubric Details</h2>
                    <div class="form-group">
                        <label for="assignment-title">Title *</label>
                        <input type="text" id="assignment-title" name="assignment-title" placeholder="Enter assignment title">
                    </div>
                    <div class="form-group">
                        <label for="description">Description, Context and Goals *</label>
                        <textarea id="description" name="description" placeholder="Enter description, context, and goals for the rubric. E.g., Assessing knowledge of biological concepts, ability to apply scientific methods, and quality of lab reports." required></textarea>
                        <span class="error" id="descriptionError"></span>
                    </div>
                    <div class="form-group">
                        <label for="style">Choose a Style *</label>
                        <select name="style" id="style" required>
                            <option value="Detailed">Detailed</option>
                            <option value="Simple">Simple</option>
                            <option value="Custom">Custom</option>
                        </select>
                        <input type="text" name="custom_style" id="custom_style" placeholder="Enter desired style" class="hidden">
                        <span class="error" id="styleError"></span>
                    </div>
                </div>

                <button id="createRubricButton" class="createRubricButton" type="button">Create Rubric</button>
            </form>
        </div>
        <div id="rubric-result" class="container" style="display:none;">
            <h2>Generated Rubric</h2>
            <div id="rubric-table">Loading...</div>
            <div id="message">
                Click 'Save & Continue' to store this rubric for future use. You can utilize it to grade assignments later or make edits within <a href="view_rubrics.php">My Rubrics</a>.
                <a onclick="copyRubric('rubric-table');" class="copyBtn">Copy Rubric</a>
            </div>
            <button class="button" onclick="saveRubric()" id="saveContinue">Save & Continue</button>
            <a onclick="startOver()" href="#" id="startOverBtn">Or, Create Another</a>
        </div>
    </div>

    <script>
function saveRubric() {
    var rubricTable = document.getElementById('rubric-table');
    if (!rubricTable) {
        console.error('Rubric table not found');
        return;
    }

    var form = document.getElementById('rubricForm');
    var formData = new FormData(form);

    // Append the generated rubric content to the formData
    formData.append('content', rubricTable.innerHTML);

    fetch('api/save_rubric.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Save response:', data);
        if (data.status === 'success') {
            alert('Rubric saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('An error occurred while saving the rubric. Please try again.');
    });
}

function copyRubric(elementId) {
    var element = document.getElementById(elementId);
    if (!element) {
        console.error('Element not found:', elementId);
        return;
    }

    var rows = element.querySelectorAll('tr');
    var tsvContent = '';

    rows.forEach(function(row) {
        var cols = row.querySelectorAll('td, th');
        var rowData = [];

        cols.forEach(function(col) {
            rowData.push(col.innerText);
        });

        tsvContent += rowData.join('\t') + '\n';
    });

    // Create a temporary textarea to hold the TSV content
    var textarea = document.createElement('textarea');
    textarea.value = tsvContent;
    document.body.appendChild(textarea);

    // Select the content of the textarea
    textarea.select();
    textarea.setSelectionRange(0, 99999); // For mobile devices

    try {
        // Copy the content to the clipboard
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
    } catch (err) {
        console.error('Oops, unable to copy', err);
    }

    // Remove the temporary textarea
    document.body.removeChild(textarea);
}

document.addEventListener('DOMContentLoaded', function() {
    function toggleCustomField(selectElement, customFieldId) {
        var selectedValue = selectElement.value;
        var customField = document.getElementById(customFieldId);
        if (selectedValue === 'Custom') {
            customField.classList.remove('hidden');
        } else {
            customField.classList.add('hidden');
            customField.value = '';
        }
    }

    document.getElementById('subject').addEventListener('change', function() {
        toggleCustomField(this, 'custom_subject');
    });

    document.getElementById('level').addEventListener('change', function() {
        toggleCustomField(this, 'custom_level');
    });

    document.getElementById('style').addEventListener('change', function() {
        toggleCustomField(this, 'custom_style');
    });

    document.getElementById('createRubricButton').addEventListener('click', function() {
        if (!validateForm()) {
            return;
        }

        var form = document.getElementById('rubricForm');
        var formData = new FormData(form);

        document.getElementById('createRubricButton').disabled = true;
        document.getElementById('rubric-result').style.display = 'block';
        document.getElementById('rubric-table').innerHTML = 'Loading...';

        fetch('api/create_rubric_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response:', data); // Log the response
            document.getElementById('createRubricButton').disabled = false;
            if (data.status === 'success') {
                document.getElementById('rubric-table').innerHTML = data.rubric;
            } else {
                document.getElementById('rubric-table').innerHTML = 'An error occurred. Please try again.';
                alert('Error: ' + data.message + '\nCheck console for more details.');
                console.error('Error details:', data);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('createRubricButton').disabled = false;
            document.getElementById('rubric-table').innerHTML = 'An error occurred. Please try again.';
        });
    });

    function validateForm() {
    var isValid = true;
    var requiredFields = ['subject', 'level', 'assignment-title', 'description', 'style'];

    requiredFields.forEach(function(field) {
        var element = document.getElementById(field);
        var errorElement = document.getElementById(field + 'Error');
        
        if (element && element.value.trim() === '') {
            isValid = false;
            if (errorElement) {
                errorElement.textContent = 'This field is required.';
            }
        } else {
            if (errorElement) {
                errorElement.textContent = '';
            }
        }
    });
    
    return isValid;
}

});
</script>

</body>
</html>
