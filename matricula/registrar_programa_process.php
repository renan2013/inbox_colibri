<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de crear programas
// if(!has_permission('crear_programas')){
//     $_SESSION['error'] = "No tienes permiso para registrar programas.";
//     header("location: ../dashboard.php");
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_programa = trim($_POST['nombre_programa']);
    $categoria = trim($_POST['categoria']);
    $costo_matricula = floatval($_POST['costo_matricula']);
    $informacion = trim($_POST['informacion']);
    $oferta = trim($_POST['oferta']);
    $perfil = trim($_POST['perfil']);

    if (empty($nombre_programa)) {
        $_SESSION['error'] = "El nombre del programa es obligatorio.";
        header("location: registrar_programa.php");
        exit;
    }

    // Insertar el nuevo programa
    $sql = "INSERT INTO programas (nombre_programa, categoria, costo_matricula, informacion, oferta, perfil) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssdsss", $nombre_programa, $categoria, $costo_matricula, $informacion, $oferta, $perfil);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Programa registrado exitosamente.";
            header("location: lista_programas.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al registrar el programa. Por favor, inténtalo de nuevo más tarde. " . $stmt->error;
            header("location: registrar_programa.php");
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: registrar_programa.php");
    exit;
}
?>