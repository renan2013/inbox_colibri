<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Error desconocido.'];

if (!isset($_GET['id_estudiante']) || empty($_GET['id_estudiante']) || !isset($_GET['periodo']) || empty($_GET['periodo'])) {
    $response['message'] = 'No se proporcionó un ID de estudiante o un período.';
    echo json_encode($response);
    exit;
}

$id_estudiante = intval($_GET['id_estudiante']);
$periodo = trim($_GET['periodo']);
$data = [];

// 1. Obtener datos del usuario
$sql_usuario = "SELECT id, nombre, apellidos, cedula, email FROM usuarios WHERE id = ?";
if ($stmt_usuario = $mysqli->prepare($sql_usuario)) {
    $stmt_usuario->bind_param("i", $id_estudiante);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    if ($result_usuario->num_rows > 0) {
        $data['usuario'] = $result_usuario->fetch_assoc();
    } else {
        $response['message'] = 'Estudiante no encontrado.';
        echo json_encode($response);
        exit;
    }
    $stmt_usuario->close();
} else {
    $response['message'] = 'Error al preparar la consulta de usuario: ' . $mysqli->error;
    echo json_encode($response);
    exit;
}


// 2. Obtener cursos matriculados y sus precios
$sql_cursos = "
    SELECT 
        pe.codigo,
        pe.materia,
        CASE 
            WHEN pe.precio > 0 THEN pe.precio
            ELSE pcc.precio
        END AS precio_final,
        ca.periodo,
        p.nombre_programa,
        p.costo_matricula
    FROM matriculas m
    JOIN cursos_activos ca ON m.id_curso_activo = ca.id_curso_activo
    JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
    JOIN programas p ON pe.id_programa = p.id_programa
    LEFT JOIN precios_cursos_conesup pcc ON p.categoria = pcc.nivel
    WHERE m.id_estudiante = ? AND ca.periodo = ?
";

$cursos = [];
if ($stmt_cursos = $mysqli->prepare($sql_cursos)) {
    $stmt_cursos->bind_param("is", $id_estudiante, $periodo);
    $stmt_cursos->execute();
    $result_cursos = $stmt_cursos->get_result();
    while ($row = $result_cursos->fetch_assoc()) {
        $cursos[] = $row;
    }
    $stmt_cursos->close();
    $data['cursos'] = $cursos;
} else {
    $response['message'] = 'Error al preparar la consulta de cursos: ' . $mysqli->error;
    echo json_encode($response);
    exit;
}

$response['success'] = true;
$response['data'] = $data;
$response['message'] = 'Datos obtenidos con éxito.';

echo json_encode($response);

$mysqli->close();
?>
