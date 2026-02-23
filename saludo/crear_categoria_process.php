<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Permisos y validación
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');

    if (empty($nombre)) {
        header("Location: gestionar_categorias.php?error=" . urlencode("El nombre de la categoría no puede estar vacío."));
        exit();
    }

    // Insertar en la base de datos
    $sql = "INSERT INTO saludo_categorias (nombre) VALUES (?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $nombre);
        if ($stmt->execute()) {
            header("Location: gestionar_categorias.php?success=" . urlencode("Categoría creada exitosamente."));
        } else {
            header("Location: gestionar_categorias.php?error=" . urlencode("Error al crear la categoría: " . $stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: gestionar_categorias.php?error=" . urlencode("Error al preparar la consulta: " . $mysqli->error));
    }
    $mysqli->close();
    exit();

} else {
    header("Location: gestionar_categorias.php");
    exit();
}
