<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Permisos y validación de entrada
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$redirect_error = "Location: gestionar_plantillas.php?error=";
$redirect_success = "Location: gestionar_plantillas.php?success=";

if (!$id) {
    header($redirect_error . urlencode("ID de plantilla no válido."));
    exit();
}

// 1. Obtener la ruta del archivo antes de borrar el registro
$sql_select = "SELECT ruta_imagen FROM saludo_plantillas WHERE id = ?";
$ruta_imagen = null;
if ($stmt_select = $mysqli->prepare($sql_select)) {
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $stmt_select->bind_result($ruta_imagen);
    $stmt_select->fetch();
    $stmt_select->close();
}

if (!$ruta_imagen) {
    header($redirect_error . urlencode("No se encontró la plantilla para eliminar."));
    exit();
}

// 2. Eliminar el registro de la base de datos
$sql_delete = "DELETE FROM saludo_plantillas WHERE id = ?";
if ($stmt_delete = $mysqli->prepare($sql_delete)) {
    $stmt_delete->bind_param("i", $id);
    
    if ($stmt_delete->execute()) {
        // 3. Si la eliminación de la BD fue exitosa, eliminar el archivo físico
        $file_path = '../' . $ruta_imagen;
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        header($redirect_success . urlencode("Plantilla eliminada exitosamente."));
    } else {
        header($redirect_error . urlencode("Error al eliminar la plantilla de la base de datos: " . $stmt_delete->error));
    }
    $stmt_delete->close();
} else {
    header($redirect_error . urlencode("Error al preparar la consulta de eliminación: " . $mysqli->error));
}

$mysqli->close();
exit();
