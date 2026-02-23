<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de añadir plan de estudios
// if(!has_permission('anadir_plan_estudios')){
//     $_SESSION['error'] = "No tienes permiso para añadir planes de estudio.";
//     header("location: ../dashboard.php");
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_programa = trim($_POST['id_programa']);
    $id_precio_curso = trim($_POST['id_precio_curso']); // Nuevo campo
    $cuatrimestre = trim($_POST['cuatrimestre']);
    $codigo = trim($_POST['codigo']);
    $materia = trim($_POST['materia']);
    $creditos = trim($_POST['creditos']);
    $requisitos = trim($_POST['requisitos']);

    if (empty($id_programa) || empty($id_precio_curso) || empty($cuatrimestre) || empty($codigo) || empty($materia) || empty($creditos)) {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
        header("location: anadir_plan_estudios.php");
        exit;
    }

    // Obtener el precio desde la tabla precios_cursos_conesup
    $precio = 0;
    $sql_precio = "SELECT precio FROM precios_cursos_conesup WHERE id = ?";
    if($stmt_precio = $mysqli->prepare($sql_precio)) {
        $stmt_precio->bind_param("i", $id_precio_curso);
        $stmt_precio->execute();
        $stmt_precio->store_result();
        if($stmt_precio->num_rows == 1){
            $stmt_precio->bind_result($precio);
            $stmt_precio->fetch();
        } else {
            $_SESSION['error'] = "El nivel académico seleccionado no es válido.";
            header("location: anadir_plan_estudios.php");
            exit;
        }
        $stmt_precio->close();
    }


    // Insertar el nuevo plan de estudios con el precio
    $sql = "INSERT INTO plan_estudios (id_programa, cuatrimestre, codigo, materia, creditos, requisitos, precio) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("isssisd", $param_id_programa, $param_cuatrimestre, $param_codigo, $param_materia, $param_creditos, $param_requisitos, $param_precio);
        
        $param_id_programa = $id_programa;
        $param_cuatrimestre = $cuatrimestre;
        $param_codigo = $codigo;
        $param_materia = $materia;
        $param_creditos = $creditos;
        $param_requisitos = $requisitos;
        $param_precio = $precio;
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Plan de estudios añadido exitosamente!";
            header("location: anadir_plan_estudios.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al añadir el plan de estudios. Por favor, inténtalo de nuevo más tarde." . $stmt->error;
            header("location: anadir_plan_estudios.php");
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: anadir_plan_estudios.php");
    exit;
}
?>