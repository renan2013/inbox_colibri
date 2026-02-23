<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página
if (!isset($_SESSION['id']) || !has_permission($mysqli, 'gestionar_usuarios')) {
    require_once 'includes/config.php';
    header("location: " . BASE_URL . "login.php?error=No tienes permiso para gestionar grupos");
    exit;
}

$page_title = 'Gestionar Grupos de Estudio';
$message = '';
$error = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// --- Lógica para procesar la creación de un nuevo grupo ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_grupo'])) {
    $id_plan = $_POST['id_plan'];
    $id_profesor = $_POST['id_profesor'];
    $periodo = trim($_POST['periodo']);
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_final = trim($_POST['fecha_final']);
    $id_estudiantes = $_POST['id_estudiantes'] ?? [];

    if (empty($id_plan) || empty($id_profesor) || empty($periodo) || empty($fecha_inicio) || empty($fecha_final)) {
        $error = "Materia, profesor, período, fecha de inicio y fecha de finalización son campos obligatorios.";
    } else {
        $sql_create_curso = "INSERT INTO cursos_activos (id_plan, id_profesor, periodo, fecha_inicio, fecha_final) VALUES (?, ?, ?, ?, ?)";
        if ($stmt_create_curso = $mysqli->prepare($sql_create_curso)) {
            $stmt_create_curso->bind_param("iisss", $id_plan, $id_profesor, $periodo, $fecha_inicio, $fecha_final);
            if ($stmt_create_curso->execute()) {
                $id_curso_activo_nuevo = $mysqli->insert_id;
                $message = "Grupo de estudio creado con éxito.";

                if (!empty($id_estudiantes)) {
                    $sql_matricula = "INSERT INTO matriculas (id_estudiante, id_curso_activo) VALUES (?, ?)";
                    if ($stmt_matricula = $mysqli->prepare($sql_matricula)) {
                        $estudiantes_matriculados = 0;
                        foreach ($id_estudiantes as $id_estudiante) {
                            $stmt_matricula->bind_param("ii", $id_estudiante, $id_curso_activo_nuevo);
                            if ($stmt_matricula->execute()) {
                                $estudiantes_matriculados++;
                            }
                        }
                        $message .= " Se matricularon " . $estudiantes_matriculados . " estudiantes.";
                        $stmt_matricula->close();
                    } else {
                        $error .= " Error al preparar la consulta de matrícula.";
                    }
                }
            } else {
                $error = "Error al crear el grupo de estudio: " . $stmt_create_curso->error;
            }
            $stmt_create_curso->close();
        } else {
            $error = "Error al preparar la consulta para crear el grupo: " . $mysqli->error;
        }
    }
}

// --- Obtener datos para los formularios y la lista ---
$programas = [];
$sql_programas = "SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa ASC";
$result_programas = $mysqli->query($sql_programas);
if ($result_programas) { while ($row = $result_programas->fetch_assoc()) $programas[] = $row; }

$profesores = [];
$sql_profesores = "SELECT u.id, u.nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE r.nombre = 'Profesor' ORDER BY u.nombre ASC";
$result_profesores = $mysqli->query($sql_profesores);
if ($result_profesores) { while ($row = $result_profesores->fetch_assoc()) $profesores[] = $row; }

$estudiantes = [];
$sql_estudiantes = "SELECT id, nombre, cedula FROM usuarios ORDER BY nombre ASC";
$result_estudiantes = $mysqli->query($sql_estudiantes);
if ($result_estudiantes) { while ($row = $result_estudiantes->fetch_assoc()) $estudiantes[] = $row; }

$grupos_existentes = [];
$sql_grupos = "SELECT ca.id_curso_activo, pe.materia, pe.codigo, u.nombre AS nombre_profesor, ca.periodo, ca.fecha_inicio, ca.fecha_final, (SELECT COUNT(*) FROM matriculas m WHERE m.id_curso_activo = ca.id_curso_activo) AS total_estudiantes FROM cursos_activos ca JOIN plan_estudios pe ON ca.id_plan = pe.id_plan JOIN usuarios u ON ca.id_profesor = u.id ORDER BY ca.periodo DESC, pe.materia ASC";
$result_grupos = $mysqli->query($sql_grupos);
if ($result_grupos) { while ($row = $result_grupos->fetch_assoc()) $grupos_existentes[] = $row; }

require_once 'includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    
    <?php if ($message): ?>
        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card mb-5">
        <div class="card-header"><h3>Crear Nuevo Grupo de Estudio</h3></div>
        <div class="card-body">
            <form action="gestionar_grupos.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_programa" class="form-label">Programa</label>
                        <select name="id_programa" id="id_programa" class="form-select" required>
                            <option value="">Seleccione un programa...</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?php echo $programa['id_programa']; ?>"><?php echo htmlspecialchars($programa['nombre_programa']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_plan" class="form-label">Materia</label>
                        <select name="id_plan" id="id_plan" class="form-select" required disabled>
                            <option value="">Seleccione un programa primero...</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="id_profesor" class="form-label">Profesor</label>
                        <select name="id_profesor" id="id_profesor" class="form-select" required>
                             <option value="">Seleccione un profesor...</option>
                            <?php foreach ($profesores as $profesor): ?>
                                <option value="<?php echo $profesor['id']; ?>"><?php echo htmlspecialchars($profesor['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="cuatrimestre_select" class="form-label">Cuatrimestre</label>
                        <select id="cuatrimestre_select" class="form-select">
                            <option value="I Cuatrimestre">I Cuatrimestre</option>
                            <option value="II Cuatrimestre">II Cuatrimestre</option>
                            <option value="III Cuatrimestre">III Cuatrimestre</option>
                            <option value="IV Cuatrimestre">IV Cuatrimestre</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ano_select" class="form-label">Año</label>
                        <select id="ano_select" class="form-select">
                            <?php 
                                $current_year = date('Y');
                                for ($i = -1; $i <= 3; $i++) {
                                    $year = $current_year + $i;
                                    echo "<option value='{$year}'" . ($year == $current_year ? ' selected' : '') . ">{$year}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="periodo" id="periodo">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_final" class="form-label">Fecha de Finalización</label>
                        <input type="date" name="fecha_final" id="fecha_final" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="id_estudiantes" class="form-label">Matricular Estudiantes (Opcional)</label>
                    <select name="id_estudiantes[]" id="id_estudiantes" class="form-select" multiple></select>
                </div>
                <button type="submit" name="crear_grupo" class="btn btn-primary">Crear Grupo</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Grupos de Estudio Existentes</h3></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Materia</th><th>Período</th><th>Profesor</th><th>Fecha Inicio</th><th>Fecha Final</th><th>Estudiantes</th><th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grupos_existentes)): ?>
                            <?php foreach ($grupos_existentes as $grupo): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($grupo['codigo'] . ' - ' . $grupo['materia']); ?></td>
                                    <td><?php echo htmlspecialchars($grupo['periodo']); ?></td>
                                    <td><?php echo htmlspecialchars($grupo['nombre_profesor']); ?></td>
                                    <td><?php echo htmlspecialchars($grupo['fecha_inicio']); ?></td>
                                    <td><?php echo htmlspecialchars($grupo['fecha_final']); ?></td>
                                    <td><?php echo $grupo['total_estudiantes']; ?></td>
                                    <td>
                                        <a href="mis_cursos/ver_curso.php?id=<?php echo $grupo['id_curso_activo']; ?>" class="btn btn-info btn-sm me-1"><i class="bi bi-eye"></i> Ver</a>
                                        <a href="editar_grupo.php?id=<?php echo $grupo['id_curso_activo']; ?>" class="btn btn-warning btn-sm me-1"><i class="bi bi-pencil"></i> Editar</a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmarEliminarGrupo(<?php echo $grupo['id_curso_activo']; ?>)"><i class="bi bi-trash"></i> Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">No hay grupos de estudio creados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#id_programa, #id_plan, #id_profesor, #cuatrimestre_select, #ano_select').select2({ theme: 'bootstrap-5' });
    
    // Lógica para combinar cuatrimestre y año en el campo 'periodo'
    function actualizarPeriodo() {
        const cuatrimestre = $('#cuatrimestre_select').val();
        const ano = $('#ano_select').val();
        $('#periodo').val(cuatrimestre + ' ' + ano);
    }

    // Actualizar al cambiar la selección
    $('#cuatrimestre_select, #ano_select').on('change', actualizarPeriodo);

    // Establecer valor inicial al cargar la página
    actualizarPeriodo();

    $('#id_estudiantes').select2({
        theme: 'bootstrap-5',
        placeholder: 'Busque estudiantes por nombre o cédula',
        closeOnSelect: false,
        ajax: {
            url: 'get_estudiantes.php',
            dataType: 'json',
            delay: 250,
            data: function (params) { return { q: params.term, page: params.page }; },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        }
    });

    $('#id_programa').on('change', function() {
        var idPrograma = $(this).val();
        var materiaSelect = $('#id_plan');
        materiaSelect.empty().append('<option value="">Cargando...</option>').prop('disabled', true);

        if (idPrograma) {
            $.ajax({
                url: 'get_materias.php',
                type: 'GET',
                data: { id_programa: idPrograma },
                dataType: 'json',
                success: function(materias) {
                    materiaSelect.empty().append('<option value="">Seleccione una materia...</option>');
                    if (materias.length > 0) {
                        $.each(materias, function(i, materia) {
                            materiaSelect.append($('<option>', { value: materia.id_plan, text: materia.codigo + ' - ' + materia.materia }));
                        });
                        materiaSelect.prop('disabled', false);
                    } else {
                        materiaSelect.empty().append('<option value="">No hay materias</option>');
                    }
                },
                error: function() {
                    materiaSelect.empty().append('<option value="">Error al cargar</option>');
                }
            });
        } else {
            materiaSelect.empty().append('<option value="">Seleccione un programa primero</option>').prop('disabled', true);
        }
    });
});

function confirmarEliminarGrupo(idGrupo) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto! Se eliminará el grupo de estudio y todas sus matrículas.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_grupo.php?id=' + idGrupo;
        }
    });
}
</script>

<?php 
$mysqli->close();
include 'includes/footer.php'; 
?>
