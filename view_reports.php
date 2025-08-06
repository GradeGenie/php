<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'api/c.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get all classes for this user
    $query = "SELECT cid, name FROM classes WHERE owner = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Get number of classes
        $numClasses = $result->num_rows;
    } else {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - GradeGenie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-selection {
            max-height: 400px;
            overflow-y: auto;
        }
        .btn-select-all {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
        }
        .btn-deselect-all {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 5px 15px;
            border-radius: 4px;
        }
        .generate-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            width: 100%;
            margin-top: 20px;
        }
        .generate-btn:disabled {
            background-color: #ccc;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4">Generate Reports</h2>
        
        <!-- Debug info -->
        <?php if ($numClasses === 0): ?>
        <div class="alert alert-info">
            No classes found for user ID: <?php echo htmlspecialchars($userId); ?>
            <br>
            <small>If you believe this is an error, please make sure you have created some classes.</small>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Select Class</h5>
                    </div>
                    <div class="card-body">
                        <select id="classSelect" class="form-select">
                            <option value="">Choose a class...</option>
                            <?php 
                            if ($result && $result->num_rows > 0):
                                while ($class = $result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo htmlspecialchars($class['cid']); ?>">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php 
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Select Reports to Generate</h5>
                        <div>
                            <button class="btn-select-all" id="selectAll">SELECT ALL</button>
                            <button class="btn-deselect-all" id="deselectAll">DESELECT ALL</button>
                        </div>
                    </div>
                    <div class="card-body report-selection">
                        <div id="assignmentList" class="list-group">
                            <div class="text-center text-muted">
                                Please select a class to view assignments
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button id="generateReports" class="generate-btn" disabled>
            GENERATE SELECTED REPORTS
        </button>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            function updateGenerateButton() {
                const checkedBoxes = $('input[name="assignments[]"]:checked').length;
                $('#generateReports').prop('disabled', checkedBoxes === 0);
            }

            $('#classSelect').change(function() {
                const classId = $(this).val();
                if (!classId) {
                    $('#assignmentList').html('<div class="text-center text-muted">Please select a class to view assignments</div>');
                    return;
                }

                // Show loading message
                $('#assignmentList').html('<div class="text-center">Loading assignments...</div>');

                $.ajax({
                    url: 'api/get_class_assignments.php',
                    data: { class_id: classId },
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            $('#assignmentList').html('<div class="text-center text-danger">' + response.error + '</div>');
                            return;
                        }
                        
                        if (!Array.isArray(response) || response.length === 0) {
                            $('#assignmentList').html('<div class="text-center text-muted">No assignments found for this class</div>');
                            return;
                        }

                        let html = '';
                        response.forEach(function(assignment) {
                            html += `
                                <div class="list-group-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="assignments[]" 
                                               value="${assignment.id}" id="assignment${assignment.id}">
                                        <label class="form-check-label" for="assignment${assignment.id}">
                                            ${assignment.name}
                                        </label>
                                    </div>
                                </div>
                            `;
                        });
                        $('#assignmentList').html(html);
                        updateGenerateButton();
                    },
                    error: function(xhr, status, error) {
                        $('#assignmentList').html('<div class="text-center text-danger">Error loading assignments. Please try again.</div>');
                        console.error('AJAX Error:', status, error);
                    }
                });
            });

            $('#selectAll').click(function() {
                $('input[name="assignments[]"]').prop('checked', true);
                updateGenerateButton();
            });

            $('#deselectAll').click(function() {
                $('input[name="assignments[]"]').prop('checked', false);
                updateGenerateButton();
            });

            $(document).on('change', 'input[name="assignments[]"]', updateGenerateButton);

            $('#generateReports').click(function() {
                const selectedAssignments = $('input[name="assignments[]"]:checked')
                    .map(function() { return this.value; })
                    .get();

                if (selectedAssignments.length === 0) {
                    alert('Please select at least one assignment');
                    return;
                }

                // Create a form and submit it to trigger the download
                const form = $('<form>')
                    .attr('method', 'post')
                    .attr('action', 'api/generate_reports.php');

                $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'assignments')
                    .attr('value', selectedAssignments.join(','))
                    .appendTo(form);

                form.appendTo('body').submit().remove();
            });
        });
    </script>
</body>
</html>
