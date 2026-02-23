<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/db_connect.php'; // Contiene la conexión $mysqli

$response = [
    'exists' => false,
    'boleta_id' => null,
    'message' => ''
];

if (!isset($_GET['id_estudiante']) || !isset($_GET['periodo'])) {
    $response['message'] = 'Faltan parámetros de búsqueda.';
    echo json_encode($response);
    exit;
}

$id_estudiante = intval($_GET['id_estudiante']);
$periodo = trim($_GET['periodo']);

try {
    // Buscar boletas para el estudiante y período que no estén 'pagada' o 'anulada'
    $sql = "SELECT id FROM boletas 
            WHERE id_estudiante = ? 
            AND periodo = ?
            AND estado NOT IN ('pagada', 'anulada') 
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $mysqli->error);
    }

    $stmt->bind_param('is', $id_estudiante, $periodo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing_boleta = $result->fetch_assoc();
        $response['exists'] = true;
        $response['boleta_id'] = $existing_boleta['id'];
        $response['message'] = 'Ya existe una boleta para este estudiante y período.';
    }

    $stmt->close();

} catch (Exception $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

$mysqli->close();
echo json_encode($response);
?>