<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php'; // Para futuras validaciones

// 1. Validar Entradas
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$staging_session_id = $_POST['staging_session_id'] ?? null;
$doc_type = $_POST['doc_type'] ?? null;

if (!$student_id || !$staging_session_id || !$doc_type) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

if (empty($_FILES['files'])) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron archivos.']);
    exit;
}

// 2. Crear Directorio Temporal (Staging)
// Formato: uploads/expedientes_digitales/staging/{student_id}/{session_id}/
$staging_dir = "../uploads/expedientes_digitales/staging/{$student_id}/{$staging_session_id}/{$doc_type}/";
if (!is_dir($staging_dir)) {
    if (!mkdir($staging_dir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio temporal.']);
        exit;
    }
}

// 3. Procesar Archivos
$uploaded_files = [];
$errors = [];
$files = $_FILES['files'];

foreach ($files['name'] as $key => $name) {
    if ($files['error'][$key] === UPLOAD_ERR_OK) {
        $tmp_name = $files['tmp_name'][$key];
        
        // Limpiar nombre de archivo para seguridad
        $safe_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($name));
        $file_target_path = $staging_dir . $safe_name;

        if (move_uploaded_file($tmp_name, $file_target_path)) {
            $uploaded_files[] = $safe_name;
        } else {
            $errors[] = "Error al mover el archivo: " . htmlspecialchars($name);
        }
    } else {
        $errors[] = "Error en la subida del archivo: " . htmlspecialchars($name);
    }
}

// 4. Devolver Respuesta
if (empty($errors)) {
    echo json_encode(['success' => true, 'uploaded_files' => $uploaded_files]);
} else {
    // Si hay errores, podrías querer eliminar los archivos que sí se subieron en esta tanda.
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
}
?>
