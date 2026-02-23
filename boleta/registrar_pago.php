<?php
ini_set('display_errors', 0); // Desactivar display_errors en producción
error_reporting(E_ALL);

// Configurar un manejador de errores para registrar errores en un archivo
function log_error_pago($message) {
    $log_file = __DIR__ . '/debug_registrar_pago.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . "
", FILE_APPEND);
}

try {
    session_start();
    header('Content-Type: application/json');

    require_once '../includes/db_connect.php'; // Contiene la conexión $mysqli
    require_once '../includes/fpdf/fpdf.php';

    $response = ['success' => false, 'message' => 'Error desconocido.'];

    // Validación de sesión y permisos
    if (!isset($_SESSION['id'])) {
        throw new Exception('Acceso denegado. Debes iniciar sesión.');
    }
    // if (!has_permission($mysqli, 'registrar_pagos')) { // Descomentar si se necesita verificar permisos
    //     throw new Exception('No tienes permiso para registrar pagos.');
    // }

    // Validación de datos de entrada
    if (!isset($_POST['boleta_id']) || !isset($_POST['monto_abono']) || !isset($_POST['fecha_pago'])) {
        throw new Exception('Faltan datos esenciales para registrar el pago.');
    }

    $boleta_id = intval($_POST['boleta_id']);
    $monto_abono = floatval(str_replace(['₡', ' ', ','], '', $_POST['monto_abono'])); // Remove currency symbol, spaces, and thousand separators (commas), keep decimal dots
    $fecha_pago = trim($_POST['fecha_pago']);
    $metodo_pago = trim($_POST['metodo_pago'] ?? 'Efectivo');
    $referencia_pago = trim($_POST['referencia_pago'] ?? '');
    $observaciones_pago = trim($_POST['observaciones_pago'] ?? '');
    $registrado_por = intval($_SESSION['id']);

    if ($monto_abono <= 0) {
        throw new Exception('El monto del abono debe ser mayor que cero.');
    }

    // Iniciar transacción
    $mysqli->begin_transaction();

    // 1. Obtener datos actuales de la boleta
    $sql_boleta = "SELECT total, monto_pagado, saldo_pendiente FROM boletas WHERE id = ? FOR UPDATE"; // Bloquear la fila
    $stmt_boleta = $mysqli->prepare($sql_boleta);
    if (!$stmt_boleta) {
        throw new Exception('Error al preparar la consulta de boleta: ' . $mysqli->error);
    }
    $stmt_boleta->bind_param("i", $boleta_id);
    $stmt_boleta->execute();
    $result_boleta = $stmt_boleta->get_result();
    $boleta_actual = $result_boleta->fetch_assoc();
    $stmt_boleta->close();

    if (!$boleta_actual) {
        throw new Exception('Boleta no encontrada.');
    }

    $nuevo_monto_pagado = $boleta_actual['monto_pagado'] + $monto_abono;
    $nuevo_saldo_pendiente = $boleta_actual['saldo_pendiente'] - $monto_abono;
    $nuevo_estado = 'pago_parcial';
    if ($nuevo_saldo_pendiente <= 0.00) {
        $nuevo_estado = 'pagada';
        $nuevo_saldo_pendiente = 0.00; // Asegurar que no haya saldos negativos
    }

    // 2. Insertar el pago en la tabla `pagos`
    $sql_insert_pago = "INSERT INTO pagos (boleta_id, monto, fecha_pago, metodo_pago, referencia, registrado_por) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert_pago = $mysqli->prepare($sql_insert_pago);
    if (!$stmt_insert_pago) {
        throw new Exception('Error al preparar la inserción del pago: ' . $mysqli->error);
    }
    $stmt_insert_pago->bind_param("idsssi", $boleta_id, $monto_abono, $fecha_pago, $metodo_pago, $referencia_pago, $registrado_por);
    if (!$stmt_insert_pago->execute()) {
        throw new Exception('Error al insertar el pago en la base de datos: ' . $stmt_insert_pago->error);
    }
    $pago_id = $mysqli->insert_id;
    $stmt_insert_pago->close();

    // 3. Actualizar la boleta
    $sql_update_boleta = "UPDATE boletas SET monto_pagado = ?, saldo_pendiente = ?, estado = ? WHERE id = ?";
    $stmt_update_boleta = $mysqli->prepare($sql_update_boleta);
    if (!$stmt_update_boleta) {
        throw new Exception('Error al preparar la actualización de boleta: ' . $mysqli->error);
    }
    $stmt_update_boleta->bind_param("ddsi", $nuevo_monto_pagado, $nuevo_saldo_pendiente, $nuevo_estado, $boleta_id);
    if (!$stmt_update_boleta->execute()) {
        throw new Exception('Error al actualizar la boleta en la base de datos: ' . $stmt_update_boleta->error);
    }
    $stmt_update_boleta->close();

    // 4. Generar PDF de recibo (Placeholder por ahora)
    $recibo_pdf_url = 'uploads/recibos_pagos/recibo_' . $pago_id . '.pdf'; // Ruta simulada
    // Aquí iría la lógica real para generar el PDF del recibo
    // (Similar a la de boleta/guardar_boleta.php pero con datos de pago)
    // ...

    $mysqli->commit(); // Confirmar transacción

    $response['success'] = true;
    $response['message'] = 'Pago registrado con éxito y recibo generado.';
    $response['recibo_url'] = $recibo_pdf_url;

} catch (Throwable $e) {
    if ($mysqli && $mysqli->in_transaction) {
        $mysqli->rollback(); // Revertir transacción en caso de error
    }
    log_error_pago($e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    $response['message'] = 'Ocurrió un error en el servidor. Por favor, revise el archivo de registro `boleta/debug_registrar_pago.log`.';
}

if (isset($mysqli) && $mysqli->ping()) {
    $mysqli->close();
}

echo json_encode($response);
?>