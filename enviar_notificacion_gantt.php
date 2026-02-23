<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/gantt_email_sender.php";
require_once "includes/permissions.php";

header('Content-Type: application/json');

// Basic security checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !has_permission($mysqli, 'crear_tareas')) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $taskId = isset($_POST['taskId']) ? (int)$_POST['taskId'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $senderId = $_SESSION['id'];
    $senderName = $_SESSION['nombre'];

    if (empty($taskId) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para enviar la notificación.']);
        exit;
    }

    // Get task details and assigned user emails
    $sql = "SELECT t.titulo, u.email, u.nombre 
            FROM tarea_asignaciones ta
            JOIN usuarios u ON ta.id_usuario = u.id
            JOIN tareas t ON ta.id_tarea = t.id
            WHERE ta.id_tarea = ?";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $taskId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $taskTitle = '';
            $emailsSent = 0;
            
            while ($row = $result->fetch_assoc()) {
                $taskTitle = $row['titulo'];
                $recipientEmail = $row['email'];
                $recipientName = $row['nombre'];

                // Send email
                $subject = "Inbox - Notificación sobre la tarea: " . $taskTitle;
                $body = "Hola " . $recipientName . ",<br><br>";
                $body .= "Has recibido una notificación de <strong>" . htmlspecialchars($senderName) . "</strong> sobre la tarea <strong>'" . htmlspecialchars($taskTitle) . "'</strong>.<br><br>";
                $body .= "<strong>Mensaje:</strong><br>";
                $body .= nl2br(htmlspecialchars($message));
                $body .= "<br><br>Puedes ver la tarea haciendo clic en el siguiente enlace:<br>";
                $body .= "<a href='https://renangalvan.net/inbox_colibri/ver_tarea.php?id=" . $taskId . "'>Ver Tarea</a>"; // Assuming this is the domain
                
                $body .= "<br><br>Saludos,<br>El equipo de DTI Unela";

                if (sendGanttNotificationEmail($recipientEmail, $recipientName, $subject, $body)) {
                    $emailsSent++;
                }
            }

            if ($emailsSent > 0) {
                echo json_encode(['success' => true, 'message' => $emailsSent . ' notificaciones enviadas con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudieron enviar las notificaciones por correo.']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontraron usuarios asignados para esta tarea.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta a la base de datos.']);
    }
    $mysqli->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
