<?php
header('Content-Type: application/json');
require_once 'includes/db_connect.php'; // Contiene la conexión $mysqli

// Iniciar la sesión para cualquier verificación de permisos futura
session_start();
// require_once 'includes/permissions.php'; // Descomentar si se necesita verificar permisos

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Verificar que se ha proporcionado un id de estudiante
if (!isset($_GET['id_estudiante']) || empty($_GET['id_estudiante'])) {
    $response['message'] = 'No se proporcionó un ID de estudiante.';
    echo json_encode($response);
    exit;
}

$id_estudiante = intval($_GET['id_estudiante']);

try {
    // 1. Obtener información del estudiante
    $stmt_usuario = $mysqli->prepare("SELECT id, nombre, apellidos, email FROM usuarios WHERE id = ?");
    $stmt_usuario->bind_param('i', $id_estudiante);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    $estudiante = $result_usuario->fetch_assoc();
    $stmt_usuario->close();

    if (!$estudiante) {
        throw new Exception("Estudiante no encontrado.");
    }

    // 2. Obtener resumen financiero: Deuda total
    $stmt_deuda = $mysqli->prepare("SELECT SUM(saldo_pendiente) AS deuda_total FROM boletas WHERE id_estudiante = ? AND estado NOT IN ('pagada', 'anulada')");
    $stmt_deuda->bind_param('i', $id_estudiante);
    $stmt_deuda->execute();
    $result_deuda = $stmt_deuda->get_result();
    $deuda_total_row = $result_deuda->fetch_assoc();
    $deuda_total = $deuda_total_row['deuda_total'];
    $stmt_deuda->close();

    // 3. Obtener el historial de todas las boletas
    $stmt_boletas = $mysqli->prepare("SELECT id, numero_boleta, total, monto_pagado, saldo_pendiente, estado, fecha_creacion FROM boletas WHERE id_estudiante = ? ORDER BY fecha_creacion DESC");
    $stmt_boletas->bind_param('i', $id_estudiante);
    $stmt_boletas->execute();
    $result_boletas = $stmt_boletas->get_result();
    $boletas = $result_boletas->fetch_all(MYSQLI_ASSOC);
    $stmt_boletas->close();

    // 4. Obtener todos los pagos realizados por el estudiante
    $stmt_pagos = $mysqli->prepare("
        SELECT p.id, p.boleta_id, b.numero_boleta, p.monto, p.fecha_pago, p.metodo_pago, p.referencia 
        FROM pagos p
        JOIN boletas b ON p.boleta_id = b.id
        WHERE b.id_estudiante = ? 
        ORDER BY p.fecha_pago DESC
    ");
    $stmt_pagos->bind_param('i', $id_estudiante);
    $stmt_pagos->execute();
    $result_pagos = $stmt_pagos->get_result();
    $pagos = $result_pagos->fetch_all(MYSQLI_ASSOC);
    $stmt_pagos->close();
    
    // 5. Obtener todos los arreglos de pago
    $stmt_arreglos = $mysqli->prepare("SELECT * FROM arreglos_pago WHERE estudiante_id = ? ORDER BY fecha_creacion DESC");
    $stmt_arreglos->bind_param('i', $id_estudiante);
    $stmt_arreglos->execute();
    $result_arreglos = $stmt_arreglos->get_result();
    $arreglos = $result_arreglos->fetch_all(MYSQLI_ASSOC);
    $stmt_arreglos->close();

    $response['success'] = true;
    $response['data'] = [
        'estudiante' => $estudiante,
        'resumen' => [
            'deuda_total' => floatval($deuda_total) ?? 0.00,
        ],
        'boletas' => $boletas,
        'pagos' => $pagos,
        'arreglos' => $arreglos,
    ];

} catch (Exception $e) {
    $response['message'] = 'Error al consultar los datos: ' . $e->getMessage();
}

$mysqli->close();
echo json_encode($response, JSON_PRETTY_PRINT);

?>