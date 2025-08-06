<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Syllabi - GradeGenie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .syllabus-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .syllabus-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .syllabus-actions {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <?php 
    include 'header.php';
    include 'api/c.php';
    
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if not logged in
        header('Location: login.php?redirect=view_syllabi.php');
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Get user's syllabi from database
    $stmt = $conn->prepare("SELECT * FROM syllabi WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <div class="container mt-4 mb-5">
        <h1 class="mb-4">My Syllabi</h1>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <p class="lead">Manage your saved course syllabi</p>
            <a href="syllabus_generator.php" class="btn btn-primary">Create New Syllabus</a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card syllabus-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($row['title']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Course:</strong> <?php echo htmlspecialchars($row['course_name']); ?></p>
                                <p><strong>Level:</strong> <?php echo htmlspecialchars($row['academic_level']); ?></p>
                                <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($row['created_at'])); ?></p>
                                
                                <div class="syllabus-actions">
                                    <a href="view_syllabus.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <a href="edit_syllabus.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <p>You haven't created any syllabi yet. <a href="syllabus_generator.php">Create your first syllabus</a>.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this syllabus? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete functionality
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            let syllabusToDelete = null;
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    syllabusToDelete = this.dataset.id;
                    deleteModal.show();
                });
            });
            
            confirmDeleteBtn.addEventListener('click', function() {
                if (syllabusToDelete) {
                    // Send delete request
                    fetch('api/delete_syllabus.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `syllabus_id=${syllabusToDelete}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove the card from the page
                            const card = document.querySelector(`.delete-btn[data-id="${syllabusToDelete}"]`).closest('.col-md-6');
                            card.remove();
                            
                            // If no more syllabi, show the empty message
                            if (document.querySelectorAll('.syllabus-card').length === 0) {
                                const container = document.querySelector('.container');
                                const rowDiv = document.querySelector('.row');
                                rowDiv.remove();
                                
                                const emptyMessage = document.createElement('div');
                                emptyMessage.className = 'alert alert-info';
                                emptyMessage.innerHTML = '<p>You haven\'t created any syllabi yet. <a href="syllabus_generator.php">Create your first syllabus</a>.</p>';
                                container.appendChild(emptyMessage);
                            }
                        } else {
                            alert(`Error: ${data.message}`);
                        }
                        deleteModal.hide();
                    })
                    .catch(error => {
                        alert(`Error: ${error.message}`);
                        deleteModal.hide();
                    });
                }
            });
        });
    </script>
</body>
</html>
