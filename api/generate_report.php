<?php
require_once '../config.php';
require_once '../vendor/autoload.php';
require_once '../vendor/tcpdf/tcpdf.php';

// Get student and assignment IDs
$studentId = $_GET['studentId'] ?? null;
$assignmentId = $_GET['assignmentId'] ?? null;

if (!$studentId || !$assignmentId) {
    http_response_code(400);
    die('Missing required parameters');
}

try {
    // Fetch student details
    $stmt = $pdo->prepare("SELECT s.*, c.class_name, a.assignment_name, sub.score, sub.feedback 
                          FROM students s 
                          JOIN submissions sub ON s.id = sub.student_id 
                          JOIN assignments a ON sub.assignment_id = a.id 
                          JOIN classes c ON a.class_id = c.id 
                          WHERE s.id = ? AND a.id = ?");
    $stmt->execute([$studentId, $assignmentId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        http_response_code(404);
        die('Student or assignment not found');
    }

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('GradeGenie');
    $pdf->SetAuthor('GradeGenie');
    $pdf->SetTitle('Student Assignment Report');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Add content
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, 'Assignment Report', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Student Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Name: ' . $data['name'], 0, 1);
    $pdf->Cell(0, 10, 'Class: ' . $data['class_name'], 0, 1);
    $pdf->Cell(0, 10, 'Assignment: ' . $data['assignment_name'], 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Grade Information', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Score: ' . $data['score'], 0, 1);
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Feedback', 0, 1);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->MultiCell(0, 10, $data['feedback'], 0, 'L');

    // Output PDF
    $pdf->Output('student_report.pdf', 'D');

} catch (Exception $e) {
    http_response_code(500);
    die('Error generating report: ' . $e->getMessage());
}
?>
