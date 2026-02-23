<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);
    $nivel = trim($_POST['nivel']);
    $precio = trim($_POST['precio']);

    if (empty($id) || empty($nivel) || !is_numeric($precio)) {
        $_SESSION['error'] = "Por favor, completa todos los campos correctamente.";
        header("location: editar_precio.php?id=" . $id);
        exit;
    }

    $sql = "UPDATE precios_cursos_conesup SET nivel = ?, precio = ? WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sdi", $nivel, $precio, $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Nivel de precio actualizado exitosamente.";
            header("location: index.php");
        } else {
            $_SESSION['error'] = "Error al actualizar el nivel de precio. Es posible que el nombre del nivel ya exista.";
            header("location: editar_precio.php?id=" . $id);
        }
        $stmt->close();
    }
    $mysqli->close();
} else {
    header("location: index.php");
    exit;
}
?>
