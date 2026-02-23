<?php
ob_start(); // INICIA EL BUFFER DE SALIDA
session_start();

// Configuración de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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

// 1. Guardar Registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save') {
    
    $usuario = trim($_POST["usuario"] ?? "");
    $clave = trim($_POST["clave"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "Soporte");
    $link_acceso = trim($_POST["link_acceso"] ?? "");
    $datos_link = trim($_POST["datos_link"] ?? "");
    $id_formulario = isset($_POST['id_formulario']) && !empty($_POST['id_formulario']) ? $_POST['id_formulario'] : null;
    $static_data = isset($_POST['static_data_hidden']) ? trim($_POST['static_data_hidden']) : "";
    $creado_por = $_SESSION["id"];

    // Manejo de campos dinámicos
    if (!empty($id_formulario) && $id_formulario !== 'estandar') {
        if (empty($usuario)) $usuario = "-";
        if (empty($clave)) $clave = "-";
        if (empty($tipo) || $tipo == "Soporte") $tipo = "Plantilla";
        
        $campos_dinamicos_str = "";
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'dyn_field_') === 0) {
                $id_campo = str_replace('dyn_field_', '', $key);
                $sql_c = "SELECT nombre_campo, mapeo FROM formularios_campos WHERE id = ?";
                if ($stmt_c = $mysqli->prepare($sql_c)) {
                    $stmt_c->bind_param("i", $id_campo);
                    $stmt_c->execute();
                    $res_c = $stmt_c->get_result();
                    if ($row_c = $res_c->fetch_assoc()) {
                        $val_trim = trim($value);
                        $campos_dinamicos_str .= "\n" . $row_c['nombre_campo'] . ": " . $val_trim;
                        if ($row_c['mapeo'] == 'usuario' && !empty($val_trim)) $usuario = $val_trim;
                        elseif ($row_c['mapeo'] == 'clave' && !empty($val_trim)) $clave = $val_trim;
                    }
                    $stmt_c->close();
                }
            }
        }
        if (!empty($campos_dinamicos_str)) {
            $datos_link .= (!empty($datos_link) ? "\n--- DATOS DEL FORMULARIO ---\n" : "") . $campos_dinamicos_str;
        }
        if (!empty($static_data)) {
            $datos_link .= "\n\n--- INFORMACIÓN ADICIONAL ---\n" . $static_data;
        }
    }

    if ($mysqli) {
        $sql = "INSERT INTO credenciales (usuario, clave, tipo, link_acceso, datos_link, creado_por) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssssi", $usuario, $clave, $tipo, $link_acceso, $datos_link, $creado_por);
            if ($stmt->execute()) {
                header("Location: index.php?msg=saved");
                exit;
            } else {
                $message = "Error SQL: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// Eliminar
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id_del = $_GET['delete'];
    $mysqli->query("DELETE FROM credenciales WHERE id_credencial = $id_del");
    header("Location: index.php?msg=deleted");
    exit;
}

// Listado
$sql_creds = "SELECT c.*, u.nombre as creador_nombre,
              (SELECT nombre FROM plataformas p WHERE TRIM(LOWER(p.link_acceso)) = TRIM(LOWER(c.link_acceso)) LIMIT 1) as nombre_plataforma 
              FROM credenciales c 
              LEFT JOIN usuarios u ON c.creado_por = u.id 
              ORDER BY c.fecha DESC";
$result_creds = $mysqli->query($sql_creds);

$sql_forms_list = "SELECT id, nombre FROM formularios WHERE activo = 1 ORDER BY nombre";
$result_forms_list = $mysqli->query($sql_forms_list);

$sql_platforms = "SELECT * FROM plataformas ORDER BY nombre";
$result_platforms = $mysqli->query($sql_platforms);

require_once PROJECT_ROOT . '/includes/header.php';
require_once PROJECT_ROOT . '/includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock"></i> Gestión de Soporte</h2>
        <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalNuevaCredencial">
            <i class="bi bi-headset"></i> Soporte
        </button>
    </div>

    <?php if(!empty($message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo ($_GET['msg'] == 'saved') ? 'Registro guardado correctamente.' : 'Acción realizada.'; ?>
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
                                    <li><a class="dropdown-item text-danger" href="?delete=<?php echo $row['id_credencial']; ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a></li>
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
                        <div class="card-footer bg-transparent">
                            <button class="btn btn-success btn-sm w-100" onclick="compartirWhatsApp('<?php echo addslashes($row['tipo']); ?>', '<?php echo addslashes($row['usuario']); ?>', '<?php echo addslashes($row['clave']); ?>', '<?php echo addslashes($row['link_acceso']); ?>', '<?php echo addslashes($row['tipo']); ?>', '<?php echo addslashes($_SESSION['nombre']); ?>', '<?php echo addslashes(str_replace(["\r","\n"], " ", $row['datos_link'])); ?>')">
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
                        <input type="text" name="usuario" class="form-control mb-2" placeholder="Usuario">
                        <input type="text" name="clave" class="form-control mb-2" placeholder="Contraseña">
                        <textarea name="datos_link" class="form-control" placeholder="Notas"></textarea>
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
function copiarTexto(t) { navigator.clipboard.writeText(t); }
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
    navigator.clipboard.writeText(msg).then(() => { alert('Copiado para WhatsApp'); });
}
</script>
<?php require_once PROJECT_ROOT . '/includes/footer.php'; ?>