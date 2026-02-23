<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página
if (!isset($_SESSION['id']) || !has_permission($mysqli, 'gestionar_usuarios')) {
    require_once 'includes/config.php';
    header("location: " . BASE_URL . "login.php?error=No tienes permiso para eliminar grupos");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_curso_activo = $_GET['id'];

    // Iniciar transacción
    $mysqli->begin_transaction();

    try {
        // 1. Eliminar matrículas asociadas
        $sql_matriculas = "DELETE FROM matriculas WHERE id_curso_activo = ?";
        $stmt_matriculas = $mysqli->prepare($sql_matriculas);
        $stmt_matriculas->bind_param("i", $id_curso_activo);
        $stmt_matriculas->execute();
        $stmt_matriculas->close();

        // 2. Eliminar el curso activo
        $sql_curso = "DELETE FROM cursos_activos WHERE id_curso_activo = ?";
        $stmt_curso = $mysqli->prepare($sql_curso);
        $stmt_curso->bind_param("i", $id_curso_activo);
        $stmt_curso->execute();
        
        // Verificar si se eliminó el curso
        $rows_affected = $stmt_curso->affected_rows;
        $stmt_curso->close();

        if ($rows_affected > 0) {
            // Si todo fue bien, confirmar transacción
            $mysqli->commit();
            $_SESSION['message'] = "Grupo de estudio y sus matrículas han sido eliminados con éxito.";
        } else {
            // Si el grupo no existía, no se eliminó nada.
            throw new Exception("El grupo de estudio no fue encontrado o ya había sido eliminado.");
        }

    } catch (Exception $e) {
        // Si algo falla, revertir transacción
        $mysqli->rollback();
        $_SESSION['error'] = "Error al eliminar el grupo: " . $e->getMessage();
    }

} else {
    $_SESSION['error'] = "ID de grupo de estudio no válido.";
}

$mysqli->close();
header("location: gestionar_grupos.php");
exit;
?>
