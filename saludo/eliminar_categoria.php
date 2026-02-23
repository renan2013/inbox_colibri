<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Permisos y validación
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: gestionar_categorias.php?error=" . urlencode("ID de categoría no válido."));
    exit();
}

// Eliminar de la base de datos
// La restricción FOREIGN KEY con ON DELETE CASCADE en la tabla `saludo_plantillas`
// se encargará de eliminar las plantillas asociadas.
$sql = "DELETE FROM saludo_categorias WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: gestionar_categorias.php?success=" . urlencode("Categoría eliminada exitosamente."));
    } else {
        header("Location: gestionar_categorias.php?error=" . urlencode("Error al eliminar la categoría: " . $stmt->error));
    }
    $stmt->close();
} else {
    header("Location: gestionar_categorias.php?error=" . urlencode("Error al preparar la consulta: " . $mysqli->error));
}
$mysqli->close();
exit();
