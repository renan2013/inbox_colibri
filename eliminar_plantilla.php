<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de gestionar plantillas
if(!has_permission($mysqli, 'gestionar_plantillas')){
    $_SESSION['error'] = "No tienes permiso para eliminar plantillas.";
    header("location: dashboard.php");
    exit;
}

// Validar ID de plantilla
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    $_SESSION['error'] = "ID de plantilla no válido para eliminar.";
    header("location: gestionar_plantillas.php");
    exit;
}
$id_plantilla = trim($_GET["id"]);

$message = "";

// --- Lógica para eliminar archivos físicos adjuntos de la plantilla ---
$sql_get_adjuntos = "SELECT nombre_servidor FROM adjuntos_plantillas WHERE id_plantilla = ?";
$adjuntos_a_eliminar = [];
if($stmt_get_adjuntos = $mysqli->prepare($sql_get_adjuntos)){
    $stmt_get_adjuntos->bind_param("i", $id_plantilla);
    $stmt_get_adjuntos->execute();
    $result_get_adjuntos = $stmt_get_adjuntos->get_result();
    while($row = $result_get_adjuntos->fetch_assoc()){
        $adjuntos_a_eliminar[] = $row['nombre_servidor'];
    }
    $stmt_get_adjuntos->close();
} else {
    $message .= "Error al preparar la consulta de adjuntos de plantilla.";
}

$upload_dir = __DIR__ . '/uploads/';
foreach ($adjuntos_a_eliminar as $nombre_servidor) {
    $file_path = $upload_dir . $nombre_servidor;
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            $message .= " Error al eliminar el archivo físico de la plantilla: " . htmlspecialchars($nombre_servidor) . ".";
        }
    }
}
// --- Fin Lógica para eliminar archivos físicos adjuntos de la plantilla ---

// Eliminar la plantilla de la base de datos
$sql_delete_plantilla = "DELETE FROM tarea_plantillas WHERE id = ?";
if($stmt_delete_plantilla = $mysqli->prepare($sql_delete_plantilla)){
    $stmt_delete_plantilla->bind_param("i", $id_plantilla);
    if($stmt_delete_plantilla->execute()){
        $_SESSION['success'] = "Plantilla eliminada con éxito." . (!empty($message) ? " " . $message : "");
        header("location: gestionar_plantillas.php");
        exit;
    } else {
        $_SESSION['error'] = "Error al eliminar la plantilla de la base de datos." . (!empty($message) ? " " . $message : "");
        header("location: gestionar_plantillas.php");
        exit;
    }
    $stmt_delete_plantilla->close();
} else {
    $_SESSION['error'] = "Error al preparar la eliminación de la plantilla." . (!empty($message) ? " " . $message : "");
    header("location: gestionar_plantillas.php");
    exit;
}

$mysqli->close();
?>