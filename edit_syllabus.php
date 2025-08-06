<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Syllabus - GradeGenie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
    <style>
        .editor-container {
            margin-bottom: 30px;
        }
        .note-editor {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php 
    include 'header.php';
    include 'api/c.php';
    
    // Check if syllabus ID is provided
    if (!isset($_GET['id'])) {
        header('Location: view_syllabi.php');
        exit;
    }
    
    $syllabus_id = $_GET['id'];
    
    // Get syllabus from database
    $stmt = $conn->prepare("SELECT * FROM syllabi WHERE id = ?");
    $stmt->bind_param("i", $syllabus_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo '<div class="container mt-4"><div class="alert alert-danger">Syllabus not found</div></div>';
        include 'footer.php';
        exit;
    }
    
    $syllabus = $result->fetch_assoc();
    
    // Check if user has permission to edit this syllabus
    session_start();
    $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $syllabus['user_id'];
    $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    
    if (!$is_owner && !$is_admin) {
        echo '<div class="container mt-4"><div class="alert alert-danger">You do not have permission to edit this syllabus</div></div>';
        include 'footer.php';
        exit;
    }
    ?>

    <div class="container mt-4 mb-5">
        <h1 class="mb-4">Edit Syllabus</h1>
        
        <div class="mb-4">
            <a href="view_syllabus.php?id=<?php echo $syllabus_id; ?>" class="btn btn-secondary">Back to Syllabus</a>
        </div>

        <form id="editSyllabusForm">
            <input type="hidden" id="syllabus_id" name="syllabus_id" value="<?php echo $syllabus_id; ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Syllabus Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($syllabus['title']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="course_name" class="form-label">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($syllabus['course_name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="academic_level" class="form-label">Academic Level</label>
                <select class="form-select" id="academic_level" name="academic_level" required>
                    <option value="High School" <?php echo ($syllabus['academic_level'] == 'High School') ? 'selected' : ''; ?>>High School</option>
                    <option value="Undergraduate" <?php echo ($syllabus['academic_level'] == 'Undergraduate') ? 'selected' : ''; ?>>Undergraduate</option>
                    <option value="Graduate" <?php echo ($syllabus['academic_level'] == 'Graduate') ? 'selected' : ''; ?>>Graduate</option>
                    <option value="Professional" <?php echo ($syllabus['academic_level'] == 'Professional') ? 'selected' : ''; ?>>Professional</option>
                </select>
            </div>
            
            <div class="editor-container">
                <label for="content" class="form-label">Syllabus Content</label>
                <textarea id="content" name="content"><?php echo $syllabus['content']; ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
        
        <div id="saveStatus" class="mt-3"></div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Summernote editor
            $('#content').summernote({
                height: 500,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
            
            // Handle form submission
            $('#editSyllabusForm').submit(function(e) {
                e.preventDefault();
                
                const syllabusId = $('#syllabus_id').val();
                const title = $('#title').val();
                const courseName = $('#course_name').val();
                const academicLevel = $('#academic_level').val();
                const content = $('#content').summernote('code');
                
                // Show saving indicator
                $('#saveStatus').html('<div class="alert alert-info">Saving changes...</div>');
                
                // Send update request
                $.ajax({
                    url: 'api/update_syllabus.php',
                    type: 'POST',
                    data: {
                        syllabus_id: syllabusId,
                        title: title,
                        course_name: courseName,
                        academic_level: academicLevel,
                        content: content
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#saveStatus').html('<div class="alert alert-success">Syllabus updated successfully!</div>');
                            
                            // Redirect after a short delay
                            setTimeout(function() {
                                window.location.href = 'view_syllabus.php?id=' + syllabusId;
                            }, 1500);
                        } else {
                            $('#saveStatus').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#saveStatus').html('<div class="alert alert-danger">Error: ' + error + '</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>
