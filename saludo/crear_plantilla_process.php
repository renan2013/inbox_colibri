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
    
    // --- Validaciones ---
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $redirect_error = "Location: crear_plantilla.php?error=";

    if (empty($nombre) || !$categoria_id) {
        header($redirect_error . urlencode("Por favor, completa todos los campos."));
        exit();
    }

    // --- Validación de la imagen ---
    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] != UPLOAD_ERR_OK) {
        header($redirect_error . urlencode("Error al subir la imagen. Código: " . $_FILES['imagen']['error']));
        exit();
    }

    $target_dir = "../uploads/saludos/";
    $image_info = getimagesize($_FILES["imagen"]["tmp_name"]);
    $allowed_types = ['image/jpeg', 'image/png'];

    if (!$image_info || !in_array($image_info['mime'], $allowed_types)) {
        header($redirect_error . urlencode("Archivo no válido. Solo se permiten imágenes JPG o PNG."));
        exit();
    }

    // --- Procesamiento de la imagen ---
    $original_name = basename($_FILES["imagen"]["name"]);
    $extension = pathinfo($original_name, PATHINFO_EXTENSION);
    $unique_name = uniqid() . '_' . time() . '.' . $extension;
    $target_file = $target_dir . $unique_name;
    $db_path = 'uploads/saludos/' . $unique_name; // Ruta para guardar en la BD

    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
        // El archivo se ha subido, ahora insertamos en la BD
        $sql = "INSERT INTO saludo_plantillas (nombre, ruta_imagen, categoria_id) VALUES (?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ssi", $nombre, $db_path, $categoria_id);
            if ($stmt->execute()) {
                header("Location: gestionar_plantillas.php?success=" . urlencode("Plantilla creada exitosamente."));
            } else {
                unlink($target_file); // Eliminar archivo si la BD falla
                header($redirect_error . urlencode("Error al guardar en la base de datos: " . $stmt->error));
            }
            $stmt->close();
        } else {
            unlink($target_file); // Eliminar archivo si la BD falla
            header($redirect_error . urlencode("Error al preparar la consulta: " . $mysqli->error));
        }
    } else {
        header($redirect_error . urlencode("Error al mover el archivo subido."));
    }

    $mysqli->close();
    exit();

} else {
    header("Location: crear_plantilla.php");
    exit();
}
