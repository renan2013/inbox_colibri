<?php
session_start();
require_once "../includes/db_connect.php";

if (!isset($_SESSION["loggedin"]) || !isset($_GET['id'])) {
    die("Acceso denegado.");
}

$id_curso = $_GET['id'];
$id_profesor = $_SESSION['id'];

// 1. Obtener el nombre del curso para saber qué borrar
$stmt = $mysqli->prepare("SELECT pe.materia FROM cursos_activos ca JOIN plan_estudios pe ON ca.id_plan = pe.id_plan WHERE ca.id_curso_activo = ?");
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$nom_curso = $stmt->get_result()->fetch_assoc()['materia'];
$stmt->close();

if ($nom_curso) {
    // 2. Borrar todas las tareas que tengan "Clase" y el nombre del curso, creadas por este profe
    $patron = "Clase%".$nom_curso."%";
    $sql_del = "DELETE FROM tareas WHERE id_creador = ? AND titulo LIKE ?";
    $stmt_del = $mysqli->prepare($sql_del);
    $stmt_del->bind_param("is", $id_profesor, $patron);
    $stmt_del->execute();
    $stmt_del->close();
}

// Redirigir de vuelta
header("Location: ver_curso.php?id=$id_curso&msg=limpieza_ok");
exit;
?>