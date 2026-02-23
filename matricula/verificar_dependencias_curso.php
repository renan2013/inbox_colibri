<?php
session_start();
require_once '../includes/db_connect.php';

$response = ['has_dependencies' => false, 'error' => ''];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_plan = $_GET['id'];

    try {
        // La tabla dependiente es `cursos_activos`, según el error de FK que reportaste.
        $sql_check = "SELECT id_curso_activo FROM cursos_activos WHERE id_plan = ? LIMIT 1";
        
        $stmt_check = $mysqli->prepare($sql_check);
        $stmt_check->bind_param("i", $id_plan);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['has_dependencies'] = true;
        }
        
        $stmt_check->close();

    } catch (Exception $e) {
        $response['error'] = 'Error de servidor al verificar dependencias.';
    }

} else {
    $response['error'] = 'ID de curso no válido.';
}

header('Content-Type: application/json');
echo json_encode($response);

if (isset($mysqli)) {
    $mysqli->close();
}
?>
