<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página
if (!isset($_SESSION['id']) || !has_permission($mysqli, 'gestionar_usuarios')) {
    header("location: dashboard.php?error=No tienes permiso para editar grupos");
    exit;
}

// Validar ID del curso activo
if (!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])) {
    header("location: gestionar_grupos.php?error=ID de grupo no válido");
    exit;
}
$id_curso_activo = trim($_GET["id"]);

$message = '';
$error = '';

// --- Lógica para procesar la actualización del grupo ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_grupo'])) {
    $id_plan = $_POST['id_plan'];
    $id_profesor = $_POST['id_profesor'];
    $periodo = trim($_POST['periodo']);
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_final = trim($_POST['fecha_final']);
    $id_estudiantes_nuevos = $_POST['id_estudiantes'] ?? [];

    if (empty($id_plan) || empty($id_profesor) || empty($periodo) || empty($fecha_inicio) || empty($fecha_final)) {
        $error = "Materia, profesor, período, fecha de inicio y fecha de finalización son campos obligatorios.";
    } else {
        $mysqli->begin_transaction();
        try {
            // 1. Actualizar el curso activo
            $sql_update_curso = "UPDATE cursos_activos SET id_plan = ?, id_profesor = ?, periodo = ?, fecha_inicio = ?, fecha_final = ? WHERE id_curso_activo = ?";
            $stmt_update_curso = $mysqli->prepare($sql_update_curso);
            $stmt_update_curso->bind_param("iisssi", $id_plan, $id_profesor, $periodo, $fecha_inicio, $fecha_final, $id_curso_activo);
            $stmt_update_curso->execute();
            $stmt_update_curso->close();

            // 2. Obtener los estudiantes actuales para no re-insertarlos (y evitar borrar sus notas)
            $estudiantes_actuales_ids = [];
            $res_actuales = $mysqli->query("SELECT id_estudiante FROM matriculas WHERE id_curso_activo = $id_curso_activo");
            while($row = $res_actuales->fetch_assoc()) {
                $estudiantes_actuales_ids[] = $row['id_estudiante'];
            }

            // 3. Insertar solo las NUEVAS matrículas
            if (!empty($id_estudiantes_nuevos)) {
                $sql_insert = "INSERT INTO matriculas (id_estudiante, id_curso_activo) VALUES (?, ?)";
                $stmt_insert = $mysqli->prepare($sql_insert);
                foreach ($id_estudiantes_nuevos as $id_estudiante) {
                    // Solo insertar si el estudiante no está ya matriculado
                    if (!in_array($id_estudiante, $estudiantes_actuales_ids)) {
                        $stmt_insert->bind_param("ii", $id_estudiante, $id_curso_activo);
                        $stmt_insert->execute();
                    }
                }
                $stmt_insert->close();
            }

            // NOTA: La lógica actual no elimina estudiantes del grupo para proteger sus notas.
            // Esto debe hacerse desde otra interfaz o directamente en la base de datos con cuidado.

            $mysqli->commit();
            header("location: gestionar_grupos.php?success=Grupo actualizado con éxito. Se añadieron nuevos estudiantes si los había.");
            exit;
        } catch (mysqli_sql_exception $exception) {
            $mysqli->rollback();
            $error = "Error al actualizar el grupo: " . $exception->getMessage();
        }
    }
}

// --- Obtener datos para el formulario ---

// Obtener detalles del grupo/curso activo
$grupo = null;
$sql_grupo = "
    SELECT ca.id_plan, ca.id_profesor, ca.periodo, ca.fecha_inicio, ca.fecha_final, pe.id_programa
    FROM cursos_activos ca
    JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
    WHERE ca.id_curso_activo = ?
";
if ($stmt_grupo = $mysqli->prepare($sql_grupo)) {
    $stmt_grupo->bind_param("i", $id_curso_activo);
    $stmt_grupo->execute();
    $result_grupo = $stmt_grupo->get_result();
    if ($result_grupo->num_rows === 1) {
        $grupo = $result_grupo->fetch_assoc();
    } else {
        header("location: gestionar_grupos.php?error=Grupo no encontrado");
        exit;
    }
    $stmt_grupo->close();
}

// Obtener todos los programas
$programas = [];
$sql_programas = "SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa ASC";
$result_programas = $mysqli->query($sql_programas);
if ($result_programas) while ($row = $result_programas->fetch_assoc()) $programas[] = $row;

// Obtener todas las materias para el programa actual (para la carga inicial)
$materias_actuales = [];
$sql_materias = "SELECT id_plan, materia, codigo FROM plan_estudios WHERE id_programa = ? ORDER BY materia ASC";
if($stmt_materias = $mysqli->prepare($sql_materias)) {
    $stmt_materias->bind_param("i", $grupo['id_programa']);
    $stmt_materias->execute();
    $result_materias = $stmt_materias->get_result();
    if ($result_materias) while ($row = $result_materias->fetch_assoc()) $materias_actuales[] = $row;
    $stmt_materias->close();
}

// Obtener todos los profesores (filtrado por rol)
$profesores = [];
$sql_profesores = "SELECT u.id, u.nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id WHERE r.nombre = 'Profesor' ORDER BY u.nombre ASC";
$result_profesores = $mysqli->query($sql_profesores);
if ($result_profesores) while ($row = $result_profesores->fetch_assoc()) $profesores[] = $row;

// Obtener los estudiantes ya matriculados para pre-seleccionarlos
$estudiantes_matriculados = [];
$sql_matriculados = "SELECT u.id, u.nombre, u.cedula FROM matriculas m JOIN usuarios u ON m.id_estudiante = u.id WHERE m.id_curso_activo = ?";
if ($stmt_matriculados = $mysqli->prepare($sql_matriculados)) {
    $stmt_matriculados->bind_param("i", $id_curso_activo);
    $stmt_matriculados->execute();
    $result_matriculados = $stmt_matriculados->get_result();
    while ($row = $result_matriculados->fetch_assoc()) {
        $estudiantes_matriculados[] = $row;
    }
    $stmt_matriculados->close();
}

// Parsear el período para pre-seleccionar los dropdowns
$periodo_parts = explode(' ', $grupo['periodo'], 2);
$cuatrimestre_actual = $periodo_parts[0] . (isset($periodo_parts[1]) && !is_numeric($periodo_parts[1]) ? ' ' . $periodo_parts[1] : '');
preg_match('/\d{4}/', $grupo['periodo'], $year_match);
$ano_actual = $year_match[0] ?? date('Y');

// Simplificando la extracción del cuatrimestre
if (strpos($grupo['periodo'], 'I Cuatrimestre') === 0) $cuatrimestre_actual = 'I Cuatrimestre';
if (strpos($grupo['periodo'], 'II Cuatrimestre') === 0) $cuatrimestre_actual = 'II Cuatrimestre';
if (strpos($grupo['periodo'], 'III Cuatrimestre') === 0) $cuatrimestre_actual = 'III Cuatrimestre';
if (strpos($grupo['periodo'], 'IV Cuatrimestre') === 0) $cuatrimestre_actual = 'IV Cuatrimestre';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo de Estudio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">
    
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Editar Grupo de Estudio</h3>
        </div>
        <div class="card-body">
            <form action="editar_grupo.php?id=<?php echo $id_curso_activo; ?>" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_programa" class="form-label">Programa</label>
                        <select name="id_programa" id="id_programa" class="form-select" required>
                            <option value="">Seleccione un programa...</option>
                            <?php foreach ($programas as $programa): ?>
                                <option value="<?php echo $programa['id_programa']; ?>" <?php echo ($programa['id_programa'] == $grupo['id_programa']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($programa['nombre_programa']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="id_plan" class="form-label">Materia</label>
                        <select name="id_plan" id="id_plan" class="form-select" required>
                            <option value="">Seleccione un programa primero...</option>
                            <?php foreach ($materias_actuales as $materia): ?>
                                <option value="<?php echo $materia['id_plan']; ?>" <?php echo ($materia['id_plan'] == $grupo['id_plan']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($materia['codigo'] . ' - ' . $materia['materia']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_profesor" class="form-label">Profesor</label>
                        <select name="id_profesor" id="id_profesor" class="form-select" required>
                            <option value="">Seleccione un profesor...</option>
                            <?php foreach ($profesores as $profesor): ?>
                                <option value="<?php echo $profesor['id']; ?>" <?php echo ($profesor['id'] == $grupo['id_profesor']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($profesor['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="cuatrimestre_select" class="form-label">Cuatrimestre</label>
                        <select id="cuatrimestre_select" class="form-select">
                            <option value="I Cuatrimestre" <?php echo $cuatrimestre_actual == 'I Cuatrimestre' ? 'selected' : ''; ?>>I Cuatrimestre</option>
                            <option value="II Cuatrimestre" <?php echo $cuatrimestre_actual == 'II Cuatrimestre' ? 'selected' : ''; ?>>II Cuatrimestre</option>
                            <option value="III Cuatrimestre" <?php echo $cuatrimestre_actual == 'III Cuatrimestre' ? 'selected' : ''; ?>>III Cuatrimestre</option>
                            <option value="IV Cuatrimestre" <?php echo $cuatrimestre_actual == 'IV Cuatrimestre' ? 'selected' : ''; ?>>IV Cuatrimestre</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="ano_select" class="form-label">Año</label>
                        <select id="ano_select" class="form-select">
                            <?php 
                                $current_year = date('Y');
                                for ($i = -2; $i <= 3; $i++) {
                                    $year = $current_year + $i;
                                    echo "<option value='{$year}'" . ($year == $ano_actual ? ' selected' : '') . ">{$year}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <input type="hidden" name="periodo" id="periodo" value="<?php echo htmlspecialchars($grupo['periodo']); ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($grupo['fecha_inicio']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fecha_final" class="form-label">Fecha de Finalización</label>
                        <input type="date" name="fecha_final" id="fecha_final" class="form-control" value="<?php echo htmlspecialchars($grupo['fecha_final']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="id_estudiantes" class="form-label">Estudiantes Matriculados</label>
                    <select name="id_estudiantes[]" id="id_estudiantes" class="form-select" multiple>
                        <?php foreach ($estudiantes_matriculados as $estudiante): ?>
                            <option value="<?php echo $estudiante['id']; ?>" selected>
                                <?php echo htmlspecialchars($estudiante['nombre'] . ' (' . $estudiante['cedula'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="editar_grupo" class="btn btn-primary">Guardar Cambios</button>
                <a href="gestionar_grupos.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('#id_programa, #id_plan, #id_profesor, #cuatrimestre_select, #ano_select').select2({ theme: 'bootstrap-5' });
    
    // Lógica para combinar cuatrimestre y año en el campo 'periodo'
    function actualizarPeriodo() {
        const cuatrimestre = $('#cuatrimestre_select').val();
        const ano = $('#ano_select').val();
        $('#periodo').val(cuatrimestre + ' ' + ano);
    }

    // Actualizar al cambiar la selección
    $('#cuatrimestre_select, #ano_select').on('change', actualizarPeriodo);
    
    $('#id_estudiantes').select2({
        theme: 'bootstrap-5',
        placeholder: 'Busque y seleccione estudiantes por nombre o cédula',
        closeOnSelect: false,
        ajax: {
            url: 'get_estudiantes.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term, page: params.page };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.results,
                    pagination: { more: data.pagination.more }
                };
            },
            cache: true
        }
    });

    // Evento change para el dropdown de programas
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
                        $.each(materias, function(index, materia) {
                            materiaSelect.append($('<option>', {
                                value: materia.id_plan,
                                text: materia.codigo + ' - ' + materia.materia
                            }));
                        });
                        materiaSelect.prop('disabled', false);
                    } else {
                        materiaSelect.empty().append('<option value="">No hay materias para este programa</option>');
                    }
                },
                error: function() {
                    materiaSelect.empty().append('<option value="">Error al cargar materias</option>');
                }
            });
        } else {
            materiaSelect.empty().append('<option value="">Seleccione un programa primero...</option>').prop('disabled', true);
        }
    });
});
</script>
</body>
</html>
