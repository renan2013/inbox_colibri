<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso
if(!has_permission($mysqli, 'gestionar_plantillas')){ // Usando un permiso existente temporalmente
    $_SESSION['error'] = "No tienes permiso para eliminar etiquetas.";
    header("location: dashboard.php");
    exit;
}

// Validar ID de etiqueta
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    $_SESSION['error'] = "ID de etiqueta no válido para eliminar.";
    header("location: gestionar_etiquetas.php");
    exit;
}
$id_etiqueta = trim($_GET["id"]);

// Eliminar la etiqueta de la base de datos
$sql_delete_etiqueta = "DELETE FROM etiquetas WHERE id = ?";
if($stmt_delete_etiqueta = $mysqli->prepare($sql_delete_etiqueta)){
    $stmt_delete_etiqueta->bind_param("i", $id_etiqueta);
    if($stmt_delete_etiqueta->execute()){
        $_SESSION['success'] = "Etiqueta eliminada con éxito.";
    } else {
        $_SESSION['error'] = "Error al eliminar la etiqueta.";
    }
    $stmt_delete_etiqueta->close();
} else {
    $_SESSION['error'] = "Error al preparar la eliminación de la etiqueta.";
}

$mysqli->close();

header("location: gestionar_etiquetas.php");
exit;
?>