<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página: solo para usuarios con permiso
if(!has_permission($mysqli, 'gestionar_plantillas')){ // Usando un permiso existente temporalmente
    $_SESSION['error'] = "No tienes permiso para editar etiquetas.";
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);

    if (empty($nombre) || empty($id) || !ctype_digit($id)) {
        $_SESSION['error'] = "Datos no válidos.";
        header("location: gestionar_etiquetas.php");
        exit;
    }

    // Verificar si el nombre ya existe para otra etiqueta
    $sql_check_nombre = "SELECT id FROM etiquetas WHERE nombre = ? AND id != ?";
    if ($stmt_check_nombre = $mysqli->prepare($sql_check_nombre)) {
        $stmt_check_nombre->bind_param("si", $nombre, $id);
        
        if ($stmt_check_nombre->execute()) {
            $stmt_check_nombre->store_result();
            
            if ($stmt_check_nombre->num_rows > 0) {
                $_SESSION['error'] = "Ese nombre de etiqueta ya está en uso.";
                header("location: editar_etiqueta.php?id=" . $id);
                exit;
            }
        }
        $stmt_check_nombre->close();
    }

    // Actualizar la etiqueta
    $sql_update = "UPDATE etiquetas SET nombre = ? WHERE id = ?";
    if ($stmt_update = $mysqli->prepare($sql_update)) {
        $stmt_update->bind_param("si", $nombre, $id);
        
        if ($stmt_update->execute()) {
            $_SESSION['success'] = "Etiqueta actualizada exitosamente.";
            header("location: gestionar_etiquetas.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al actualizar la etiqueta.";
            header("location: editar_etiqueta.php?id=" . $id);
            exit;
        }
        $stmt_update->close();
    }
    
    $mysqli->close();
} else {
    header("location: gestionar_etiquetas.php");
    exit;
}
?>