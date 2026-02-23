<?php
ob_start();
session_start();

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: " . BASE_URL . "login.php");
    exit;
}

// Verificar ID de Formulario
if(!isset($_GET['id']) || !ctype_digit($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id_form = $_GET['id'];
$message = "";
$message_type = "";

// 1. LÓGICA DE PROCESAMIENTO: Guardar Nuevo Campo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_field') {
    $nombre_campo = trim($_POST["nombre_campo"]);
    $tipo_campo = trim($_POST["tipo_campo"]);
    $opciones = trim($_POST["opciones"]);
    $requerido = isset($_POST["requerido"]) ? 1 : 0;
    $mapeo = $_POST["mapeo"];
    
    if ($mysqli) {
        $sql = "INSERT INTO formularios_campos (id_formulario, nombre_campo, tipo_campo, opciones, requerido, mapeo) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("isssis", $id_form, $nombre_campo, $tipo_campo, $opciones, $requerido, $mapeo);
            if ($stmt->execute()) {
                header("Location: configurar_campos.php?id=$id_form&msg=field_saved");
                exit;
            } else {
                $message = "Error al guardar el campo: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// 2. LÓGICA DE PROCESAMIENTO: Eliminar Campo
if (isset($_GET['delete_field']) && ctype_digit($_GET['delete_field'])) {
    $id_del = $_GET['delete_field'];
    $sql_del = "DELETE FROM formularios_campos WHERE id = ? AND id_formulario = ?";
    if ($stmt_del = $mysqli->prepare($sql_del)) {
        $stmt_del->bind_param("ii", $id_del, $id_form);
        if ($stmt_del->execute()) {
            header("Location: configurar_campos.php?id=$id_form&msg=field_deleted");
            exit;
        }
        $stmt_del->close();
    }
}

// Obtener detalles del formulario
$sql_form = "SELECT * FROM formularios WHERE id = ?";
$stmt_form = $mysqli->prepare($sql_form);
$stmt_form->bind_param("i", $id_form);
$stmt_form->execute();
$formulario = $stmt_form->get_result()->fetch_assoc();
$stmt_form->close();

if(!$formulario){
    header("Location: index.php");
    exit;
}

// Obtener campos existentes
$sql_fields = "SELECT * FROM formularios_campos WHERE id_formulario = ? ORDER BY orden, id";
$stmt_fields = $mysqli->prepare($sql_fields);
$stmt_fields->bind_param("i", $id_form);
$stmt_fields->execute();
$result_fields = $stmt_fields->get_result();

// --- VISTA ---
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php">Sub-Módulos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Configurar Campos</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <div>
            <h2><i class="bi bi-gear-fill"></i> Configurar: <?php echo htmlspecialchars($formulario['nombre']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($formulario['descripcion']); ?></p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoCampo">
            <i class="bi bi-plus-square"></i> Añadir Campo
        </button>
    </div>

    <!-- Alertas -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                if($_GET['msg'] == 'field_saved') echo "Campo añadido al sub-módulo.";
                if($_GET['msg'] == 'field_deleted') echo "Campo eliminado del sub-módulo.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla de Campos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">Campos Actuales</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre del Campo</th>
                                    <th>Tipo</th>
                                    <th>Requerido</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_fields && $result_fields->num_rows > 0): ?>
                                    <?php while($row = $result_fields->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['nombre_campo']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo $row['tipo_campo']; ?></span>
                                            </td>
                                            <td>
                                                <?php if($row['requerido']): ?>
                                                    <span class="badge bg-danger">Sí</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $row['id']; ?>)" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted fst-italic">No ha definido ningún campo todavía.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body">
                    <h5><i class="bi bi-info-circle"></i> Consejos</h5>
                    <ul class="small mt-3">
                        <li>El campo **Selector (Select)** requiere que escribas las opciones separadas por coma en el campo "Opciones".</li>
                        <li>El campo **Password** ocultará el texto mientras se escribe.</li>
                        <li>Asegúrese de definir campos claros para que los usuarios no cometan errores al llenarlos.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Campo -->
<div class="modal fade" id="modalNuevoCampo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id_form; ?>" method="post">
                <input type="hidden" name="action" value="save_field">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Añadir Campo al Formulario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-modal="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Campo / Pregunta</label>
                        <input type="text" name="nombre_campo" class="form-control" placeholder="Ej: Dirección IP, Serial de Servidor, etc." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de Dato</label>
                        <select name="tipo_campo" class="form-select" id="tipoCampoSelect" onchange="toggleOpciones()" required>
                            <option value="text">Texto Corto</option>
                            <option value="textarea">Texto Largo (Área de texto)</option>
                            <option value="number">Número</option>
                            <option value="date">Fecha</option>
                            <option value="select">Selector (Varias opciones)</option>
                            <option value="password">Contraseña</option>
                            <option value="email">Email</option>
                            <option value="url">URL (Link)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary">Asignar a Campo Principal (Mapeo)</label>
                        <select name="mapeo" class="form-select border-primary">
                            <option value="ninguno">Ninguno (Solo guardar en notas)</option>
                            <option value="usuario">Usar como Usuario Principal</option>
                            <option value="clave">Usar como Contraseña Principal</option>
                        </select>
                        <div class="form-text">Si seleccionas uno, este dato aparecerá en los inputs finales de la tarjeta de credencial.</div>
                    </div>
                    <div class="mb-3" id="campoOpciones" style="display:none;">
                        <label class="form-label">Opciones del Selector (Separadas por coma)</label>
                        <input type="text" name="opciones" class="form-control" placeholder="Ej: Opción 1, Opción 2, Opción 3">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="requerido" id="checkRequerido">
                        <label class="form-check-label" for="checkRequerido">Este campo es obligatorio</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Campo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleOpciones() {
    const select = document.getElementById('tipoCampoSelect');
    const divOpciones = document.getElementById('campoOpciones');
    if (select.value === 'select') {
        divOpciones.style.display = 'block';
    } else {
        divOpciones.style.display = 'none';
    }
}

function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar Campo?',
        text: "Se borrará este campo y toda la información asociada en los registros existentes.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar campo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?id=<?php echo $id_form; ?>&delete_field=' + id;
        }
    });
}
</script>

<?php 
require_once '../includes/footer.php'; 
ob_end_flush();
?>