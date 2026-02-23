<?php
// Asegúrate de que las clases de PHPMailer estén disponibles
// Asumo que PHPMailer está en includes/PHPMailer/src/PHPMailer.php, etc.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ajusta la ruta según donde hayas colocado PHPMailer
require __DIR__ . '/Exception.php';
require __DIR__ . '/PHPMailer.php';
require __DIR__ . '/SMTP.php';

function sendTaskAssignmentEmail($toEmail, $toName, $taskTitle, $taskDescription, $taskId, $dueDate = null) {
    $mail = new PHPMailer(true); // Pasar `true` habilita excepciones
    $mail->CharSet = 'UTF-8'; // Asegurar la codificación correcta de caracteres para tildes y caracteres especiales

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'renangalvan@gmail.com'; // <<< REEMPLAZA CON TU CORREO GMAIL
        $mail->Password = 'uhppqgizpkzcuwgs'; // <<< REEMPLAZA CON TU CONTRASEÑA DE APLICACIÓN DE GMAIL
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SMTPS (SSL/TLS implícito)
        $mail->Port = 465; // Puerto para SMTPS

        // Remitente
        $mail->setFrom('renangalvan@gmail.com', 'Inbox - Colibrí Notificaciones'); // <<< REEMPLAZA CON TU CORREO GMAIL

        // Destinatario
        $mail->addAddress($toEmail, $toName);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Nueva Tarea Asignada desde Inbox: ' . $taskTitle;

        $dueDateMessage = '';
        if ($dueDate && $dueDate !== '0000-00-00') {
            try {
                $today = new DateTime();
                $dueDateTime = new DateTime($dueDate);
                $interval = $today->diff($dueDateTime);
                $days = $interval->days;

                if ($interval->invert) { // Fecha de vencimiento en el pasado
                    $dueDateMessage = '<p style="color: red;"><strong>¡La tarea ha vencido hace ' . $days . ' día(s)!</strong></p>';
                } else if ($days == 0) {
                    $dueDateMessage = '<p style="color: orange;"><strong>¡La tarea vence hoy!</strong></p>';
                } else {
                    $dueDateMessage = '<p><strong>Usted tiene ' . $days . ' día(s) para completar esta tarea.</strong></p>';
                }
            } catch (Exception $e) {
                error_log("Error al calcular la fecha de vencimiento: " . $e->getMessage());
                $dueDateMessage = ''; // En caso de error, no mostrar mensaje de fecha
            }
        }

        $mail->Body    = '
            <html>
            <head>
                <title>Nueva Tarea Asignada</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { width: 80%; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
                    h2 { color: #0056b3; }
                    p { margin-bottom: 10px; }
                    .task-details { background-color: #fff; padding: 15px; border-left: 5px solid #007bff; margin-top: 20px; }
                    .footer { margin-top: 20px; font-size: 0.8em; color: #777; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2>¡Hola, ' . htmlspecialchars($toName) . '!</h2>
                    <p>Se te ha asignado una nueva tarea en Inbox Colibrí:</p>
                    <div class="task-details">
                        <h3>' . htmlspecialchars($taskTitle) . '</h3>
                        <p><strong>Descripción:</strong> ' . $taskDescription . '</p>
                        ' . $dueDateMessage . '
                        <p>Puedes ver los detalles completos de la tarea en Inbox haciendo clic aquí: <a href="https://renangalvan.net/inbox_colibri/ver_tarea.php?id=' . $taskId . '">Ver Tarea</a></p>
                    </div>
                    <p class="footer">Este es un mensaje automático, por favor no respondas a este correo. Design an developed by renangalvan.net</p>
                </div>
            </body>
            </html>
        ';
        $mail->AltBody = 'Hola ' . $toName . ',\n\nSe te ha asignado una nueva tarea en Inbox - Colibrí: ' . $taskTitle . '\n\nDescripción: ' . $taskDescription . '\n\n' . strip_tags($dueDateMessage) . '\n\nPuedes ver los detalles completos de la tarea en: https://renangalvan.net/inbox_colibri/ver_tarea.php?id=' . $taskId . '\n\nEste es un mensaje automático, por favor no respondas a este correo.';

        $mail->send();
        // echo 'El mensaje ha sido enviado'; // Para depuración
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        // echo "El mensaje no pudo ser enviado. Mailer Error: {$mail->ErrorInfo}"; // Para depuración
        return false;
    }
}

// Nueva función genérica para envío de correos
function sendEmail($toEmail, $subject, $body) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        // Configuración del servidor SMTP (reutilizada del código existente)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'renangalvan@gmail.com'; // <<< REEMPLAZA CON TU CORREO GMAIL
        $mail->Password = 'uhppqgizpkzcuwgs'; // <<< REEMPLAZA CON TU CONTRASEÑA DE APLICACIÓN DE GMAIL
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Remitente (reutilizado del código existente)
        $mail->setFrom('renangalvan@gmail.com', 'Colibrí Notificaciones'); // Usar un nombre más genérico

        // Destinatario
        $mail->addAddress($toEmail); // No necesitamos el nombre del destinatario aquí, ya que el cuerpo del correo lo manejará

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Versión de texto plano del cuerpo HTML

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo a $toEmail: {$mail->ErrorInfo}");
        return false;
    }
}

function sendGradeEmail($toEmail, $toName, $courseName, $grade, $gradeDetails = []) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        // Configuración Servidor
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'renangalvan@gmail.com'; 
        $mail->Password = 'uhppqgizpkzcuwgs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('renangalvan@gmail.com', 'Registro - Colibrí');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Calificación Final: ' . $courseName;

        // Determinar estilo según nota
        $colorNota = ($grade >= 70) ? '#198754' : '#dc3545'; // Verde o Rojo
        $mensajeNota = ($grade >= 70) ? '¡Felicidades! Ha aprobado el curso.' : 'El curso no ha sido aprobado.';

        // Construir tabla de detalles si existen
        $detailsTable = '';
        if (!empty($gradeDetails)) {
            $detailsTable .= '<h4 style="margin-top: 30px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">Desglose de Calificación</h4>';
            $detailsTable .= '<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">';
            $detailsTable .= '<thead><tr style="background-color: #f2f2f2;">
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: left;">Rubro de Evaluación</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Porcentaje</th>
                                <th style="padding: 8px; border: 1px solid #ddd; text-align: center;">Calificación</th>
                              </tr></thead>';
            $detailsTable .= '<tbody>';
            foreach ($gradeDetails as $detail) {
                $detailsTable .= '<tr>
                                    <td style="padding: 8px; border: 1px solid #ddd;">' . htmlspecialchars($detail['rubro']) . '</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($detail['porcentaje']) . '%</td>
                                    <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">' . htmlspecialchars($detail['calificacion']) . '</td>
                                  </tr>';
            }
            $detailsTable .= '</tbody></table>';
        }

        $body = '
            <html>
            <head>
                <style>
                    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                    .header { background-color: #003366; color: #ffffff; padding: 20px; text-align: center; }
                    .content { padding: 30px; color: #333333; line-height: 1.6; }
                    .grade-box { text-align: center; margin: 25px 0; padding: 20px; background-color: #f8f9fa; border-radius: 8px; border-left: 5px solid ' . $colorNota . '; }
                    .grade-value { font-size: 48px; font-weight: bold; color: ' . $colorNota . '; margin: 0; }
                    .footer { background-color: #eeeeee; padding: 15px; text-align: center; font-size: 12px; color: #777777; }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="header">
                        <h1 style="margin:0;">Reporte de Calificaciones</h1>
                    </div>
                    <div class="content">
                        <p>Estimado(a) <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                        <p>Se ha registrado su calificación final para el curso: <strong>' . htmlspecialchars($courseName) . '</strong>.</p>
                        
                        <div class="grade-box">
                            <p style="margin:0; font-size:14px; text-transform:uppercase; color:#777;">Calificación Final</p>
                            <p class="grade-value">' . number_format($grade, 2) . '</p>
                            <p style="margin-top:10px; font-weight:bold;">' . $mensajeNota . '</p>
                        </div>

                        ' . $detailsTable . '

                        <p>Si tiene alguna duda, por favor comuníquese con su profesor o con el departamento de registro.</p>
                    </div>
                    <div class="footer">
                        <p>&copy; ' . date('Y') . ' Colibrí - Gestor de Proyectos. Todos los derechos reservados.</p>
                    </div>
                </div>
            </body>
            </html>
        ';

        $mail->Body = $body;
        $mail->AltBody = "Estimado(a) $toName,\n\nSu nota final en el curso $courseName es: " . number_format($grade, 2) . ".\n\n$mensajeNota\n\nSi tiene alguna duda, por favor comuníquese con su profesor o con el departamento de registro.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error envío nota: {$mail->ErrorInfo}");
        return false;
    }
}

function sendEmailWithAttachment($toEmail, $subject, $body, $attachmentPath) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    try {
        // Configuración del servidor SMTP (reutilizada)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'renangalvan@gmail.com';
        $mail->Password = 'uhppqgizpkzcuwgs';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Remitente
        $mail->setFrom('renangalvan@gmail.com', 'Colibrí BPM');

        // Destinatario
        $mail->addAddress($toEmail);

        // Adjunto
        if (!file_exists($attachmentPath)) {
            error_log("Error de adjunto: El archivo no existe en la ruta: $attachmentPath");
            return false;
        }
        $mail->addAttachment($attachmentPath);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo con adjunto a $toEmail: {$mail->ErrorInfo}");
        return false;
    }
}