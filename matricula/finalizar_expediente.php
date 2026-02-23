<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

// --- Funciones de Ayuda ---
function recursive_scandir($dir) {
    $result = [];
    foreach(scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $result = array_merge($result, recursive_scandir($path));
        } else {
            $result[] = $path;
        }
    }
    return $result;
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    rrmdir($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        rmdir($dir);
    }
}

// 1. Validar Entradas
$student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$staging_session_id = $_POST['staging_session_id'] ?? null;

if (!$student_id || !$staging_session_id) {
    echo json_encode(['success' => false, 'error' => 'Datos de sesión o estudiante inválidos.']);
    exit;
}

// 2. Definir Rutas
$staging_base_dir = "../uploads/expedientes_digitales/staging/{$student_id}/{$staging_session_id}/";
$final_dir = "../uploads/expedientes_digitales/{$student_id}/";

if (!is_dir($staging_base_dir)) {
    echo json_encode(['success' => false, 'error' => 'No se encontraron archivos para procesar.']);
    exit;
}

if (!is_dir($final_dir)) {
    if (!mkdir($final_dir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio final.']);
        exit;
    }
}

// 3. Iniciar Transacción y Procesar Archivos
$mysqli->begin_transaction();

try {
    // Asumimos una tabla `expediente_documentos`
    $stmt = $mysqli->prepare("INSERT INTO expediente_documentos (estudiante_id, tipo_documento, ruta_archivo, fecha_subida) VALUES (?, ?, ?, NOW())");

    $staged_files_by_type = scandir($staging_base_dir);

    foreach ($staged_files_by_type as $doc_type) {
        if ($doc_type === '.' || $doc_type === '..') continue;
        
        $type_dir = $staging_base_dir . $doc_type;
        $files_in_type = scandir($type_dir);

        foreach ($files_in_type as $filename) {
            if ($filename === '.' || $filename === '..') continue;

            $staged_path = "{$type_dir}/{$filename}";
            $final_path = $final_dir . $filename;
            
            // Mover archivo
            if (!rename($staged_path, $final_path)) {
                throw new Exception("No se pudo mover el archivo: {$filename}");
            }

            // Guardar en BD
            $relative_path = "uploads/expedientes_digitales/{$student_id}/{$filename}";
            $stmt->bind_param("iss", $student_id, $doc_type, $relative_path);
            if (!$stmt->execute()) {
                throw new Exception("No se pudo guardar el registro del archivo en la base de datos.");
            }
        }
    }

    // Si todo fue bien, confirmar la transacción
    $mysqli->commit();
    $stmt->close();

    // 4. Limpiar Directorio Temporal
    rrmdir("../uploads/expedientes_digitales/staging/{$student_id}/{$staging_session_id}");

    // 5. Devolver Éxito
    echo json_encode(['success' => true, 'message' => 'Expediente digital creado y todos los archivos procesados correctamente.']);

} catch (Exception $e) {
    // Si algo falla, revertir todo
    $mysqli->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$mysqli->close();
?>
