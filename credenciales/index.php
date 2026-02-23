<?php
ob_start();
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('PROJECT_ROOT', dirname(__DIR__)); 

require_once PROJECT_ROOT . '/includes/config.php';
require_once PROJECT_ROOT . '/includes/db_connect.php';
require_once PROJECT_ROOT . '/includes/permissions.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: " . BASE_URL . "login.php");
    exit;
}

$page_title = 'Gestión de Soporte';
$message = "";
$message_type = "";

// 1. LÓGICA DE GUARDADO / ACTUALIZACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save') {
    
    $id_edit = $_POST["id_credencial_edit"] ?? null;
    $usuario = trim($_POST["usuario"] ?? "");
    $clave = trim($_POST["clave"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "Soporte");
    $link_acceso = trim($_POST["link_acceso"] ?? "");
    $datos_link = trim($_POST["datos_link"] ?? "");
    $id_formulario = isset($_POST['id_formulario']) && !empty($_POST['id_formulario']) ? $_POST['id_formulario'] : null;
    $static_data = isset($_POST['static_data_hidden']) ? trim($_POST['static_data_hidden']) : "";
    $creado_por = $_SESSION["id"];

    // Iniciar Transacción para asegurar que se guarde en ambos lados
    $mysqli->begin_transaction();

    try {
        // A. Manejo de campos dinámicos
        $id_registro = null;
        if (!empty($id_formulario) && $id_formulario !== 'estandar') {
            if (empty($usuario)) $usuario = "-";
            if (empty($clave)) $clave = "-";
            if (empty($tipo) || $tipo == "Soporte" || $tipo == "Plantilla") {
                $sql_fn = "SELECT nombre FROM formularios WHERE id = ?";
                $st_fn = $mysqli->prepare($sql_fn);
                $st_fn->bind_param("i", $id_formulario);
                $st_fn->execute();
                $res_fn = $st_fn->get_result()->fetch_assoc();
                $tipo = $res_fn['nombre'] ?? "Plantilla";
                $st_fn->close();
            }
            
            // Crear registro en la tabla de formularios dinámicos si no existe uno previo
            $sql_reg = "INSERT INTO formularios_registros (id_formulario, id_usuario) VALUES (?, ?)";
            $st_reg = $mysqli->prepare($sql_reg);
            $st_reg->bind_param("ii", $id_formulario, $creado_por);
            $st_reg->execute();
            $id_registro = $mysqli->insert_id;
            $st_reg->close();

            $campos_dinamicos_str = "";
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'dyn_field_') === 0) {
                    $id_campo = str_replace('dyn_field_', '', $key);
                    $val_trim = trim($value);
                    
                    // Guardar valor individualmente en la tabla de valores dinámicos
                    $sql_val = "INSERT INTO formularios_valores (id_registro, id_campo, valor) VALUES (?, ?, ?)";
                    $st_val = $mysqli->prepare($sql_val);
                    $st_val->bind_param("iis", $id_registro, $id_campo, $val_trim);
                    $st_val->execute();
                    $st_val->close();

                    // Obtener configuración del campo para el resumen de texto y mapeo
                    $sql_c = "SELECT nombre_campo, mapeo FROM formularios_campos WHERE id = ?";
                    $st_c = $mysqli->prepare($sql_c);
                    $st_c->bind_param("i", $id_campo);
                    $st_c->execute();
                    $row_c = $st_c->get_result()->fetch_assoc();
                    
                    $campos_dinamicos_str .= "\n" . $row_c['nombre_campo'] . ": " . $val_trim;
                    
                    if ($row_c['mapeo'] == 'usuario' && !empty($val_trim)) $usuario = $val_trim;
                    elseif ($row_c['mapeo'] == 'clave' && !empty($val_trim)) $clave = $val_trim;
                    $st_c->close();
                }
            }
            
            $resumen_final = (!empty($datos_link) ? $datos_link . "\n" : "") . "--- DATOS DEL FORMULARIO ---\n" . $campos_dinamicos_str;
            if (!empty($static_data)) {
                $resumen_final .= "\n\n--- INFORMACIÓN ADICIONAL ---\n" . $static_data;
            }
            $datos_link = $resumen_final;
        }

        // B. Guardar o Actualizar en la tabla principal de credenciales
        if ($id_edit) {
            $sql = "UPDATE credenciales SET usuario=?, clave=?, tipo=?, link_acceso=?, datos_link=?, id_formulario=?, id_registro=? WHERE id_credencial=?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssssiii", $usuario, $clave, $tipo, $link_acceso, $datos_link, $id_formulario, $id_registro, $id_edit);
        } else {
            $sql = "INSERT INTO credenciales (usuario, clave, tipo, link_acceso, datos_link, creado_por, id_formulario, id_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("sssssiii", $usuario, $clave, $tipo, $link_acceso, $datos_link, $creado_por, $id_formulario, $id_registro);
        }
        
        $stmt->execute();
        $stmt->close();
        
        $mysqli->commit();
        header("Location: index.php?msg=saved");
        exit;

    } catch (Exception $e) {
        $mysqli->rollback();
        $message = "Error al procesar: " . $e->getMessage();
        $message_type = "danger";
    }
}

// 2. ELIMINAR
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id_del = $_GET['delete'];
    $mysqli->query("DELETE FROM credenciales WHERE id_credencial = $id_del");
    header("Location: index.php?msg=deleted");
    exit;
}

// 3. OBTENER LISTADO
$sql_creds = "SELECT c.*, u.nombre as creador_nombre,
              (SELECT nombre FROM plataformas p WHERE TRIM(LOWER(p.link_acceso)) = TRIM(LOWER(c.link_acceso)) LIMIT 1) as nombre_plataforma 
              FROM credenciales c 
              LEFT JOIN usuarios u ON c.creado_por = u.id 
              ORDER BY c.fecha DESC";
$result_creds = $mysqli->query($sql_creds);

$sql_forms_list = "SELECT id, nombre FROM formularios WHERE activo = 1 ORDER BY nombre";
$result_forms_list = $mysqli->query($sql_forms_list);

require_once PROJECT_ROOT . '/includes/header.php';
require_once PROJECT_ROOT . '/includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock"></i> Gestión de Soporte</h2>
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevaCredencial" onclick="resetForm()">
            <i class="bi bi-headset"></i> Soporte
        </button>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Registro guardado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php if ($result_creds && $result_creds->num_rows > 0): ?>
            <?php while($row = $result_creds->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?php echo htmlspecialchars($row['tipo']); ?></span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light border-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-warning" href="javascript:void(0);" onclick='editarRegistro(<?php echo json_encode($row); ?>)'><i class="bi bi-pencil"></i> Editar</a></li>
                                    <li><a class="dropdown-item text-danger" href="?delete=<?php echo $row['id_credencial']; ?>" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">Usuario</small>
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($row['usuario']); ?>" readonly>
                                <button class="btn btn-outline-secondary" onclick="copiarTexto('<?php echo addslashes($row['usuario']); ?>')"><i class="bi bi-clipboard"></i></button>
                            </div>
                            <small class="text-muted">Contraseña</small>
                            <div class="input-group input-group-sm mb-2">
                                <input type="password" class="form-control bg-light" value="<?php echo htmlspecialchars($row['clave']); ?>" id="p_<?php echo $row['id_credencial']; ?>" readonly>
                                <button class="btn btn-outline-secondary" onclick="togglePassword(<?php echo $row['id_credencial']; ?>)"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-outline-secondary" onclick="copiarTexto('<?php echo addslashes($row['clave']); ?>')"><i class="bi bi-clipboard"></i></button>
                            </div>
                            <p class="card-text small text-muted" style="white-space: pre-wrap;"><?php echo htmlspecialchars($row['datos_link']); ?></p>
                        </div>
                        <div class="card-footer bg-transparent border-0 d-flex gap-1">
                            <button class="btn btn-success btn-sm flex-grow-1" onclick="compartirWhatsApp('<?php echo addslashes($row['tipo']); ?>', '<?php echo addslashes($row['usuario']); ?>', '<?php echo addslashes($row['clave']); ?>', '<?php echo addslashes($row['link_acceso']); ?>', '<?php echo addslashes($row['tipo']); ?>', '<?php echo addslashes($_SESSION['nombre']); ?>', '<?php echo addslashes(str_replace(["\r","\n"], " ", $row['datos_link'])); ?>')">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><p class="text-muted">No hay registros.</p></div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalNuevaCredencial" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php" method="post" id="formSoporte">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_credencial_edit" id="id_credencial_edit">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Gestión de Soporte</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar Plantilla</label>
                        <select name="id_formulario" id="selectSubModulo" class="form-select" onchange="cargarCamposDinamicos()">
                            <option value="">-- Seleccionar --</option>
                            <option value="estandar">Registro Manual</option>
                            <?php 
                            mysqli_data_seek($result_forms_list, 0);
                            while($f = $result_forms_list->fetch_assoc()) echo '<option value="'.$f['id'].'">'.htmlspecialchars($f['nombre']).'</option>';
                            ?>
                        </select>
                    </div>
                    <div id="contenedorCamposDinamicos" style="display:none;" class="bg-light p-3 rounded mb-3">
                        <div id="dynamicFieldsBody"></div>
                    </div>
                    <div class="campo-estandar" style="display:none;">
                        <input type="text" name="usuario" id="input_usuario" class="form-control mb-2" placeholder="Usuario">
                        <input type="text" name="clave" id="input_clave" class="form-control mb-2" placeholder="Contraseña">
                        <textarea name="datos_link" id="input_notas" class="form-control" placeholder="Notas"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('formSoporte').reset();
    document.getElementById('id_credencial_edit').value = '';
    cargarCamposDinamicos();
}

function editarRegistro(data) {
    resetForm();
    document.getElementById('id_credencial_edit').value = data.id_credencial;
    const selectForm = document.getElementById('selectSubModulo');
    
    if (data.id_formulario) {
        selectForm.value = data.id_formulario;
        const c = document.getElementById('contenedorCamposDinamicos');
        const e = document.querySelectorAll('.campo-estandar');
        c.style.display = 'block'; e.forEach(x => x.style.display = 'none');
        
        // Cargar valores dinámicos
        document.getElementById('dynamicFieldsBody').innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
        fetch('get_dynamic_values.php?id_reg=' + data.id_registro)
            .then(r => r.text())
            .then(h => { document.getElementById('dynamicFieldsBody').innerHTML = h; });
    } else {
        selectForm.value = 'estandar';
        document.querySelectorAll('.campo-estandar').forEach(x => x.style.display = 'block');
        document.getElementById('input_usuario').value = data.usuario;
        document.getElementById('input_clave').value = data.clave;
        document.getElementById('input_notas').value = data.datos_link;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('modalNuevaCredencial'));
    modal.show();
}

function copiarTexto(t) { 
    navigator.clipboard.writeText(t).then(() => {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500 });
        Toast.fire({ icon: 'success', title: 'Copiado' });
    });
}
function togglePassword(id) { 
    const i = document.getElementById('p_' + id); 
    i.type = i.type === "password" ? "text" : "password"; 
}
function cargarCamposDinamicos() {
    const v = document.getElementById('selectSubModulo').value;
    const c = document.getElementById('contenedorCamposDinamicos');
    const e = document.querySelectorAll('.campo-estandar');
    if (v === 'estandar') {
        c.style.display = 'none'; e.forEach(x => x.style.display = 'block');
    } else if (v !== '') {
        c.style.display = 'block'; e.forEach(x => x.style.display = 'none');
        fetch('get_dynamic_fields.php?id_form=' + v).then(r => r.text()).then(h => { document.getElementById('dynamicFieldsBody').innerHTML = h; });
    } else {
        c.style.display = 'none'; e.forEach(x => x.style.display = 'none');
    }
}
function compartirWhatsApp(p, u, c, l, t, a, n) {
    let msg = `*${p.toUpperCase()}*\n\nUsuario: *${u}*\nClave: *${c}*\n\nNotas: ${n}\n\n_Atendido por: ${a}_`;
    navigator.clipboard.writeText(msg).then(() => {
        Swal.fire({ title: 'Copiado', text: 'Pégalo en WhatsApp', icon: 'success', confirmButtonColor: '#25D366' });
    });
}
</script>
<?php require_once PROJECT_ROOT . '/includes/footer.php'; ?>