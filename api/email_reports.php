<?php
require_once '../config.php';
require_once '../vendor/autoload.php';
require_once '../vendor/tcpdf/tcpdf.php';
require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once '../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$studentIds = $data['studentIds'] ?? [];
$assignmentIds = $data['assignmentIds'] ?? [];
$recipientEmail = $data['recipientEmail'] ?? null;

if (empty($studentIds) || empty($assignmentIds) || !$recipientEmail) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Create temporary directory for PDF files
    $tempDir = sys_get_temp_dir() . '/reports_' . uniqid();
    mkdir($tempDir);
    $pdfFiles = [];

    foreach ($studentIds as $studentId) {
        foreach ($assignmentIds as $assignmentId) {
            // Fetch student and assignment details
            $stmt = $pdo->prepare("SELECT s.*, c.class_name, a.assignment_name, sub.score, sub.feedback 
                                  FROM students s 
                                  JOIN submissions sub ON s.id = sub.student_id 
                                  JOIN assignments a ON sub.assignment_id = a.id 
                                  JOIN classes c ON a.class_id = c.id 
                                  WHERE s.id = ? AND a.id = ?");
            $stmt->execute([$studentId, $assignmentId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                continue; // Skip if no data found
            }

            // Generate PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('GradeGenie');
            $pdf->SetAuthor('GradeGenie');
            $pdf->SetTitle('Student Assignment Report');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AddPage();

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

            // Save PDF
            $pdfFileName = $tempDir . '/' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['name']) . '_' . 
                          preg_replace('/[^a-zA-Z0-9]/', '_', $data['assignment_name']) . '_report.pdf';
            $pdf->Output($pdfFileName, 'F');
            $pdfFiles[] = [
                'path' => $pdfFileName,
                'name' => basename($pdfFileName)
            ];
        }
    }

    if (empty($pdfFiles)) {
        echo json_encode(['success' => false, 'message' => 'No reports generated']);
        exit;
    }

    // Send email
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;

    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, 'GradeGenie');
    $mail->addAddress($recipientEmail);

    // Attachments
    foreach ($pdfFiles as $file) {
        $mail->addAttachment($file['path'], $file['name']);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Student Assignment Reports';
    $mail->Body = "Dear Recipient,<br><br>
                   Please find attached the requested student assignment reports.<br><br>
                   Best regards,<br>
                   GradeGenie Team";

    $mail->send();

    // Clean up
    foreach ($pdfFiles as $file) {
        unlink($file['path']);
    }
    rmdir($tempDir);

    echo json_encode(['success' => true, 'message' => 'Reports sent successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error sending reports: ' . $e->getMessage()]);
}
?>
