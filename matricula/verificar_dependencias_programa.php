<?php
session_start();
require_once '../includes/db_connect.php';

$response = ['has_dependencies' => false];

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_programa = trim($_GET['id']);

    // Verificar si hay planes de estudio asociados
    $sql_check = "SELECT id_plan FROM plan_estudios WHERE id_programa = ? LIMIT 1";
    
    if ($stmt_check = $mysqli->prepare($sql_check)) {
        $stmt_check->bind_param("i", $id_programa);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['has_dependencies'] = true;
        }
        
        $stmt_check->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
$mysqli->close();
?>
