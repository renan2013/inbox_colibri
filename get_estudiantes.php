<?php
header('Content-Type: application/json');

session_start(); // Necesario para has_permission si se usa
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php'; // Para usar has_permission si es necesario

// Opcional: Proteger este endpoint si solo usuarios con ciertos permisos pueden buscar estudiantes
// if (!isset($_SESSION['id']) || !has_permission($mysqli, 'gestionar_usuarios')) {
//     echo json_encode(['results' => [], 'error' => 'Acceso denegado']);
//     exit;
// }

$search = isset($_GET['q']) ? $_GET['q'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Número de resultados por página
$offset = ($page - 1) * $limit;

$users_data = [];

// Consulta para obtener los usuarios
$sql = "SELECT id, nombre, apellidos, email, cedula FROM usuarios 
        WHERE nombre LIKE ? OR apellidos LIKE ? OR email LIKE ? OR cedula LIKE ?
        ORDER BY nombre ASC LIMIT ? OFFSET ?";

if ($stmt = $mysqli->prepare($sql)) {
    $search_param = '%' . $search . '%';
    // Repetimos el search_param 4 veces para los 4 campos de la cláusula WHERE
    $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $users_data[] = $row; // Almacenamos el row completo
    }
    $stmt->close();
}

// Consulta para contar el total de usuarios para la paginación
$sql_count = "SELECT COUNT(*) FROM usuarios 
              WHERE nombre LIKE ? OR apellidos LIKE ? OR email LIKE ? OR cedula LIKE ?";

$total_count = 0;
if ($stmt_count = $mysqli->prepare($sql_count)) {
    $search_param = '%' . $search . '%';
    $stmt_count->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt_count->execute();
    $stmt_count->bind_result($total_count);
    $stmt_count->fetch();
    $stmt_count->close();
}

// Para Select2, necesitamos el formato { "results": [...], "pagination": { "more": true/false } }
// Los resultados se devuelven tal cual para que el frontend los formatee
// pero para la compatibilidad con el 'processResults' anterior del select2 directo,
// también se agrega una propiedad 'text' aquí.
$results_for_select2 = [];
foreach ($users_data as $user) {
    $results_for_select2[] = [
        'id' => $user['id'],
        'nombre' => $user['nombre'],
        'apellidos' => $user['apellidos'],
        'email' => $user['email'],
        'text' => $user['nombre'] . ' ' . $user['apellidos'] . ' (' . $user['email'] . ')' // Formato para mostrar en Select2 directamente
    ];
}

$response = [
    'results' => $results_for_select2,
    'pagination' => ['more' => ($offset + count($users_data)) < $total_count]
];

$mysqli->close();

echo json_encode($response);
