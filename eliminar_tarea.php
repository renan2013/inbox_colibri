<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'eliminar_tareas')){
    header("location: dashboard.php?error=No tienes permiso para eliminar tareas");
    exit;
}

// Validar ID de la tarea
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: dashboard.php?error=ID de tarea no válido para eliminar");
    exit;
}
$id_tarea = trim($_GET["id"]);

$message = "";

// --- Lógica para eliminar archivos físicos adjuntos ---
$sql_get_adjuntos = "SELECT nombre_servidor FROM adjuntos WHERE id_tarea = ?";
$adjuntos_a_eliminar = [];
if($stmt_get_adjuntos = $mysqli->prepare($sql_get_adjuntos)){
    $stmt_get_adjuntos->bind_param("i", $id_tarea);
    $stmt_get_adjuntos->execute();
    $result_get_adjuntos = $stmt_get_adjuntos->get_result();
    while($row = $result_get_adjuntos->fetch_assoc()){
        $adjuntos_a_eliminar[] = $row['nombre_servidor'];
    }
    $stmt_get_adjuntos->close();
} else {
    $message .= "Error al preparar la consulta de adjuntos.";
}

$upload_dir = __DIR__ . '/uploads/';
foreach ($adjuntos_a_eliminar as $nombre_servidor) {
    $file_path = $upload_dir . $nombre_servidor;
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            $message .= " Error al eliminar el archivo físico: " . htmlspecialchars($nombre_servidor) . ".";
        }
    }
}
// --- Fin Lógica para eliminar archivos físicos adjuntos ---

// Eliminar la tarea de la base de datos
$sql_delete_tarea = "DELETE FROM tareas WHERE id = ?";
if($stmt_delete_tarea = $mysqli->prepare($sql_delete_tarea)){
    $stmt_delete_tarea->bind_param("i", $id_tarea);
    if($stmt_delete_tarea->execute()){
        header("location: dashboard.php?success=" . urlencode("Tarea eliminada con éxito."));
        exit;
    } else {
        $error_message = "Error al eliminar la tarea de la base de datos.";
        if (!empty($message)) { // If there was a file deletion error
            $error_message .= " " . $message;
        }
        header("location: dashboard.php?error=" . urlencode($error_message));
        exit;
    }
    $stmt_delete_tarea->close();
} else {
    $error_message = "Error al preparar la eliminación de la tarea.";
    if (!empty($message)) { // If there was a file deletion error
        $error_message .= " " . $message;
    }
    header("location: dashboard.php?error=" . urlencode($error_message));
    exit;
}

$mysqli->close();
// The final if (!empty($message)) block is now redundant and should be removed.
// It was meant to catch errors if the script continued without redirecting,
// but now all error paths lead to a header redirect.
?>