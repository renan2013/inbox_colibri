<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Verificar si el precio está siendo usado en plan_estudios
// OJO: Esta es una simplificación. Lo ideal sería buscar por el ID, no por el precio.
// Como no guardamos el id_precio_curso en plan_estudios, la comprobación no es directa.
// Por ahora, permitiremos la eliminación. En una futura mejora, se podría añadir una FK.

$sql = "DELETE FROM precios_cursos_conesup WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "Nivel de precio eliminado exitosamente.";
        } else {
            $_SESSION['error'] = "No se encontró el nivel de precio para eliminar.";
        }
    } else {
        $_SESSION['error'] = "Error al eliminar el nivel de precio. Puede que esté en uso en algún plan de estudios.";
    }
    $stmt->close();
}
$mysqli->close();

header("location: index.php");
exit;
?>
