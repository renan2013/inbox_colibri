<?php
session_start();
require_once '../includes/db_connect.php';

// Ya que la verificación de dependencias se hace con AJAX antes de llamar a este script,
// no necesitamos el complejo bloque try-catch para el error de FK 1451.
// Este script ahora asume que solo se llama si la eliminación es segura.

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_plan = $_GET['id'];

    // Usamos mysqli en modo procedural para un control simple
    $sql = "DELETE FROM plan_estudios WHERE id_plan = ?";
    
    $stmt = mysqli_prepare($mysqli, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_plan);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $_SESSION['success'] = "¡Curso eliminado exitosamente!";
            } else {
                $_SESSION['error'] = "No se encontró el curso para eliminar. Es posible que ya haya sido borrado.";
            }
        } else {
            // Este error no debería ocurrir si la verificación AJAX funciona, pero es una salvaguarda.
            $_SESSION['error'] = "Ocurrió un error al ejecutar la eliminación.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Error al preparar la consulta de eliminación.";
    }

} else {
    $_SESSION['error'] = "ID de curso no especificado o no válido.";
}

if (isset($mysqli)) {
    $mysqli->close();
}

// Redirigir siempre de vuelta a la lista
header("Location: lista_cursos.php");
exit;
?>
