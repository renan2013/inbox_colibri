<?php
session_start();

// Directorio temporal para las subidas
$temp_upload_dir = __DIR__ . '/uploads/temp/';

// Asegurarse de que el directorio exista
if (!is_dir($temp_upload_dir)) {
    mkdir($temp_upload_dir, 0777, true);
}

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['file'];

    // Validaciones básicas
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
    $max_size = 5 * 1024 * 1024; // 5 MB

    if (!in_array($file['type'], $allowed_types)) {
        $response['message'] = 'Tipo de archivo no permitido.';
    } elseif ($file['size'] > $max_size) {
        $response['message'] = 'El archivo es demasiado grande (máx. 5MB).';
    } else {
        $original_name = basename($file['name']);
        $extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $server_name = uniqid('temp_') . '.' . $extension;
        $temp_path = $temp_upload_dir . $server_name;

        if (move_uploaded_file($file['tmp_name'], $temp_path)) {
            // Guardar metadatos en la sesión
            if (!isset($_SESSION['temp_adjuntos'])) {
                $_SESSION['temp_adjuntos'] = [];
            }
            $_SESSION['temp_adjuntos'][] = [
                'original_name' => $original_name,
                'server_name' => $server_name,
                'temp_path' => $temp_path,
                'type' => $file['type'],
                'size' => $file['size']
            ];
            $response['success'] = true;
            $response['message'] = 'Archivo subido temporalmente.';
            $response['file'] = [
                'original_name' => $original_name,
                'server_name' => $server_name
            ];
        } else {
            $response['message'] = 'Error al mover el archivo temporal.';
        }
    }
} else {
    $response['message'] = 'No se recibió ningún archivo o hubo un error de subida.';
    if (isset($_FILES['file']['error'])) {
        $response['error_code'] = $_FILES['file']['error'];
    }
}

echo json_encode($response);
?>