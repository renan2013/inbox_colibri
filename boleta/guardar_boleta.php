<?php
ini_set('display_errors', 0); // Desactivar display_errors en producción
error_reporting(E_ALL);

// Configurar un manejador de errores para registrar errores en un archivo
function log_error_boleta($message) {
    $log_file = __DIR__ . '/debug_guardar_boleta.log';
    file_put_contents($log_file, date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

// Envolver toda la lógica en un bloque try-catch para capturar excepciones
try {
    session_start();
    header('Content-Type: application/json');

    require_once '../includes/db_connect.php';
    require_once '../includes/fpdf/fpdf.php';

    $response = ['success' => false, 'message' => 'Error desconocido.'];

    // Validación de datos de entrada
    if (!isset($_SESSION['id'])) {
        throw new Exception('Acceso denegado. Debes iniciar sesión.');
    }
    if (!isset($_POST['id_estudiante']) || !isset($_POST['total']) || !isset($_POST['signature']) || !isset($_POST['periodo'])) {
        throw new Exception('Faltan datos para procesar la boleta.');
    }

    $id_estudiante = intval($_POST['id_estudiante']);
    $id_creador = intval($_SESSION['id']);
    
    // Limpiar y convertir el total a float
    $total_raw = $_POST['total'];
    $total = preg_replace('/[^\d,.]/', '', $total_raw); // Eliminar todo lo que no sea dígito, coma o punto
    $total = str_replace('.', '', $total);             // Eliminar puntos (separadores de miles)
    $total = str_replace(',', '.', $total);             // Reemplazar coma decimal por punto decimal
    $total = floatval($total);

    $signature_data_url = $_POST['signature'];
    $periodo = trim($_POST['periodo']);

    // 1. Guardar la firma como imagen
    $signature_path = ''; // Ruta relativa para la DB
    $signature_path_full = ''; // Ruta física para guardar el archivo
    if (!empty($signature_data_url)) {
        if (preg_match('/^data:image\/(png|jpeg);base64,/', $signature_data_url)) {
            $img = preg_replace('/^data:image\/(png|jpeg);base64,/', '', $signature_data_url);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            
            $upload_dir_physical = __DIR__ . '/../uploads/firmas_boletas/';
            if (!is_dir($upload_dir_physical) && !mkdir($upload_dir_physical, 0777, true)) {
                throw new Exception('No se pudo crear el directorio de firmas.');
            }
            $filename = 'firma_' . uniqid() . '.png';
            $signature_path_full = $upload_dir_physical . $filename;
            
            if (file_put_contents($signature_path_full, $data) === false) {
                throw new Exception('Error al guardar la imagen de la firma.');
            }
            $signature_path = 'uploads/firmas_boletas/' . $filename;
        } else {
            throw new Exception('El formato de la firma no es válido.');
        }
    }

    // 2. Insertar registro en la tabla `boletas`
    $sql_insert = "INSERT INTO boletas (id_estudiante, id_creador, total, ruta_firma, saldo_pendiente, estado, periodo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $mysqli->prepare($sql_insert);
    if (!$stmt_insert) {
        throw new Exception('Error al preparar la inserción: ' . $mysqli->error);
    }
    
    $estado_inicial = 'pendiente';
    $stmt_insert->bind_param("iidsdss", $id_estudiante, $id_creador, $total, $signature_path, $total, $estado_inicial, $periodo);
    
    if (!$stmt_insert->execute()) {
        if (!empty($signature_path_full) && file_exists($signature_path_full)) unlink($signature_path_full);
        throw new Exception('Error al insertar el registro en la base de datos: ' . $stmt_insert->error);
    }
    $id_boleta = $mysqli->insert_id;
    $stmt_insert->close();

    // 3. Crear el número de boleta formateado y actualizar el registro
    $numero_boleta_formateado = "B-" . str_pad($id_boleta, 6, "0", STR_PAD_LEFT);
    $sql_update_numero = "UPDATE boletas SET numero_boleta = ? WHERE id = ?";
    if ($stmt_update_numero = $mysqli->prepare($sql_update_numero)) {
        $stmt_update_numero->bind_param("si", $numero_boleta_formateado, $id_boleta);
        $stmt_update_numero->execute();
        $stmt_update_numero->close();
    }

    // 4. Obtener todos los datos necesarios para el PDF
    $datos_pdf = [];
    $sql_usuario = "SELECT nombre, apellidos, cedula, email FROM usuarios WHERE id = ?";
    $stmt_usuario = $mysqli->prepare($sql_usuario);
    $stmt_usuario->bind_param("i", $id_estudiante);
    $stmt_usuario->execute();
    $datos_pdf['usuario'] = $stmt_usuario->get_result()->fetch_assoc();
    $stmt_usuario->close();

    $sql_cursos = "SELECT pe.codigo, pe.materia, CASE WHEN pe.precio > 0 THEN pe.precio ELSE pcc.precio END AS precio_final, ca.periodo, p.nombre_programa, p.costo_matricula FROM matriculas m JOIN cursos_activos ca ON m.id_curso_activo = ca.id_curso_activo JOIN plan_estudios pe ON ca.id_plan = pe.id_plan JOIN programas p ON pe.id_programa = p.id_programa LEFT JOIN precios_cursos_conesup pcc ON p.categoria = pcc.nivel WHERE m.id_estudiante = ? AND ca.periodo = ?";
    $stmt_cursos = $mysqli->prepare($sql_cursos);
    $stmt_cursos->bind_param("is", $id_estudiante, $periodo);
    $stmt_cursos->execute();
    $result_cursos = $stmt_cursos->get_result();
    $cursos = [];
    while ($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
    $datos_pdf['cursos'] = $cursos;
    $stmt_cursos->close();

    // 5. Generar el PDF con FPDF
    class PDF extends FPDF {
        function Header() {
            $this->Image('../imgs/logo_unela_color.png', 10, 6, 40);
            $this->SetFont('Arial','B',15); $this->Cell(80); $this->Cell(30,10,utf8_decode('BOLETA DE PAGO'),0,0,'C'); $this->Ln(20);
        }
        function Footer() {
            $this->SetY(-15); $this->SetFont('Arial','I',8); $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages(); $pdf->AddPage(); $pdf->SetFont('Arial','',12);
    $pdf->SetFont('Arial','B',12); $pdf->Cell(0, 10, 'Boleta No: ' . $numero_boleta_formateado, 0, 1, 'R');
    $pdf->SetFont('Arial','',10); $pdf->Cell(0, 5, 'Fecha: ' . date('d/m/Y'), 0, 1, 'R'); $pdf->Ln(10);
    $costo_matricula_pdf = !empty($datos_pdf['cursos']) ? floatval($datos_pdf['cursos'][0]['costo_matricula']) : 0;
    $nombre_programa_pdf = !empty($datos_pdf['cursos']) ? $datos_pdf['cursos'][0]['nombre_programa'] : 'N/A';
    $pdf->SetFont('Arial','B',11); $pdf->Cell(0, 7, 'Datos del Estudiante', 0, 1);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0, 6, 'Nombre: ' . utf8_decode($datos_pdf['usuario']['nombre'] . ' ' . $datos_pdf['usuario']['apellidos']), 0, 1);
    $pdf->Cell(0, 6, utf8_decode('Cédula: ') . $datos_pdf['usuario']['cedula'], 0, 1);
    $pdf->Cell(0, 6, 'Email: ' . $datos_pdf['usuario']['email'], 0, 1);
    $pdf->Cell(0, 6, 'Carrera: ' . utf8_decode($nombre_programa_pdf), 0, 1);
    $pdf->Cell(0, 6, 'Cuatrimestre: ' . utf8_decode($periodo), 0, 1); $pdf->Ln(10);
    $pdf->SetFont('Arial','B',10); $pdf->Cell(30, 7, utf8_decode('Código'), 1); $pdf->Cell(95, 7, 'Materia / Concepto', 1); $pdf->Cell(35, 7, 'Periodo', 1); $pdf->Cell(30, 7, 'Monto', 1, 1, 'R');
    $pdf->SetFont('Arial','',10);
    $total_cursos = 0;
    foreach ($datos_pdf['cursos'] as $curso) {
        $precio = floatval($curso['precio_final']);
        $pdf->Cell(30, 7, $curso['codigo'], 1); $pdf->Cell(95, 7, utf8_decode($curso['materia']), 1); $pdf->Cell(35, 7, utf8_decode($curso['periodo']), 1); $pdf->Cell(30, 7, number_format($precio, 2), 1, 1, 'R');
        $total_cursos += $precio;
    }
    $pdf->Cell(160, 8, utf8_decode('Matrícula'), 1, 0, 'R'); $pdf->Cell(30, 8, number_format($costo_matricula_pdf, 2), 1, 1, 'R');
    $subtotal_pdf = $total_cursos + $costo_matricula_pdf;
    $pdf->SetFont('Arial','B',10); $pdf->Cell(160, 8, 'Sub-Total', 1, 0, 'R'); $pdf->Cell(30, 8, number_format($subtotal_pdf, 2), 1, 1, 'R');
    $pdf->SetFont('Arial','',10);
    $descuento_pdf = floatval($_POST['descuento'] ?? 0);
    $pdf->Cell(160, 8, 'Descuento', 1, 0, 'R'); $pdf->Cell(30, 8, number_format($descuento_pdf, 2), 1, 1, 'R');
    $total_general_pdf = $subtotal_pdf - $descuento_pdf;
    $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell(160, 8, 'TOTAL GENERAL', 1, 0, 'R', true); $pdf->Cell(30, 8, number_format($total_general_pdf, 2), 1, 1, 'R', true);
    $pdf->SetFont('Arial','',10);
    $mensualidad_pdf = $total_cursos / 4;
    $pdf->Cell(160, 8, 'Mensualidad (4 pagos de)', 1, 0, 'R'); $pdf->Cell(30, 8, number_format($mensualidad_pdf, 2), 1, 1, 'R'); $pdf->Ln(10);
    if (!empty($signature_path_full) && file_exists($signature_path_full)) {
        $pdf->SetFont('Arial','B',11); $pdf->Cell(0, 7, 'Firma de Registro', 0, 1);
        $pdf->Image($signature_path_full, $pdf->GetX(), $pdf->GetY(), 80, 40);
        $pdf->Ln(45);
    }
    $pdf_upload_dir = __DIR__ . '/../uploads/boletas_generadas/';
    if (!is_dir($pdf_upload_dir)) mkdir($pdf_upload_dir, 0777, true);
    $pdf_filename = 'boleta_' . str_replace('-', '_', strtolower($numero_boleta_formateado)) . '.pdf';
    $pdf_path_full = $pdf_upload_dir . $pdf_filename;
    $pdf->Output('F', $pdf_path_full);
    $pdf_path_db = 'uploads/boletas_generadas/' . $pdf_filename;
    $sql_update_pdf = "UPDATE boletas SET ruta_pdf = ? WHERE id = ?";
    if ($stmt_update_pdf = $mysqli->prepare($sql_update_pdf)) {
        $stmt_update_pdf->bind_param("si", $pdf_path_db, $id_boleta);
        $stmt_update_pdf->execute();
        $stmt_update_pdf->close();
    }

    $response['success'] = true;
    $response['message'] = 'Boleta generada y guardada con éxito.';
    $response['pdf_url'] = $pdf_path_db;

} catch (Throwable $e) {
    log_error_boleta($e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    $response['message'] = 'Ocurrió un error en el servidor. Por favor, revise el archivo de registro `boleta/debug_guardar_boleta.log`.';
}

if (isset($mysqli) && $mysqli->ping()) {
    $mysqli->close();
}

echo json_encode($response);
?>
