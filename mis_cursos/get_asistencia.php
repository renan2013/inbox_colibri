<?php
session_start();
require_once "../includes/db_connect.php";

header('Content-Type: application/json');

if (isset($_GET['id_tarea']) && is_numeric($_GET['id_tarea'])) {
    $id_tarea = $_GET['id_tarea'];
    $asistencias = [];
    
    $sql = "SELECT id_estudiante, estado FROM asistencias WHERE id_tarea = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_tarea);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $asistencias[$row['id_estudiante']] = $row['estado'];
        }
        $stmt->close();
    }
    echo json_encode($asistencias);
} else {
    echo json_encode([]);
}
?>