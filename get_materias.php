<?php
header('Content-Type: application/json');

require_once 'includes/db_connect.php';

$id_programa = isset($_GET['id_programa']) ? (int)$_GET['id_programa'] : 0;

if ($id_programa === 0) {
    echo json_encode([]);
    exit;
}

$materias = [];
$sql = "SELECT id_plan, materia, codigo FROM plan_estudios WHERE id_programa = ? ORDER BY materia ASC";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id_programa);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $materias[] = $row;
        }
    }
    $stmt->close();
}

$mysqli->close();

echo json_encode($materias);
