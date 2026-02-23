<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/db_connect.php'; // Contains the $mysqli connection
require_once '../includes/permissions.php'; // For has_permission function

$response = [
    'success' => false,
    'message' => '',
    'boleta' => null,
    'pagos' => []
];

// Check if user is logged in and has permission (adjust permission as needed)
if (!isset($_SESSION['user_id']) || !has_permission($mysqli, 'gestionar_expedientes')) {
    $response['message'] = 'Acceso denegado. No tienes permisos suficientes.';
    echo json_encode($response);
    exit;
}

if (!isset($_GET['boleta_id'])) {
    $response['message'] = 'ID de boleta no proporcionado.';
    echo json_encode($response);
    exit;
}

$boleta_id = intval($_GET['boleta_id']);

try {
    // 1. Get boleta details
    $sql_boleta = "SELECT b.*, u.nombre AS estudiante_nombre, u.apellidos AS estudiante_apellidos, u.email AS estudiante_email, u.cedula AS estudiante_cedula
                   FROM boletas b
                   JOIN usuarios u ON b.id_estudiante = u.id
                   WHERE b.id = ?";
    $stmt_boleta = $mysqli->prepare($sql_boleta);
    if (!$stmt_boleta) {
        throw new Exception('Error al preparar la consulta de boleta: ' . $mysqli->error);
    }
    $stmt_boleta->bind_param('i', $boleta_id);
    $stmt_boleta->execute();
    $result_boleta = $stmt_boleta->get_result();
    $boleta = $result_boleta->fetch_assoc();
    $stmt_boleta->close();

    if (!$boleta) {
        $response['message'] = 'Boleta no encontrada.';
        echo json_encode($response);
        exit;
    }

    $response['boleta'] = $boleta;

    // 2. Get payments for the boleta
    $sql_pagos = "SELECT p.*, u.nombre AS registrado_por_nombre, u.apellidos AS registrado_por_apellidos
                  FROM pagos p
                  LEFT JOIN usuarios u ON p.registrado_por = u.id
                  WHERE p.boleta_id = ?
                  ORDER BY p.fecha_pago ASC";
    $stmt_pagos = $mysqli->prepare($sql_pagos);
    if (!$stmt_pagos) {
        throw new Exception('Error al preparar la consulta de pagos: ' . $mysqli->error);
    }
    $stmt_pagos->bind_param('i', $boleta_id);
    $stmt_pagos->execute();
    $result_pagos = $stmt_pagos->get_result();
    $pagos = [];
    while ($row = $result_pagos->fetch_assoc()) {
        $pagos[] = $row;
    }
    $stmt_pagos->close();

    $response['pagos'] = $pagos;
    $response['success'] = true;

} catch (Exception $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

$mysqli->close();
echo json_encode($response);
?>