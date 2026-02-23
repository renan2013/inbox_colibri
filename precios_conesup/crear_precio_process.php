<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar la conexión a la base de datos
    if (!isset($mysqli) || $mysqli->connect_error) {
        $_SESSION['error'] = "Error crítico de conexión a la base de datos.";
        header("location: crear_precio.php");
        exit;
    }

    $nivel = trim($_POST['nivel']);
    $precio = trim($_POST['precio']);

    // Validar entradas
    if (empty($nivel) || !is_numeric($precio)) {
        $_SESSION['error'] = "Por favor, completa todos los campos correctamente.";
        header("location: crear_precio.php");
        exit;
    }

    $sql = "INSERT INTO precios_cursos_conesup (nivel, precio) VALUES (?, ?)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sd", $nivel, $precio);
        
        // El @ suprime el warning de PHP por la excepción, que ya manejamos
        if (@$stmt->execute()) {
            $_SESSION['success'] = "Nivel de precio añadido exitosamente.";
            header("location: index.php");
            exit;
        } else {
            // Comprobar si el error es por una entrada duplicada
            if ($mysqli->errno === 1062) { // 1062 es el código de error para 'Duplicate entry'
                $_SESSION['error'] = "Error: El nivel académico '" . htmlspecialchars($nivel) . "' ya existe.";
            } else {
                $_SESSION['error'] = "Error al guardar en la base de datos: " . $mysqli->error;
            }
            header("location: crear_precio.php");
            exit;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error al preparar la consulta: " . $mysqli->error;
        header("location: crear_precio.php");
        exit;
    }
    $mysqli->close();

} else {
    // Redirigir si no es un método POST
    header("location: index.php");
    exit;
}
?>