<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Include database configuration and TCPDF
require_once 'c.php';
require_once 'tcpdf/tcpdf_static.php';
require_once 'tcpdf/tcpdf_fonts.php';
require_once 'tcpdf/tcpdf_images.php';
require_once 'tcpdf.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    die('User not authenticated');
}

// Get assignment IDs from POST data
$assignmentIds = isset($_POST['assignments']) ? explode(',', $_POST['assignments']) : [];
if (empty($assignmentIds)) {
    die('No assignments selected');
}

try {
    // Create PDO connection from the mysqli connection in c.php
    if (!isset($conn)) {
        throw new Exception('Database connection not established in c.php');
    }
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create temporary directory for PDF files
    $tempDir = sys_get_temp_dir() . '/reports_' . uniqid();
    if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true)) {
        throw new Exception('Failed to create temporary directory');
    }

    // Create ZIP archive
    $zip = new ZipArchive();
    $zipFileName = $tempDir . '/reports.zip';
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Failed to create ZIP archive');
    }

    foreach ($assignmentIds as $assignmentId) {
        if (!is_numeric($assignmentId)) {
            continue;
        }

        $stmt = $pdo->prepare("SELECT s.studentName as name, c.name as class_name, a.name as assignment_name, 
                                     s.score, s.comments as feedback
                              FROM submissions s 
                              JOIN assignments a ON s.aid = a.aid 
                              JOIN classes c ON a.class = c.cid 
                              WHERE s.aid = ? AND s.status = 1");
        $stmt->execute([$assignmentId]);
        
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Create new PDF for each student
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('GradeGenie');
            $pdf->SetAuthor('GradeGenie');
            $pdf->SetTitle('Student Report - ' . $data['name']);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(20, 20, 20);
            $pdf->SetAutoPageBreak(true, 20);

            // Add a page
            $pdf->AddPage();

            // Set font
            $pdf->SetFont('helvetica', 'B', 20);

            // Add logo or header image if needed
            // $pdf->Image('path/to/logo.png', 10, 10, 30);

            // Title
            $pdf->Cell(0, 15, 'Student Report', 0, 1, 'C');
            $pdf->Ln(10);

            // Student Information
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Student Information', 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Name: ' . $data['name'], 0, 1);
            $pdf->Cell(0, 10, 'Class: ' . $data['class_name'], 0, 1);
            $pdf->Cell(0, 10, 'Assignment: ' . $data['assignment_name'], 0, 1);
            $pdf->Ln(5);

            // Grade Information
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Grade Information', 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Score: ' . $data['score'], 0, 1);
            $pdf->Ln(5);

            // Feedback
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Feedback', 0, 1);
            $pdf->SetFont('helvetica', '', 12);
            $pdf->MultiCell(0, 10, $data['feedback'], 0, 'L');

            // Add footer with date
            $pdf->SetY(-30);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'Generated on: ' . date('F j, Y'), 0, 1, 'R');

            // Save PDF to temp directory
            $fileName = preg_replace('/[^a-zA-Z0-9]/', '_', $data['name']) . '_' . 
                       preg_replace('/[^a-zA-Z0-9]/', '_', $data['assignment_name']) . '_report.pdf';
            $pdfPath = $tempDir . '/' . $fileName;
            
            $pdf->Output($pdfPath, 'F');
            $zip->addFile($pdfPath, $fileName);
        }
    }

    $zip->close();

    // Send ZIP file to browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="student_reports.zip"');
    header('Content-Length: ' . filesize($zipFileName));
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');

    readfile($zipFileName);

    // Clean up
    array_map('unlink', glob("$tempDir/*.*"));
    rmdir($tempDir);

} catch (Exception $e) {
    error_log("Error in generate_reports.php: " . $e->getMessage());
    
    // Clean up if needed
    if (isset($tempDir) && is_dir($tempDir)) {
        array_map('unlink', glob("$tempDir/*.*"));
        rmdir($tempDir);
    }
    
    die("Error generating reports: " . $e->getMessage());
}
?>
