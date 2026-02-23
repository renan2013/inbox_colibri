<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de eliminar programas
// if(!has_permission('eliminar_programas')){
//     $_SESSION['error'] = "No tienes permiso para eliminar programas.";
//     header("location: ../dashboard.php");
//     exit;
// }

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_programa = trim($_GET['id']);

    // Verificar si hay planes de estudio asociados
    $sql_check = "SELECT id_plan FROM plan_estudios WHERE id_programa = ? LIMIT 1";
    if ($stmt_check = $mysqli->prepare($sql_check)) {
        $stmt_check->bind_param("i", $param_id_programa);
        $param_id_programa = $id_programa;
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $_SESSION['error'] = "No se puede eliminar el programa porque tiene planes de estudio asociados.";
            header("location: lista_programas.php");
            exit;
        }
        $stmt_check->close();
    }

    // Eliminar el programa
    $sql = "DELETE FROM programas WHERE id_programa = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $id_programa;
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Programa eliminado exitosamente!";
            header("location: lista_programas.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al eliminar el programa. Por favor, inténtalo de nuevo más tarde." . $stmt->error;
            header("location: lista_programas.php");
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    $_SESSION['error'] = "ID de programa no especificado.";
    header("location: lista_programas.php");
    exit;
}
?>