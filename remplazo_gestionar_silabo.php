<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$id_profesor = $_SESSION['id'];
$message = "";

// Determinar qué materia estamos gestionando
$id_plan = $_GET['id_plan'] ?? null;
$id_curso_volver = $_GET['id_curso'] ?? null; // Capturar ID curso para volver

if (!$id_plan) {
    // Si viene de ver_curso.php, obtenemos el id_plan del curso activo
    if (isset($_GET['id_curso'])) {
        $id_curso = $_GET['id_curso'];
        $stmt_c = $mysqli->prepare("SELECT id_plan FROM cursos_activos WHERE id_curso_activo = ?");
        $stmt_c->bind_param("i", $id_curso);
        $stmt_c->execute();
        $res_c = $stmt_c->get_result();
        if ($row_c = $res_c->fetch_assoc()) {
            $id_plan = $row_c['id_plan'];
        }
        $stmt_c->close();
    }
}

if(!$id_plan) {
    header("location: index.php?error=No se especificó la materia.");
    exit;
}

// 1. Obtener Info de la Materia
$sql_materia = "SELECT materia, codigo FROM plan_estudios WHERE id_plan = ?";
$stmt_m = $mysqli->prepare($sql_materia);
$stmt_m->bind_param("i", $id_plan);
$stmt_m->execute();
$materia_info = $stmt_m->get_result()->fetch_assoc();
$stmt_m->close();

// 2. Cargar datos del Sílabo para esta materia y este profesor
$silabo = null;
$id_silabo = null;
$res_silabo = $mysqli->query("SELECT * FROM silabos WHERE id_plan = $id_plan AND id_profesor = $id_profesor");
if ($res_silabo->num_rows > 0) {
    $silabo = $res_silabo->fetch_assoc();
    $id_silabo = $silabo['id_silabo'];
}

// 3. Procesar Guardado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_silabo'])) {
    $desc = $_POST['descripcion'];
    $obj_gen = $_POST['objetivo_general'];
    $obj_esp = $_POST['objetivos_especificos'];
    $metodo = $_POST['metodologia'];
    $contenidos = $_POST['contenidos'];
    $cronograma = $_POST['cronograma'];
    $biblio = $_POST['bibliografia'];
    $mod = $_POST['modalidad'];
    
    // Concatenar Día y Hora
    $h_dia = $_POST['horario_dia'] ?? '';
    $h_hora = $_POST['horario_hora'] ?? '';
    $horario = trim("$h_dia $h_hora");

    if ($silabo) {
        // Update
        $sql = "UPDATE silabos SET descripcion=?, objetivo_general=?, objetivos_especificos=?, metodologia=?, contenidos=?, cronograma=?, bibliografia=?, modalidad=?, horario=? WHERE id_silabo=?";
        $stmt_up = $mysqli->prepare($sql);
        $stmt_up->bind_param("sssssssssi", $desc, $obj_gen, $obj_esp, $metodo, $contenidos, $cronograma, $biblio, $mod, $horario, $id_silabo);
        $stmt_up->execute();
    } else {
        // Insert
        $sql = "INSERT INTO silabos (id_plan, id_profesor, descripcion, objetivo_general, objetivos_especificos, metodologia, contenidos, cronograma, bibliografia, modalidad, horario) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $stmt_in = $mysqli->prepare($sql);
        $stmt_in->bind_param("iisssssssss", $id_plan, $id_profesor, $desc, $obj_gen, $obj_esp, $metodo, $contenidos, $cronograma, $biblio, $mod, $horario);
        $stmt_in->execute();
        $id_silabo = $mysqli->insert_id;
    }

    // --- Guardar Evaluación (Lógica Mejorada para no borrar notas) ---
    $ids_evaluacion_post = $_POST['id_evaluacion'] ?? [];
    $rubros_post = $_POST['rubros'] ?? [];
    $porcentajes_post = $_POST['porcentajes'] ?? [];
    
    $ids_actuales_db = [];
    $res_ids = $mysqli->query("SELECT id_evaluacion FROM silabo_evaluacion WHERE id_silabo = $id_silabo");
    while($row = $res_ids->fetch_assoc()) {
        $ids_actuales_db[] = $row['id_evaluacion'];
    }

    $ids_mantener = [];

    foreach ($rubros_post as $key => $rubro_nombre) {
        if (!empty($rubro_nombre)) {
            $id_eval = $ids_evaluacion_post[$key] ?? null;
            $porcentaje = (int)($porcentajes_post[$key] ?? 0);

            if (!empty($id_eval) && in_array($id_eval, $ids_actuales_db)) {
                $stmt_update_ev = $mysqli->prepare("UPDATE silabo_evaluacion SET rubro = ?, porcentaje = ? WHERE id_evaluacion = ? AND id_silabo = ?");
                $stmt_update_ev->bind_param("siii", $rubro_nombre, $porcentaje, $id_eval, $id_silabo);
                $stmt_update_ev->execute();
                $ids_mantener[] = $id_eval;
            } else {
                $stmt_insert_ev = $mysqli->prepare("INSERT INTO silabo_evaluacion (id_silabo, rubro, porcentaje) VALUES (?, ?, ?)");
                $stmt_insert_ev->bind_param("isi", $id_silabo, $rubro_nombre, $porcentaje);
                $stmt_insert_ev->execute();
            }
        }
    }

    $ids_para_eliminar = array_diff($ids_actuales_db, $ids_mantener);
    if (!empty($ids_para_eliminar)) {
        $ids_eliminar_str = implode(',', array_map('intval', $ids_para_eliminar));
        $mysqli->query("DELETE FROM silabo_evaluacion WHERE id_silabo = $id_silabo AND id_evaluacion IN ($ids_eliminar_str)");
    }
    // --- Fin de Lógica Mejorada ---


    // Guardar Cronograma
    $mysqli->query("DELETE FROM silabo_cronograma WHERE id_silabo = $id_silabo");
    $semanas = $_POST['c_semana'] ?? [];
    $fechas = $_POST['c_fecha'] ?? [];
    $actividades = $_POST['c_actividad'] ?? [];

    foreach ($semanas as $key => $sem) {
        if (!empty($sem)) {
            $fec = $fechas[$key] ?? '';
            $act = $actividades[$key] ?? '';
            $stmt_cro = $mysqli->prepare("INSERT INTO silabo_cronograma (id_silabo, semana, fecha, actividad) VALUES (?, ?, ?, ?)");
            $stmt_cro->bind_param("isss", $id_silabo, $sem, $fec, $act);
            $stmt_cro->execute();
        }
    }

    $redirect_url = "gestionar_silabo.php?id_plan=$id_plan&msg=ok";
    if($id_curso_volver) $redirect_url .= "&id_curso=$id_curso_volver";
    
    header("Location: $redirect_url");
    exit;
}

// Obtener rubros
$evaluaciones = [];
if($id_silabo) {
    $res_ev = $mysqli->query("SELECT * FROM silabo_evaluacion WHERE id_silabo = $id_silabo");
    while($row = $res_ev->fetch_assoc()) $evaluaciones[] = $row;
}

// Obtener cronograma
$cronograma_data = [];
if($id_silabo) {
    $res_cro = $mysqli->query("SELECT * FROM silabo_cronograma WHERE id_silabo = $id_silabo");
    while($row = $res_cro->fetch_assoc()) $cronograma_data[] = $row;
}

// Obtener contenidos
$contenidos_data = [];
if($id_silabo) {
    $res_con = $mysqli->query("SELECT * FROM silabo_contenidos WHERE id_silabo = $id_silabo");
    while($row = $res_con->fetch_assoc()) $contenidos_data[] = $row;
}

$page_title = "Gestionar Programa: " . $materia_info['materia'];
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<script src="https://cdn.tiny.cloud/1/jjwu40cfr9t3r2semmriq8uv0lvwbgdkjla0psbt7snt9itx/tinymce/7/tinymce.min.js"></script>
<script>
  tinymce.init({
    selector: '.editor-rico',
    plugins: 'lists link table code',
    toolbar: 'undo redo | bold italic | bullist numlist | link table | code',
    menubar: false,
    height: 250
  });
</script>

<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Mis Cursos</a></li>
            <?php if($id_curso_volver): ?>
                <li class="breadcrumb-item"><a href="ver_curso.php?id=<?php echo $id_curso_volver; ?>"><?php echo htmlspecialchars($materia_info['materia']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active">Gestionar Sílabo</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-journal-text text-primary"></i> Programa Académico</h2>
            <small class="text-muted"><?php echo htmlspecialchars($materia_info['codigo'] . " - " . $materia_info['materia']); ?></small>
        </div>
        <div>
            <?php if($id_silabo): ?>
                <a href="crear_silabo_documento.php?id_plan=<?php echo $id_plan; ?>&id_curso=<?php echo $id_curso_volver; ?>" class="btn btn-info me-2"><i class="bi bi-send"></i> Enviar Sílabo (PDF)</a>
            <?php endif; ?>
            <?php 
                $link_volver = $id_curso_volver ? "ver_curso.php?id=$id_curso_volver" : "index.php";
            ?>
            <a href="<?php echo $link_volver; ?>" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <?php if(isset($_GET['msg'])) echo "<script>Swal.fire('Hecho', 'Programa guardado correctamente.', 'success');</script>"; ?>

    <form action="" method="POST" id="formSilabo">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="silaboTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">General</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#academic" type="button">Académico</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#content" type="button">Contenidos</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#eval" type="button">Evaluación</button></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="general">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Modalidad</label>
                                <select name="modalidad" class="form-select">
                                    <option value="Presencial" <?php echo ($silabo && $silabo['modalidad'] == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                                    <option value="Virtual" <?php echo ($silabo && $silabo['modalidad'] == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
                                    <option value="Híbrido" <?php echo ($silabo && $silabo['modalidad'] == 'Híbrido') ? 'selected' : ''; ?>>Híbrido</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Horario Sugerido</label>
                                <div class="input-group">
                                    <?php 
                                        $h_val = $silabo['horario'] ?? '';
                                        $parts = explode(' ', $h_val, 2);
                                        $sel_dia = $parts[0] ?? '';
                                        $sel_hora = $parts[1] ?? '';
                                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                                    ?>
                                    <select name="horario_dia" class="form-select">
                                        <option value="">Día...</option>
                                        <?php foreach($dias as $d): ?>
                                            <option value="<?php echo $d; ?>" <?php echo ($sel_dia == $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="time" name="horario_hora" class="form-control" value="<?php echo $sel_hora; ?>">
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Descripción del Curso</label>
                                <textarea name="descripcion" class="form-control editor-rico"><?php echo $silabo['descripcion'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="academic">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Objetivo General</label>
                            <textarea name="objetivo_general" class="form-control editor-rico"><?php echo $silabo['objetivo_general'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Objetivos Específicos</label>
                            <textarea name="objetivos_especificos" class="form-control editor-rico"><?php echo $silabo['objetivos_especificos'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Metodología</label>
                            <textarea name="metodologia" class="form-control editor-rico"><?php echo $silabo['metodologia'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="content">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Contenidos del Curso</label>
                            <textarea name="contenidos" class="form-control editor-rico"><?php echo $silabo['contenidos'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cronograma de Actividades</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="tablaCronograma">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 100px;">Semana</th>
                                            <th style="width: 150px;">Fechas</th>
                                            <th>Actividad / Tema</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($cronograma_data)): ?>
                                            <tr>
                                                <td><input type="text" name="c_semana[]" class="form-control form-control-sm" placeholder="1"></td>
                                                <td><input type="date" name="c_fecha[]" class="form-control form-control-sm"></td>
                                                <td><textarea name="c_actividad[]" class="form-control form-control-sm" rows="4"></textarea></td>
                                                <td></td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($cronograma_data as $cro): ?>
                                                <tr>
                                                    <td><input type="text" name="c_semana[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($cro['semana']); ?>"></td>
                                                    <td><input type="date" name="c_fecha[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($cro['fecha']); ?>"></td>
                                                    <td><textarea name="c_actividad[]" class="form-control form-control-sm" rows="3"><?php echo htmlspecialchars($cro['actividad']); ?></textarea></td>
                                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="agregarFilaCronograma()"><i class="bi bi-plus-lg"></i> Añadir Semana</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Bibliografía</label>
                            <textarea name="bibliografia" class="form-control editor-rico"><?php echo $silabo['bibliografia'] ?? ''; ?></textarea>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="eval">
                        <div class="alert alert-info py-2 small"><i class="bi bi-info-circle"></i> Defina los rubros de evaluación. Estos se usarán para generar la tabla de notas del curso.</div>
                        <table class="table table-bordered" id="tablaEvaluacion">
                            <thead class="table-light">
                                <tr>
                                    <th>Rubro de Evaluación</th>
                                    <th style="width: 150px;">Porcentaje (%)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($evaluaciones)): ?>
                                    <tr>
                                        <td><input type="text" name="rubros[]" class="form-control" placeholder="Ej: Examen 1"></td>
                                        <td><input type="number" name="porcentajes[]" class="form-control valor-p" value="0"></td>
                                        <td></td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($evaluaciones as $ev): ?>
                                        <tr>
                                            <td>
                                                <input type="hidden" name="id_evaluacion[]" value="<?php echo $ev['id_evaluacion']; ?>">
                                                <input type="text" name="rubros[]" class="form-control" value="<?php echo htmlspecialchars($ev['rubro']); ?>">
                                            </td>
                                            <td><input type="number" name="porcentajes[]" class="form-control valor-p" value="<?php echo $ev['porcentaje']; ?>"></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end fw-bold">Total:</td>
                                    <td class="text-center fw-bold" id="totalPorcentaje">0%</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-sm btn-success" onclick="agregarFila()"><i class="bi bi-plus-lg"></i> Añadir Rubro</button>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light p-3">
                <button type="submit" name="guardar_silabo" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-save"></i> Guardar Todo el Programa
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function agregarFila() {
    const table = document.getElementById('tablaEvaluacion').getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    row.innerHTML = `
        <td>
            <input type="hidden" name="id_evaluacion[]" value="">
            <input type="text" name="rubros[]" class="form-control">
        </td>
        <td><input type="number" name="porcentajes[]" class="form-control valor-p" value="0"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button></td>
    `;
    vincularEventos();
}

function agregarFilaCronograma() {
    const table = document.getElementById('tablaCronograma').getElementsByTagName('tbody')[0];
    const row = table.insertRow();
    row.innerHTML = `
        <td><input type="text" name="c_semana[]" class="form-control form-control-sm"></td>
        <td><input type="date" name="c_fecha[]" class="form-control form-control-sm"></td>
        <td><textarea name="c_actividad[]" class="form-control form-control-sm" rows="3"></textarea></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button></td>
    `;
}

function eliminarFila(btn) {
    btn.closest('tr').remove();
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    document.querySelectorAll('.valor-p').forEach(input => {
        total += parseInt(input.value) || 0;
    });
    const display = document.getElementById('totalPorcentaje');
    display.innerText = total + '%';
    display.className = (total === 100) ? 'text-center fw-bold text-success' : 'text-center fw-bold text-danger';
}

function vincularEventos() {
    document.querySelectorAll('.valor-p').forEach(input => {
        input.addEventListener('input', calcularTotal);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    calcularTotal();
    vincularEventos();
});
</script>

<?php require_once '../includes/footer.php'; ?>
