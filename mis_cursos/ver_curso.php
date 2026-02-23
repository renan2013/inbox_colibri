<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";
require_once "../includes/email_sender.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    header("location: index.php");
    exit;
}

$id_curso = $_GET['id'];
$id_profesor_sesion = $_SESSION['id'];
$isAdmin = has_permission($mysqli, 'gestionar_usuarios');
$message = "";
$error = "";

// 1. Procesar Envío Masivo de Notas / Guardado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Guardar Notas por Rubros
    if (isset($_POST['guardar_notas_rubros'])) {
        // --- INICIO LOGGING ---
        $log_file = 'log_notas.txt';
        $log_message = date('[Y-m-d H:i:s]') . " - User: {$_SESSION['id']} - Course: $id_curso\n";
        $log_message .= "POST data: " . print_r($_POST['notas_parciales'] ?? [], true) . "\n---\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        // --- FIN LOGGING ---

        $notas_parciales_post = $_POST['notas_parciales'] ?? [];
        
        foreach ($notas_parciales_post as $id_matricula => $rubros_alumno) {
            $nota_final_calculada = 0;
            
            foreach ($rubros_alumno as $id_rubro => $valor) {
                // Guardar/Actualizar nota parcial
                if ($valor !== '') {
                    // Verificar si existe nota
                    $check = $mysqli->query("SELECT id_nota_rubro FROM notas_rubros WHERE id_matricula = $id_matricula AND id_rubro = $id_rubro");
                    if($check->num_rows > 0) {
                        $stmt_up = $mysqli->prepare("UPDATE notas_rubros SET calificacion_obtenida = ? WHERE id_matricula = ? AND id_rubro = ?");
                        $stmt_up->bind_param("dii", $valor, $id_matricula, $id_rubro);
                        $stmt_up->execute();
                    } else {
                        $stmt_in = $mysqli->prepare("INSERT INTO notas_rubros (id_matricula, id_rubro, calificacion_obtenida) VALUES (?, ?, ?)");
                        $stmt_in->bind_param("idi", $id_matricula, $id_rubro, $valor);
                        $stmt_in->execute();
                    }
                    
                    // Calcular contribución al promedio
                    // Necesitamos el porcentaje del rubro
                    $res_p = $mysqli->query("SELECT porcentaje FROM silabo_evaluacion WHERE id_evaluacion = $id_rubro");
                    if($r = $res_p->fetch_assoc()) {
                        $nota_final_calculada += ($valor * ($r['porcentaje'] / 100));
                    }
                }
            }
            
            // Actualizar Nota Final en tabla matriculas
            $stmt_final = $mysqli->prepare("UPDATE matriculas SET calificacion = ? WHERE id_matricula = ?");
            $stmt_final->bind_param("di", $nota_final_calculada, $id_matricula);
            $stmt_final->execute();
        }
        // Redirigir para mostrar el Toast
        header("Location: ver_curso.php?id=$id_curso&msg=saved");
        exit;
    }
}

// 2. Obtener Info del Curso
$sql_curso = "SELECT ca.id_curso_activo, ca.id_profesor, ca.periodo, ca.fecha_inicio, ca.fecha_final, pe.id_plan, pe.materia, pe.codigo, p.nombre_programa 
              FROM cursos_activos ca
              JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
              JOIN programas p ON pe.id_programa = p.id_programa
              WHERE ca.id_curso_activo = ?";

if (!$isAdmin) {
    $sql_curso .= " AND ca.id_profesor = ?";
}

$stmt = $mysqli->prepare($sql_curso);

if ($isAdmin) {
    $stmt->bind_param("i", $id_curso);
} else {
    $stmt->bind_param("ii", $id_curso, $id_profesor_sesion);
}

$stmt->execute();
$res_curso = $stmt->get_result();

if($res_curso->num_rows === 0){
    // Usar una página de error más amigable en el futuro
    require_once '../includes/header.php';
    require_once '../includes/navbar.php';
    echo '<div class="container mt-4 alert alert-danger">Curso no encontrado o no tienes permiso para verlo.</div>';
    require_once '../includes/footer.php';
    exit;
}
$curso = $res_curso->fetch_assoc();
$id_plan = $curso['id_plan'];
$id_profesor_del_curso = $curso['id_profesor']; // El profesor real del curso

// 3. Obtener Rubros del Sílabo (basado solo en el plan de estudio)
$rubros = [];
$sql_rubros = "SELECT re.id_evaluacion as id_rubro, re.rubro, re.porcentaje 
               FROM silabo_evaluacion re 
               JOIN silabos s ON re.id_silabo = s.id_silabo 
               WHERE s.id_plan = ?
               ORDER BY re.id_evaluacion ASC";
$stmt_r = $mysqli->prepare($sql_rubros);
$stmt_r->bind_param("i", $id_plan);
$stmt_r->execute();
$res_rubros = $stmt_r->get_result();
while($r = $res_rubros->fetch_assoc()) $rubros[] = $r;
$stmt_r->close();

// 4. Obtener Estudiantes
$sql_alum = "SELECT u.id as id_estudiante, u.nombre, u.email, u.cedula, m.id_matricula, m.calificacion as nota_final_oficial, m.email_enviado, m.fecha_envio_email 
             FROM matriculas m
             JOIN usuarios u ON m.id_estudiante = u.id
             WHERE m.id_curso_activo = ?
             ORDER BY u.nombre ASC";
$stmt_alum = $mysqli->prepare($sql_alum);
$stmt_alum->bind_param("i", $id_curso);
$stmt_alum->execute();
$res_alum = $stmt_alum->get_result();

// 5. Cargar notas parciales
$notas_parciales = [];
if (!empty($rubros)) {
    $sql_np = "SELECT nr.id_matricula, nr.id_rubro, nr.calificacion_obtenida 
               FROM notas_rubros nr 
               JOIN matriculas m ON nr.id_matricula = m.id_matricula 
               WHERE m.id_curso_activo = ?";
    $stmt_np = $mysqli->prepare($sql_np);
    $stmt_np->bind_param("i", $id_curso);
    $stmt_np->execute();
    $res_np = $stmt_np->get_result();
    while($np = $res_np->fetch_assoc()){
        $notas_parciales[$np['id_matricula']][$np['id_rubro']] = $np['calificacion_obtenida'];
    }
    $stmt_np->close();
}

$page_title = 'Curso: ' . $curso['materia'];
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>
<!-- Version: <?php echo date('Y-m-d H:i:s'); ?> -->

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php">Mis Cursos</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($curso['materia']); ?></li>
        </ol>
    </nav>

    <?php 
    // Mostrar Toast de éxito si se guardaron las notas
    if (isset($_GET['msg']) && $_GET['msg'] == 'saved') {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Guardado',
                    text: 'Calificaciones guardadas correctamente.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        </script>";
    }
    // Mostrar errores si existen (estos sí pueden ser estáticos para que el usuario los lea bien)
    if($error): ?> 
        <div class="alert alert-danger"><?php echo $error; ?></div> 
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="text-primary"><?php echo htmlspecialchars($curso['materia']); ?></h2>
            <p class="text-muted">
                <i class="bi bi-calendar3"></i> <?php echo htmlspecialchars($curso['periodo']); ?> 
                (<?php echo date("d/m/Y", strtotime($curso['fecha_inicio'])); ?> - <?php echo date("d/m/Y", strtotime($curso['fecha_final'])); ?>)
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="gestionar_silabo.php?id_plan=<?php echo $id_plan; ?>&id_curso=<?php echo $id_curso; ?>" class="btn btn-outline-primary me-2">
                <i class="bi bi-journal-text"></i> Gestionar Sílabo
            </a>
            <button type="button" id="btnActaCalificaciones" class="btn btn-danger" disabled title="Completa todas las notas antes de generar el acta">
                <i class="bi bi-file-earmark-pdf"></i> Acta de Calificaciones
            </button>
        </div>
    </div>

    <?php if(empty($rubros)): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> <strong>Atención:</strong> No se han definido rubros de evaluación en el Sílabo. 
            <a href="gestionar_silabo.php?id_plan=<?php echo $id_plan; ?>&id_curso=<?php echo $id_curso; ?>" class="alert-link">Configurar Evaluación ahora</a> para poder calificar detalladamente.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-people"></i> Listado de Alumnos y Notas</h5>
        </div>
        <div class="card-body p-0">
            <form action="" method="POST" id="formNotas">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-bordered">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th style="width: 50px;">Nro.</th>
                                <th class="text-start ps-3" style="min-width: 125px;">Estudiante</th>
                                <th style="width: 90px;">Cédula</th>
                                <?php if(!empty($rubros)): ?>
                                    <?php foreach($rubros as $rubro): ?>
                                        <th style="width: 80px; font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($rubro['rubro']); ?>
                                            <br><span class="badge bg-secondary" style="font-size: 0.7rem;"><?php echo $rubro['porcentaje']; ?>%</span>
                                        </th>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <th>Nota Final (Manual)</th>
                                <?php endif; ?>
                                <th class="bg-light" style="width: 100px;">Total</th>
                                <th style="width: 100px;">Condición</th>
                                <th style="width: 80px;">Estado</th>
                                <th style="width: 120px;">Fecha de Envío</th>
                                <th style="width: 120px;">Enviar Nota</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $contador = 1; while($alum = $res_alum->fetch_assoc()): 
                                $id_mat = $alum['id_matricula'];
                                $nota_final_calc = 0;

                                // Lógica para deshabilitar el botón
                                $todas_notas_completas = true;
                                if (!empty($rubros)) {
                                    foreach($rubros as $rubro) {
                                        $id_r_check = $rubro['id_rubro'];
                                        if (!isset($notas_parciales[$id_mat][$id_r_check]) || $notas_parciales[$id_mat][$id_r_check] === '') {
                                            $todas_notas_completas = false;
                                            break;
                                        }
                                    }
                                } elseif (empty($rubros)) {
                                    $todas_notas_completas = false; 
                                }
                                $disabled_attr = !$todas_notas_completas ? 'disabled' : '';
                                $disabled_title = !$todas_notas_completas ? 'Completa todas las notas de los rubros para poder enviar.' : 'Enviar nota por correo';
                            ?>
                                <tr>
                                    <td><?php echo $contador++; ?></td>
                                    <td class="ps-3">
                                        <div class="fw-bold"><?php echo htmlspecialchars($alum['nombre']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($alum['email']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($alum['cedula'] ?? ''); ?></td>
                                    
                                    <?php if(!empty($rubros)): ?>
                                        <?php foreach($rubros as $rubro): 
                                            $id_r = $rubro['id_rubro'];
                                            $val = $notas_parciales[$id_mat][$id_r] ?? '';
                                            if($val !== '') $nota_final_calc += ($val * ($rubro['porcentaje']/100));
                                        ?>
                                            <td class="p-0">
                                                <input type="number" step="0.01" min="0" max="100" 
                                                       name="notas_parciales[<?php echo $id_mat; ?>][<?php echo $id_r; ?>]" 
                                                       class="form-control form-control-sm text-center input-nota border-0" 
                                                       style="height: 35px; border-radius: 0;"
                                                       data-porcentaje="<?php echo $rubro['porcentaje']; ?>"
                                                       data-matricula="<?php echo $id_mat; ?>"
                                                       data-rubro="<?php echo $id_r; ?>"
                                                       value="<?php echo htmlspecialchars($val); ?>">
                                            </td>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Fallback manual si no hay rubros -->
                                        <td>
                                            <input type="number" name="notas[<?php echo $alum['id_estudiante']; ?>]" class="form-control" value="<?php echo $alum['nota_final_oficial']; ?>">
                                        </td>
                                    <?php endif; ?>

                                    <!-- Columna Total Calculado -->
                                    <td class="text-center fw-bold fs-5 bg-light">
                                        <span id="total_<?php echo $id_mat; ?>" class="<?php echo ($nota_final_calc >= 70) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($nota_final_calc, 2); ?>
                                        </span>
                                    </td>

                                    <!-- Nueva Columna de Condición -->
                                    <td class="text-center">
                                        <span id="condicion_<?php echo $id_mat; ?>" class="badge <?php echo ($nota_final_calc >= 70) ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ($nota_final_calc >= 70) ? 'Aprobado' : 'Reprobado'; ?>
                                        </span>
                                    </td>

                                    <!-- Nueva Columna de Estado -->
                                    <td class="text-center">
                                        <?php if($alum['email_enviado']): ?>
                                            <i class="bi bi-check-circle-fill text-success" title="Enviado"></i>
                                        <?php else: ?>
                                            <i class="bi bi-circle text-muted" title="Pendiente de envío"></i>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Nueva Columna de Fecha de Envío -->
                                    <td class="text-center">
                                        <?php echo $alum['email_enviado'] ? date("d/m/y H:i", strtotime($alum['fecha_envio_email'])) : 'N/A'; ?>
                                    </td>

                                    <!-- Columna de Acciones (solo botón) -->
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="enviarNotaIndividual(<?php echo $id_mat; ?>, '<?php echo addslashes($alum['nombre']); ?>', '<?php echo $alum['email']; ?>')" 
                                                <?php echo $disabled_attr; ?> title="<?php echo $disabled_title; ?>">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </td>
                                </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            </form>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
    <button type="submit" id="btnGuardarTodasNotas" name="guardar_notas_rubros" form="formNotas" class="btn btn-primary" disabled>
        <i class="bi bi-save"></i> Guardar Todas las Notas
    </button>
</div>
</div>



<script>
function checkIfAllNotesAreComplete() {
    let allFormNotesComplete = true;
    const inputs = document.querySelectorAll('.input-nota');
    if (inputs.length === 0) { // Si no hay inputs, no hay notas que completar
        allFormNotesComplete = false;
    } else {
        inputs.forEach(input => {
            if (input.value.trim() === '' || isNaN(parseFloat(input.value))) {
                allFormNotesComplete = false;
            }
        });
    }
    
    const btnActa = document.getElementById('btnActaCalificaciones');
    if (btnActa) {
        btnActa.disabled = !allFormNotesComplete;
        btnActa.title = !allFormNotesComplete ? 'Completa todas las notas de todos los alumnos para generar el acta.' : 'Generar acta de calificaciones';
    }

    const btnGuardarTodas = document.getElementById('btnGuardarTodasNotas');
    if (btnGuardarTodas) {
        btnGuardarTodas.disabled = !allFormNotesComplete;
    }
}

// Cálculo en tiempo real para cada fila
document.querySelectorAll('.input-nota').forEach(input => {
    input.addEventListener('input', function() {
        const idMatricula = this.dataset.matricula;
        let total = 0;
        let todasCompletasFila = true;
        
        document.querySelectorAll(`.input-nota[data-matricula="${idMatricula}"]`).forEach(item => {
            const val = parseFloat(item.value);
            if (isNaN(val) || item.value.trim() === '') {
                todasCompletasFila = false;
            }
            const porc = parseFloat(item.dataset.porcentaje) || 0;
            total += ((val || 0) * (porc / 100));
        });

        const displayTotal = document.getElementById(`total_${idMatricula}`);
        displayTotal.innerText = total.toFixed(2);
        
        const displayCondicion = document.getElementById(`condicion_${idMatricula}`);
        if(total >= 70) {
            displayTotal.classList.remove('text-danger');
            displayTotal.classList.add('text-success');
            displayCondicion.innerText = 'Aprobado';
            displayCondicion.classList.remove('bg-danger');
            displayCondicion.classList.add('bg-success');
        } else {
            displayTotal.classList.remove('text-success');
            displayTotal.classList.add('text-danger');
            displayCondicion.innerText = 'Reprobado';
            displayCondicion.classList.remove('bg-success');
            displayCondicion.classList.add('bg-danger');
        }
        
        const sendButton = document.querySelector(`button[onclick*="enviarNotaIndividual(${idMatricula},"]`);
        if (sendButton) {
            sendButton.disabled = !todasCompletasFila;
            sendButton.title = !todasCompletasFila ? 'Completa todas las notas de los rubros para poder enviar.' : 'Enviar nota por correo';
        }
        
        checkIfAllNotesAreComplete();
    });
});

function enviarNotaIndividual(idMatricula, nombre, email) {
    Swal.fire({
        title: '¿Enviar nota?',
        text: `Se enviará la calificación actual al alumno ${nombre} (${email}).`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            let formData = new FormData();
            formData.append('id_matricula', idMatricula);
            
            return fetch('enviar_nota_individual.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor.');
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message);
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Error: ${error}`);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Enviado',
                text: 'La nota ha sido enviada con éxito.',
                icon: 'success'
            }).then(() => {
                window.location.reload(); 
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const btnActa = document.getElementById('btnActaCalificaciones');
    if (btnActa) {
        btnActa.addEventListener('click', function() {
            window.open('generar_acta.php?id=<?php echo $id_curso; ?>', '_blank');
        });
    }
    checkIfAllNotesAreComplete();
});
</script>

<?php require_once '../includes/footer.php'; ?>