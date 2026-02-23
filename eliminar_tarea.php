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

// Iniciar una transacción para asegurar que todo se borre o nada se borre
$mysqli->begin_transaction();

try {
    // 1. Eliminar asistencias relacionadas
    $sql_asistencias = "DELETE FROM asistencias WHERE id_tarea = ?";
    $stmt_asistencias = $mysqli->prepare($sql_asistencias);
    $stmt_asistencias->bind_param("i", $id_tarea);
    $stmt_asistencias->execute();
    $stmt_asistencias->close();

    // 2. Eliminar asignaciones relacionadas
    $sql_asignaciones = "DELETE FROM tarea_asignaciones WHERE id_tarea = ?";
    $stmt_asignaciones = $mysqli->prepare($sql_asignaciones);
    $stmt_asignaciones->bind_param("i", $id_tarea);
    $stmt_asignaciones->execute();
    $stmt_asignaciones->close();

    // 3. Eliminar etiquetas relacionadas
    $sql_etiquetas = "DELETE FROM tarea_etiquetas WHERE id_tarea = ?";
    $stmt_etiquetas = $mysqli->prepare($sql_etiquetas);
    $stmt_etiquetas->bind_param("i", $id_tarea);
    $stmt_etiquetas->execute();
    $stmt_etiquetas->close();

    // 4. Eliminar comentarios relacionados
    $sql_comentarios = "DELETE FROM comentarios WHERE id_tarea = ?";
    $stmt_comentarios = $mysqli->prepare($sql_comentarios);
    $stmt_comentarios->bind_param("i", $id_tarea);
    $stmt_comentarios->execute();
    $stmt_comentarios->close();

    // 5. Eliminar adjuntos relacionados en DB
    $sql_adjuntos = "DELETE FROM adjuntos WHERE id_tarea = ?";
    $stmt_adjuntos = $mysqli->prepare($sql_adjuntos);
    $stmt_adjuntos->bind_param("i", $id_tarea);
    $stmt_adjuntos->execute();
    $stmt_adjuntos->close();

    // 6. Finalmente eliminar la tarea de la base de datos
    $sql_delete_tarea = "DELETE FROM tareas WHERE id = ?";
    $stmt_delete_tarea = $mysqli->prepare($sql_delete_tarea);
    $stmt_delete_tarea->bind_param("i", $id_tarea);
    $stmt_delete_tarea->execute();
    $stmt_delete_tarea->close();

    // Confirmar cambios
    $mysqli->commit();

    header("location: dashboard.php?success=" . urlencode("Tarea y todos sus datos relacionados eliminados con éxito."));
    exit;

} catch (mysqli_sql_exception $exception) {
    $mysqli->rollback();
    $error_message = "Error crítico al eliminar la tarea: " . $exception->getMessage();
    header("location: dashboard.php?error=" . urlencode($error_message));
    exit;
}

$mysqli->close();
// The final if (!empty($message)) block is now redundant and should be removed.
// It was meant to catch errors if the script continued without redirecting,
// but now all error paths lead to a header redirect.
?>