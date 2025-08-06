<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genie | Home</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            margin: 0;
            font-family: 'Albert Sans', sans-serif;
            background-color: #f0f0f0;
            color: #333;
        }

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        #logo {
            font-size: 24px;
            font-weight: bold;
        }

        #right {
            font-size: 16px;
        }

        /* Nav Styles */
        nav {
            width: 200px;
            background-color: #343a40;
            height: 100vh;
            position: fixed;
            top: 48px;
            left: 0;
            display: flex;
            flex-direction: column;
            padding-top: 20px;
        }

        #navItems {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .navItem {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            width: 100%;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .navItem:hover {
            background-color: #495057;
        }

        .navIcon {
            width: 20px;
            height: 20px;
            background-color: #adb5bd;
            margin-right: 10px;
        }

        .navLabel {
            font-size: 16px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .button:hover {
            background-color: #0056b3;
        }

        .button:active {
            background-color: #003d80;
        }

        #mainContent {
            position: absolute;
            left: 230px;
            top: 70px;
            right: 30px;
            padding-bottom: 20px;
        }

        .createRubricButton {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            border: none;
        }

        .createRubricButton:hover {
            background-color: #218838;
        }

        .createRubricButton:active {
            background-color: #1e7e34;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        label {
            flex: 1 0 100%;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 1.1em;
        }

        input, textarea, select {
            flex: 1 0 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Albert Sans', sans-serif;
            margin-bottom: 10px;
            font-size: 1em;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        .icon-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            margin-left: 10px;
        }

        .icon-button:hover {
            color: #007bff;
        }

        .icon-button.remove {
            color: red;
            font-size: 1.5em;
        }

        #rubric-table {
            margin-top: 20px;
        }

        th {
            background: #e9e9e9;
            font-weight: bold;
        }

        td:nth-child(1) {
            background: #e9e9e9;
            font-weight: bold;
        }

        th, td {
            border: 1px solid;
            padding: 6px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.8em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 220px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 125%; /* Position the tooltip */
            left: 50%;
            margin-left: -110px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        .hidden {
            display: none;
        }

        .error {
            color: red;
            font-size: 1em;
        }

        .add-remove-btn {
            text-align: right;
            margin-top: 10px;
        }

        .form-group.inline {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-group.inline input {
            flex: 1;
            margin-right: 10px;
            font-size: 1em;
        }

        .form-group.inline .icon-button {
            margin-left: 10px;
        }

        .helper-text {
            font-size: 1em;
            color: #666;
        }

        @media (max-width: 768px) {
            nav {
                height: auto;
                width: 100%;
                position: relative;
            }

            #mainContent {
                left: 0;
                top: 100px;
                padding: 20px;
            }

            .form-group.inline {
                flex-direction: column;
                align-items: flex-start;
            }

            .form-group.inline input {
                margin-right: 0;
                margin-bottom: 10px;
                width: 100%;
            }

            .form-group.inline .icon-button {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
<header>
    <div id="logo">Grady</div>
    <div id="right">Serene</div>
</header>
<nav>
    <div id="navItems">
        <a href="index.php" class="navItem">
            <div class="navIcon"></div>
            <div class="navLabel">Dashboard</div>
        </a>
        <a href="rubric.create.php" class="navItem">
            <div class="navIcon"></div>
            <div class="navLabel">Create Rubric</div>
        </a>
        <a href="bulk-grader.php" class="navItem">
            <div class="navIcon"></div>
            <div class="navLabel">Bulk Grading</div>
        </a>
        <a href="help.guides.php" class="navItem">
            <div class="navIcon"></div>
            <div class="navLabel">Help Guides</div>
        </a>
    </div>
</nav>


<div id="mainContent">
    <div class="container">
        <h1>Create Your Rubric</h1>
        <p>Creating Rubrics has never been easier, just tell Grady what you need it for.</p>
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
                    <label for="assignment-title">Assignment Title (optional)</label>
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
                    <input type="text" name="custom_style" id="custom_style" placeholder="Enter custom assignment type" class="hidden">
                    <span class="error" id="styleError"></span>
                </div>
            </div>

            <!-- Further Customization Section -->
            <div class="section">
                <h2>Further Customization</h2>

                <!-- Criteria Section -->
                <div class="subsection">
                    <h3>Criteria <span class="tooltip">ℹ️<span class="tooltiptext">Provide criteria for the rubric. E.g., Knowledge, Application, Analysis</span></span></h3>
                    <p class="helper-text">Optional. Leave blank for AI-generated criteria.</p>
                    <div id="criteriaList" class="collapse">
                        <div class="form-group inline">
                            <input type="text" name="criteria[]" placeholder="Enter criteria">
                            <input type="number" name="weighting[]" placeholder="Enter weighting (%)" min="0" max="100">
                            <button type="button" class="icon-button remove" onclick="removeCriteria(this)">❌</button>
                        </div>
                    </div>
                    <div class="add-remove-btn">
                        <button type="button" class="icon-button" onclick="addCriteria()">➕ Add Criteria</button>
                    </div>
                </div>

                <!-- Evaluation Scale Section -->
                <div class="subsection">
                    <h3>Evaluation Scale <span class="tooltip">ℹ️<span class="tooltiptext">Define performance levels and corresponding rating scales. E.g., Excellent, Good, Satisfactory, Needs Improvement and ★★★★★, A-E, 5-1</span></span></h3>
                    <p class="helper-text">Optional. Leave blank for AI-generated evaluation scales.</p>
                    <div id="evaluationList" class="collapse">
                        <div class="form-group inline">
                            <input type="text" name="performance_levels[]" placeholder="Enter performance level">
                            <input type="text" name="rating_scale[]" placeholder="Enter rating scale">
                            <button type="button" class="icon-button remove" onclick="removeEvaluation(this)">❌</button>
                        </div>
                    </div>
                    <div class="add-remove-btn">
                        <button type="button" class="icon-button" onclick="addEvaluation()">➕ Add Evaluation</button>
                    </div>
                </div>
            </div>

            <button id="createRubricButton" class="createRubricButton" type="button" onclick="createRubric()">Create Rubric</button>
        </form>
    </div>
    <div id="rubric-result" class="container" style="display:none;">
        <h2>Generated Rubric</h2>
        <div id="rubric-table">Loading...</div>
        <button onclick="saveRubric()">Save & Continue</button>
        <a onclick="startOver()" href="#" id="startOverBtn">Or, Create Another</a>
    </div>
</div>

<script>
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

function validateForm() {
    var isValid = true;
    var requiredFields = ['subject', 'level', 'description', 'style'];
    requiredFields.forEach(function(field) {
        var element = document.getElementById(field);
        var errorElement = document.getElementById(field + 'Error');
        if (element.value.trim() === '') {
            isValid = false;
            errorElement.textContent = 'This field is required.';
        } else {
            errorElement.textContent = '';
        }
    });
    return isValid;
}

function createRubric() {
    if (!validateForm()) {
        return;
    }

    // Get form values
    var form = document.getElementById('rubricForm');
    var formData = new FormData(form);

    // Hide the create button and show loading message
    document.getElementById('createRubricButton').disabled = true;
    document.getElementById('rubric-result').style.display = 'block';
    document.getElementById('rubric-table').innerHTML = 'Loading...';

    // Send data to create_rubric_api.php using fetch API
    fetch('api/create_rubric_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text()) // Expecting HTML response
    .then(data => {
        console.log(data);
        // Display the rubric result
        document.getElementById('rubric-table').innerHTML = data;
        // Store the rubric content in a hidden input field for later use
        document.getElementById('generatedRubric').value = data;
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('rubric-table').innerHTML = 'An error occurred. Please try again.';
    });
}

function startOver() {
    // Reset the form and interface
    document.getElementById('rubricForm').reset();
    document.getElementById('rubric-result').style.display = 'none';
    document.getElementById('createRubricButton').disabled = false;
}

function saveRubric() {
    // Get form values
    var subject = document.getElementById('subject').value;
    var level = document.getElementById('level').value;
    var description = document.getElementById('description').value;
    var style = document.getElementById('style').value;
    var content = document.getElementById('generatedRubric').value;

    // Create a FormData object
    var formData = new FormData();
    formData.append('subject', subject);
    formData.append('level', level);
    formData.append('description', description);
    formData.append('style', style);
    formData.append('content', content);

    // Send data to save_rubric.php using fetch API
    fetch('api/save_rubric.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        // Display success message or handle response
        alert('Rubric saved successfully!');
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function addCriteria() {
    var criteriaList = document.getElementById('criteriaList');
    var criteriaDiv = document.createElement('div');
    criteriaDiv.className = 'form-group inline';
    criteriaDiv.innerHTML = `
        <input type="text" name="criteria[]" placeholder="Enter criteria">
        <input type="number" name="weighting[]" placeholder="Enter weighting (%)" min="0" max="100">
        <button type="button" class="icon-button remove" onclick="removeCriteria(this)">❌</button>
    `;
    criteriaList.appendChild(criteriaDiv);
}

function removeCriteria(button) {
    var criteriaList = document.getElementById('criteriaList');
    criteriaList.removeChild(button.parentElement);
}

function addEvaluation() {
    var evaluationList = document.getElementById('evaluationList');
    var evaluationDiv = document.createElement('div');
    evaluationDiv.className = 'form-group inline';
    evaluationDiv.innerHTML = `
        <input type="text" name="performance_levels[]" placeholder="Enter performance level">
        <input type="text" name="rating_scale[]" placeholder="Enter rating scale">
        <button type="button" class="icon-button remove" onclick="removeEvaluation(this)">❌</button>
    `;
    evaluationList.appendChild(evaluationDiv);
}

function removeEvaluation(button) {
    var evaluationList = document.getElementById('evaluationList');
    evaluationList.removeChild(button.parentElement);
}

$(document).ready(function() {
    $('.section h2').click(function() {
        $(this).next().toggleClass('collapse');
    });
});
</script>
<input type="hidden" id="generatedRubric" value="">
</body>
</html>
