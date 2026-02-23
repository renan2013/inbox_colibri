<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Error desconocido.', 'periodos' => []];

if (!isset($_GET['id_estudiante']) || empty($_GET['id_estudiante'])) {
    $response['message'] = 'No se proporcionó un ID de estudiante.';
    echo json_encode($response);
    exit;
}

$id_estudiante = intval($_GET['id_estudiante']);

$sql = "
    SELECT DISTINCT ca.periodo
    FROM matriculas m
    JOIN cursos_activos ca ON m.id_curso_activo = ca.id_curso_activo
    WHERE m.id_estudiante = ?
    ORDER BY ca.periodo DESC
";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id_estudiante);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $periodos_raw = [];
    while ($row = $result->fetch_assoc()) {
        $periodos_raw[] = $row['periodo'];
    }
    
    $anios = [];
    $cuatrimestres_tipos = [];

    foreach ($periodos_raw as $periodo_str) {
        // Ejemplo: "I Cuatrimestre 2026"
        if (preg_match('/^(.*?)\s(\d{4})$/', $periodo_str, $matches)) {
            $tipo = trim($matches[1]);
            $anio = trim($matches[2]);
            $anios[$anio] = $anio; // Usar el año como clave para asegurar unicidad
            $cuatrimestres_tipos[$tipo] = $tipo; // Usar el tipo como clave para asegurar unicidad
        }
    }

    // Convertir a arrays planos para el frontend
    $response['success'] = true;
    $response['anios'] = array_values($anios);
    $response['cuatrimestres_tipos'] = array_values($cuatrimestres_tipos);
    $response['message'] = 'Períodos obtenidos con éxito.';
    
    $stmt->close();
} else {
    $response['message'] = 'Error al preparar la consulta de períodos: ' . $mysqli->error;
}

echo json_encode($response);
$mysqli->close();
?>
