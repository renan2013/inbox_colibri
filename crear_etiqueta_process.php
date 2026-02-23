<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página: solo para usuarios con permiso
if(!has_permission($mysqli, 'gestionar_plantillas')){ // Usando un permiso existente temporalmente
    $_SESSION['error'] = "No tienes permiso para crear etiquetas.";
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);

    if (empty($nombre)) {
        $_SESSION['error'] = "El nombre de la etiqueta es obligatorio.";
        header("location: crear_etiqueta.php");
        exit;
    }

    // Verificar si la etiqueta ya existe
    $sql = "SELECT id FROM etiquetas WHERE nombre = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $param_nombre);
        $param_nombre = $nombre;
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $_SESSION['error'] = "Esta etiqueta ya existe.";
                header("location: crear_etiqueta.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al verificar la etiqueta.";
            header("location: crear_etiqueta.php");
            exit;
        }
        $stmt->close();
    }

    // Insertar la nueva etiqueta
    $sql = "INSERT INTO etiquetas (nombre) VALUES (?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $param_nombre);
        $param_nombre = $nombre;
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Etiqueta creada exitosamente!";
            header("location: gestionar_etiquetas.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al crear la etiqueta.";
            header("location: crear_etiqueta.php");
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: crear_etiqueta.php");
    exit;
}
?>