<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Syllabus - GradeGenie</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .syllabus-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            font-family: 'Arial', sans-serif;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        /* GradeGenie branding */
        .btn-primary {
            background-color: #4a6fdc;
            border-color: #4a6fdc;
        }
        .btn-primary:hover {
            background-color: #3a5fc9;
            border-color: #3a5fc9;
        }
        @media print {
            .no-print {
                display: none;
            }
            .syllabus-container {
                box-shadow: none;
                padding: 0;
            }
            body {
                padding: 0;
                margin: 0;
            }
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
    
    // Check if user has permission to view this syllabus
    session_start();
    $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $syllabus['user_id'];
    $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    
    if (!$is_owner && !$is_admin) {
        echo '<div class="container mt-4"><div class="alert alert-danger">You do not have permission to view this syllabus</div></div>';
        include 'footer.php';
        exit;
    }
    ?>

    <div class="container mt-4 mb-5">
        <div class="action-buttons no-print">
            <a href="view_syllabi.php" class="btn btn-secondary">Back to My Syllabi</a>
            <button onclick="window.print()" class="btn btn-primary">Print Syllabus</button>
            <button id="downloadPdfBtn" class="btn btn-success">Download PDF</button>
            <button id="downloadWordBtn" class="btn btn-info">Download Word</button>
            <?php if ($is_owner || $is_admin): ?>
                <a href="edit_syllabus.php?id=<?php echo $syllabus_id; ?>" class="btn btn-warning">Edit</a>
                <button id="deleteBtn" class="btn btn-danger">Delete</button>
            <?php endif; ?>
        </div>

        <div class="syllabus-container">
            <?php echo $syllabus['content']; ?>
        </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syllabusContainer = document.querySelector('.syllabus-container');
            const syllabusId = <?php echo $syllabus_id; ?>;
            const syllabusTitle = "<?php echo addslashes($syllabus['title']); ?>";
            const deleteBtn = document.getElementById('deleteBtn');
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            
            // Download as PDF
            document.getElementById('downloadPdfBtn').addEventListener('click', function() {
                const filename = syllabusTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.pdf';
                
                // Create a clone of the element to ensure proper styling
                const clonedElement = syllabusContainer.cloneNode(true);
                // Set explicit styling for PDF generation
                clonedElement.style.width = '100%';
                clonedElement.style.padding = '20px';
                clonedElement.style.backgroundColor = '#ffffff';
                clonedElement.style.color = '#000000';
                clonedElement.style.fontFamily = 'Arial, sans-serif';
                
                // Temporarily append to body but hide it
                clonedElement.style.position = 'absolute';
                clonedElement.style.left = '-9999px';
                document.body.appendChild(clonedElement);
                
                const options = {
                    margin: 10,
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                // Generate PDF from the cloned element
                html2pdf().set(options).from(clonedElement).save().then(() => {
                    // Remove the cloned element after PDF is generated
                    document.body.removeChild(clonedElement);
                });
            });
            
            // Download as Word (HTML)
            document.getElementById('downloadWordBtn').addEventListener('click', function() {
                const filename = syllabusTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.doc';
                const htmlContent = syllabusContainer.innerHTML;
                
                const blob = new Blob([`
                    <html>
                        <head>
                            <meta charset="utf-8">
                            <title>${syllabusTitle}</title>
                            <style>
                                body { font-family: Arial, sans-serif; }
                                table { border-collapse: collapse; width: 100%; }
                                th, td { border: 1px solid #ddd; padding: 8px; }
                                th { background-color: #f2f2f2; }
                                /* GradeGenie branding */
                                h1, h2, h3 { color: #4a6fdc; }
                                a { color: #4a6fdc; }
                            </style>
                        </head>
                        <body>
                            ${htmlContent}
                        </body>
                    </html>
                `], { type: 'application/msword' });
                
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.click();
                URL.revokeObjectURL(link.href);
            });
            
            // Delete functionality
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    deleteModal.show();
                });
                
                confirmDeleteBtn.addEventListener('click', function() {
                    // Send delete request
                    fetch('api/delete_syllabus.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `syllabus_id=${syllabusId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            window.location.href = 'view_syllabi.php';
                        } else {
                            alert(`Error: ${data.message}`);
                            deleteModal.hide();
                        }
                    })
                    .catch(error => {
                        alert(`Error: ${error.message}`);
                        deleteModal.hide();
                    });
                });
            }
        });
    </script>
</body>
</html>
