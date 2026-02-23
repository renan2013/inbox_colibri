<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Verificar login y permisos
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403);
    exit("Acceso denegado.");
}
if (!has_permission($mysqli, 'ver_cumpleanos')) {
    http_response_code(403);
    exit("No tienes permisos para realizar esta acción.");
}

// Verificar que la solicitud sea por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validar datos recibidos
    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $plantilla_id = filter_input(INPUT_POST, 'plantilla_id', FILTER_VALIDATE_INT);
    $mensaje_personalizado = filter_input(INPUT_POST, 'mensaje_personalizado', FILTER_SANITIZE_STRING);
    $creado_por = $_SESSION['id']; // El admin que está creando la tarjeta

    if (!$usuario_id || !$plantilla_id) {
        header("Location: preparar_tarjeta.php?usuario_id=" . ($usuario_id ?: '') . "&error=" . urlencode("Datos inválidos. Por favor, inténtalo de nuevo."));
        exit();
    }
    
    // Insertar el saludo en la base de datos
    $sql = "INSERT INTO saludos_enviados (plantilla_id, usuario_id, mensaje_personalizado, creado_por) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iisi", $plantilla_id, $usuario_id, $mensaje_personalizado, $creado_por);
        
        if ($stmt->execute()) {
            // Inserción exitosa, obtener el ID del nuevo saludo
            $nuevo_saludo_id = $mysqli->insert_id;
            
            // Redirigir a la página para ver/descargar la tarjeta
            header("Location: ver_tarjeta.php?id=" . $nuevo_saludo_id);
            exit();
        } else {
            // Error en la ejecución
            $error_message = "Error al guardar la tarjeta: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Error en la preparación
        $error_message = "Error al preparar la consulta: " . $mysqli->error;
    }
    
    $mysqli->close();

    // Si hubo un error, redirigir de vuelta con un mensaje
    header("Location: preparar_tarjeta.php?usuario_id=" . $usuario_id . "&error=" . urlencode($error_message));
    exit();

} else {
    // Si no es POST, redirigir al dashboard
    header("Location: ../dashboard.php");
    exit();
}
