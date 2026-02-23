<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])) {
    header("location: " . $base_url . "/soporte/gestionar_soportes.php?error=ID de artículo no válido para eliminar");
    exit;
}
$id_soporte = trim($_GET["id"]);

// Eliminar el artículo de la base de datos
$sql_delete_soporte = "DELETE FROM soporte WHERE id = ?";
if ($stmt_delete_soporte = $mysqli->prepare($sql_delete_soporte)) {
    $stmt_delete_soporte->bind_param("i", $id_soporte);
    if ($stmt_delete_soporte->execute()) {
        header("location: lista_soporte.php?success=" . urlencode("Artículo eliminado con éxito."));
        exit;
    } else {
        header("location: gestionar_soportes.php?error=" . urlencode("Error al eliminar el artículo."));
        exit;
    }
    $stmt_delete_soporte->close();
} else {
    header("location: gestionar_soportes.php?error=" . urlencode("Error al preparar la eliminación del artículo."));
    exit;
}

$mysqli->close();
?>