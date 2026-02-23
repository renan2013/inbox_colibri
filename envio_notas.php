<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';
require_once 'includes/email_sender.php';

if (!isset($_SESSION['id']) || !has_permission($mysqli, 'enviar_notas')) {
    require_once 'includes/config.php';
    header("location: " . BASE_URL . "login.php");
    exit;
}

$page_title = 'Envío de Notas';
$user_id = $_SESSION['id'];
$user_name = $_SESSION['nombre'] ?? 'Usuario';

$selected_curso_activo_id = null;
$students_data = [];
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_notas'])) {
    $selected_curso_activo_id = $_POST['curso_activo_id'];
    $notas = $_POST['notas'];
    $comentarios = $_POST['comentarios'];
    $student_emails = $_POST['student_emails'];
    $student_names = $_POST['student_names'];

    $stmt_curso_details = $mysqli->prepare("SELECT ca.periodo, pe.materia AS nombre_curso, u_prof.nombre AS nombre_profesor, u_prof.email AS email_profesor FROM cursos_activos ca JOIN plan_estudios pe ON ca.id_plan = pe.id_plan JOIN usuarios u_prof ON ca.id_profesor = u_prof.id WHERE ca.id_curso_activo = ?");
    $stmt_curso_details->bind_param("i", $selected_curso_activo_id);
    $stmt_curso_details->execute();
    $result_curso_details = $stmt_curso_details->get_result();
    $curso_details = $result_curso_details->fetch_assoc();
    $stmt_curso_details->close();

    if ($curso_details) {
        $nombre_curso = $curso_details['nombre_curso'] . " (" . $curso_details['periodo'] . ")";
        $nombre_profesor = $curso_details['nombre_profesor'];
        $selected_students_ids = $_POST['selected_students'] ?? [];

        if (empty($selected_students_ids)) {
            $error = "Debe seleccionar al menos un estudiante para enviar notas.";
        } else {
            foreach ($selected_students_ids as $student_id) {
                $nota = $notas[$student_id] ?? null;
                $comentario = $comentarios[$student_id] ?? '';
                $student_email = $student_emails[$student_id] ?? '';
                $student_name = $student_names[$student_id] ?? '';
                $estado = ($nota >= 70) ? 'Aprobado' : 'Reprobado';

                if ($nota === null) {
                    $error .= "No se encontró calificación para el estudiante " . htmlspecialchars($student_name) . ".<br>";
                    continue;
                }

                $stmt_update_calificacion = $mysqli->prepare("UPDATE matriculas SET calificacion = ? WHERE id_estudiante = ? AND id_curso_activo = ?");
                $stmt_update_calificacion->bind_param("dii", $nota, $student_id, $selected_curso_activo_id);
                $stmt_update_calificacion->execute();
                $stmt_update_calificacion->close();

                $email_sent_status = 0;
                $fecha_envio = NULL;

                $subject = "Calificación de su curso: " . $nombre_curso;
                $body = "<html>...</html>"; // El cuerpo del email está omitido por brevedad
                
                if (sendEmail($student_email, $subject, $body)) {
                    $email_sent_status = 1;
                    $fecha_envio = date('Y-m-d H:i:s');
                    $message .= "Nota de " . htmlspecialchars($student_name) . " enviada con éxito.<br>";
                } else {
                    $error .= "Error al enviar nota a " . htmlspecialchars($student_name) . ".<br>";
                }

                $sql_update_email_status = "UPDATE matriculas SET email_enviado = ?, fecha_envio_email = ? WHERE id_estudiante = ? AND id_curso_activo = ?";
                if ($stmt_update_email_status = $mysqli->prepare($sql_update_email_status)) {
                    $stmt_update_email_status->bind_param("isii", $email_sent_status, $fecha_envio, $student_id, $selected_curso_activo_id);
                    $stmt_update_email_status->execute();
                    $stmt_update_email_status->close();
                }
            }
        }
    } else {
        $error = "No se pudieron obtener los detalles del curso.";
    }
}

if (($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['select_curso'])) || isset($_GET['curso_activo_id'])) {
    $selected_curso_activo_id = $_POST['curso_activo_id'] ?? $_GET['curso_activo_id'];
}


if ($selected_curso_activo_id) {
    $stmt_students = $mysqli->prepare("SELECT u.id AS student_id, u.nombre AS student_name, u.cedula, u.email AS student_email, m.calificacion, m.email_enviado, m.fecha_envio_email FROM matriculas m JOIN usuarios u ON m.id_estudiante = u.id WHERE m.id_curso_activo = ?");
    $stmt_students->bind_param("i", $selected_curso_activo_id);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
    while ($row = $result_students->fetch_assoc()) {
        $students_data[] = $row;
    }
    $stmt_students->close();
}

$cursos_activos = [];
$stmt_cursos = $mysqli->prepare("SELECT ca.id_curso_activo, pe.materia AS nombre_materia, u.nombre AS nombre_profesor, ca.periodo FROM cursos_activos ca JOIN plan_estudios pe ON ca.id_plan = pe.id_plan JOIN usuarios u ON ca.id_profesor = u.id ORDER BY ca.periodo DESC, pe.materia ASC");
$stmt_cursos->execute();
$result_cursos = $stmt_cursos->get_result();
while ($row = $result_cursos->fetch_assoc()) {
    $cursos_activos[] = $row;
}
$stmt_cursos->close();

require_once 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Envío de Notas a Estudiantes</h2>

    <?php if ($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>

    <form action="envio_notas.php" method="POST" class="mb-4">
        <div class="form-group">
            <label for="curso_activo_id">Seleccionar Curso:</label>
            <select class="form-control" id="curso_activo_id" name="curso_activo_id" onchange="this.form.submit()">
                <option value="">-- Seleccione un curso --</option>
                <?php foreach ($cursos_activos as $curso): ?>
                    <option value="<?php echo htmlspecialchars($curso['id_curso_activo']); ?>" <?php echo ($selected_curso_activo_id == $curso['id_curso_activo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($curso['nombre_materia'] . " - " . $curso['nombre_profesor'] . " (" . $curso['periodo'] . ")"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="hidden" name="select_curso" value="1">
    </form>

    <?php if ($selected_curso_activo_id && !empty($students_data)): ?>
        <form action="envio_notas.php" method="POST">
            <input type="hidden" name="curso_activo_id" value="<?php echo htmlspecialchars($selected_curso_activo_id); ?>">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select_all_students"></th>
                            <th>Nombre Estudiante</th><th>Cédula</th><th>Email</th><th>Calificación (0-100)</th><th>Comentario</th><th>Estado Envío</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students_data as $student): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_students[]" value="<?php echo $student['student_id']; ?>" class="student_checkbox"></td>
                                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['cedula']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_email']); ?></td>
                                <td><input type="number" class="form-control" name="notas[<?php echo $student['student_id']; ?>]" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($student['calificacion'] ?? ''); ?>"></td>
                                <td><textarea class="form-control" name="comentarios[<?php echo $student['student_id']; ?>]" rows="2"></textarea></td>
                                <td class="text-center">
                                    <?php if ($student['email_enviado']): ?>
                                        <i class="bi bi-check-circle-fill text-success fs-4" title="Correo enviado el <?php echo htmlspecialchars($student['fecha_envio_email']); ?>"></i>
                                        <br><small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($student['fecha_envio_email'])); ?></small>
                                    <?php else: ?>
                                        <i class="bi bi-x-circle-fill text-danger fs-4" title="Correo no enviado"></i>
                                    <?php endif; ?>
                                </td>
                                <input type="hidden" name="student_emails[<?php echo $student['student_id']; ?>]" value="<?php echo htmlspecialchars($student['student_email']); ?>">
                                <input type="hidden" name="student_names[<?php echo $student['student_id']; ?>]" value="<?php echo htmlspecialchars($student['student_name']); ?>">
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" name="submit_notas" class="btn btn-primary mt-3">Enviar Notas y Correos</button>
        </form>
    <?php elseif ($selected_curso_activo_id && empty($students_data)): ?>
        <div class="alert alert-info">No hay estudiantes matriculados en este curso.</div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#select_all_students').on('change', function() {
            $('.student_checkbox').prop('checked', $(this).prop('checked'));
        });
        $('.student_checkbox').on('change', function() {
            if (!$(this).prop('checked')) {
                $('#select_all_students').prop('checked', false);
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>