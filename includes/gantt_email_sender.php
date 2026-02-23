<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';

function sendGanttNotificationEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        // Server settings from the original email_sender.php
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'renangalvan@gmail.com';
        $mail->Password = 'uhppqgizpkzcuwgs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender
        $mail->setFrom('renangalvan@gmail.com', 'Inbox - Unela Notificaciones');

        // Recipient
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de notificación Gantt: {$mail->ErrorInfo}");
        return false;
    }
}
