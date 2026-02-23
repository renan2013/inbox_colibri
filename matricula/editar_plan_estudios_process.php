<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de editar planes de estudio
// if(!has_permission('editar_planes_estudio')){
//     $_SESSION['error'] = "No tienes permiso para editar planes de estudio.";
//     header("location: ../dashboard.php");
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_plan = trim($_POST['id_plan']);
    $id_programa = trim($_POST['id_programa']);
    $cuatrimestre = trim($_POST['cuatrimestre']);
    $codigo = trim($_POST['codigo']);
    $materia = trim($_POST['materia']);
    $creditos = trim($_POST['creditos']);
    $requisitos = trim($_POST['requisitos']);

    if (empty($id_plan) || empty($id_programa) || empty($cuatrimestre) || empty($codigo) || empty($materia) || empty($creditos)) {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
        header("location: editar_plan_estudios.php?id=" . $id_plan);
        exit;
    }

    // Actualizar el plan de estudios
    $sql = "UPDATE plan_estudios SET id_programa = ?, cuatrimestre = ?, codigo = ?, materia = ?, creditos = ?, requisitos = ? WHERE id_plan = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isssisi", $param_id_programa, $param_cuatrimestre, $param_codigo, $param_materia, $param_creditos, $param_requisitos, $param_id_plan);
        
        $param_id_programa = $id_programa;
        $param_cuatrimestre = $cuatrimestre;
        $param_codigo = $codigo;
        $param_materia = $materia;
        $param_creditos = $creditos;
        $param_requisitos = $requisitos;
        $param_id_plan = $id_plan;
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Plan de estudios actualizado exitosamente!";
            header("location: ver_plan_estudios.php?id=" . $id_plan);
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al actualizar el plan de estudios. Por favor, inténtalo de nuevo más tarde." . $stmt->error;
            header("location: editar_plan_estudios.php?id=" . $id_plan);
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: lista_cursos.php");
    exit;
}
?>