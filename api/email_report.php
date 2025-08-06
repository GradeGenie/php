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

$studentId = $_GET['studentId'] ?? null;
$assignmentId = $_GET['assignmentId'] ?? null;

if (!$studentId || !$assignmentId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Fetch student details
    $stmt = $pdo->prepare("SELECT s.*, c.class_name, a.assignment_name, sub.score, sub.feedback, s.email 
                          FROM students s 
                          JOIN submissions sub ON s.id = sub.student_id 
                          JOIN assignments a ON sub.assignment_id = a.id 
                          JOIN classes c ON a.class_id = c.id 
                          WHERE s.id = ? AND a.id = ?");
    $stmt->execute([$studentId, $assignmentId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Student or assignment not found']);
        exit;
    }

    // Generate PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('GradeGenie');
    $pdf->SetAuthor('GradeGenie');
    $pdf->SetTitle('Student Assignment Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Add content (same as generate_report.php)
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

    // Save PDF to temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'report');
    $pdf->Output($tempFile, 'F');

    // Send email
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;  // Add these constants to your config.php
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;

    // Recipients
    $mail->setFrom(SMTP_FROM_EMAIL, 'GradeGenie');
    $mail->addAddress($data['email'], $data['name']);

    // Attachments
    $mail->addAttachment($tempFile, 'assignment_report.pdf');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Assignment Report: ' . $data['assignment_name'];
    $mail->Body = "Dear {$data['name']},<br><br>
                   Please find attached your assignment report for {$data['assignment_name']}.<br><br>
                   Best regards,<br>
                   GradeGenie Team";

    $mail->send();
    unlink($tempFile);  // Delete temporary file

    echo json_encode(['success' => true, 'message' => 'Report sent successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error sending report: ' . $e->getMessage()]);
}
?>
