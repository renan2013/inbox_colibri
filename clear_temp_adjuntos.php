<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['temp_adjuntos'])) {
    // Opcional: Eliminar los archivos físicos del directorio temp/
    // foreach ($_SESSION['temp_adjuntos'] as $adjunto_temp) {
    //     if (file_exists($adjunto_temp['temp_path'])) {
    //         unlink($adjunto_temp['temp_path']);
    //     }
    // }
    unset($_SESSION['temp_adjuntos']);
    echo json_encode(['success' => true, 'message' => 'Adjuntos temporales de sesión limpiados.']);
} else {
    echo json_encode(['success' => true, 'message' => 'No hay adjuntos temporales en la sesión.']);
}
?>