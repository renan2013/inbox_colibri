<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$query = $_GET['query'] ?? '';

if (strlen($query) < 3) {
    echo json_encode([]);
    exit;
}

$search_term = "%" . $query . "%";
$results = [];

// La búsqueda ahora incluye nombre, apellidos, email y cédula
$sql = "SELECT id, nombre, apellidos, email FROM usuarios WHERE nombre LIKE ? OR apellidos LIKE ? OR email LIKE ? OR cedula LIKE ?";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        // En un entorno de producción, sería mejor loguear este error que mostrarlo.
        http_response_code(500);
        echo json_encode(['error' => 'Error al ejecutar la consulta.']);
        exit;
    }
    
    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al preparar la consulta.']);
    exit;
}

$mysqli->close();

echo json_encode($results);
?>
