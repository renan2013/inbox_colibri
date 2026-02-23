<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de editar programas
// if(!has_permission('editar_programas')){
//     $_SESSION['error'] = "No tienes permiso para editar programas.";
//     header("location: ../dashboard.php");
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_programa = trim($_POST['id_programa']);
    $nombre_programa = trim($_POST['nombre_programa']);
    $categoria = trim($_POST['categoria']);
    $costo_matricula = floatval($_POST['costo_matricula']);
    $informacion = trim($_POST['informacion']);
    $oferta = trim($_POST['oferta']);
    $perfil = trim($_POST['perfil']);

    if (empty($id_programa) || empty($nombre_programa)) {
        $_SESSION['error'] = "ID del programa y nombre son obligatorios.";
        header("location: editar_programa.php?id=" . $id_programa);
        exit;
    }

    // Actualizar el programa
    $sql = "UPDATE programas SET nombre_programa = ?, categoria = ?, costo_matricula = ?, informacion = ?, oferta = ?, perfil = ? WHERE id_programa = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("ssdsssi", $nombre_programa, $categoria, $costo_matricula, $informacion, $oferta, $perfil, $id_programa);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Programa actualizado exitosamente!";
            header("location: ver_programa.php?id=" . $id_programa);
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al actualizar el programa. Por favor, inténtalo de nuevo más tarde." . $stmt->error;
            header("location: editar_programa.php?id=" . $id_programa);
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: lista_programas.php");
    exit;
}
?>