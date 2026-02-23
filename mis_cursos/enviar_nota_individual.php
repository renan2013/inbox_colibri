<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/email_sender.php";

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_matricula'])) {
    $id_matricula = $_POST['id_matricula'];

    // Obtener datos necesarios
    $sql = "SELECT u.email, u.nombre, m.calificacion, m.id_curso_activo, ca.id_plan, pe.materia, pe.codigo 
            FROM matriculas m
            JOIN usuarios u ON m.id_estudiante = u.id
            JOIN cursos_activos ca ON m.id_curso_activo = ca.id_curso_activo
            JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
            WHERE m.id_matricula = ?";
            
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['calificacion'] === null) {
            echo json_encode(['success' => false, 'message' => 'El estudiante aún no tiene una nota registrada.']);
            exit;
        }

        $cursoNombre = $row['materia'] . " (" . $row['codigo'] . ")";
        
        // --- OBTENER DETALLE DE NOTAS ---
        $gradeDetails = [];
        $id_curso_activo = $row['id_curso_activo'];

        // 1. Obtener los rubros del sílabo para este curso
        $rubros_sql = "SELECT re.id_evaluacion as id_rubro, re.rubro, re.porcentaje 
                       FROM silabo_evaluacion re
                       JOIN silabos s ON re.id_silabo = s.id_silabo
                       WHERE s.id_plan = ?";
        $stmt_rubros = $mysqli->prepare($rubros_sql);
        $stmt_rubros->bind_param("i", $row['id_plan']);
        $stmt_rubros->execute();
        $result_rubros = $stmt_rubros->get_result();
        $rubros = $result_rubros->fetch_all(MYSQLI_ASSOC);
        $stmt_rubros->close();

        // 2. Obtener las notas parciales del alumno
        $notas_sql = "SELECT id_rubro, calificacion_obtenida FROM notas_rubros WHERE id_matricula = ?";
        $stmt_notas = $mysqli->prepare($notas_sql);
        $stmt_notas->bind_param("i", $id_matricula);
        $stmt_notas->execute();
        $result_notas = $stmt_notas->get_result();
        $notas_alumno = [];
        while($nota_row = $result_notas->fetch_assoc()) {
            $notas_alumno[$nota_row['id_rubro']] = $nota_row['calificacion_obtenida'];
        }
        $stmt_notas->close();

        // 3. Construir el array de detalles
        foreach ($rubros as $rubro) {
            $gradeDetails[] = [
                'rubro' => $rubro['rubro'],
                'porcentaje' => $rubro['porcentaje'],
                'calificacion' => $notas_alumno[$rubro['id_rubro']] ?? 'N/A'
            ];
        }
        // --- FIN DETALLE DE NOTAS ---

        if (sendGradeEmail($row['email'], $row['nombre'], $cursoNombre, $row['calificacion'], $gradeDetails)) {
            // Si el correo se envía, actualizamos la base de datos
            $update_stmt = $mysqli->prepare("UPDATE matriculas SET email_enviado = 1, fecha_envio_email = NOW() WHERE id_matricula = ?");
            $update_stmt->bind_param("i", $id_matricula);
            $update_stmt->execute();
            $update_stmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Correo enviado correctamente a ' . $row['email']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar el correo.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Matrícula no encontrada.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>