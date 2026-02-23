<?php
ob_start(); // INICIA EL BUFFER DE SALIDA: Crucial para evitar pantallas blancas por headers
session_start();

// Configuración de errores para depuración (si sigue fallando, veremos el error)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir ruta base segura
define('PROJECT_ROOT', dirname(__DIR__)); 

require_once PROJECT_ROOT . '/includes/config.php';
require_once PROJECT_ROOT . '/includes/db_connect.php';
require_once PROJECT_ROOT . '/includes/permissions.php';

// Proteger la página
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: " . BASE_URL . "login.php");
    exit;
}

$page_title = 'Gestión de Credenciales';
$message = "";
$message_type = "";

// --- LÓGICA DE PROCESAMIENTO ---

// Capturar mensajes de redirección
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'saved') {
        $message = "Credencial guardada con éxito.";
        $message_type = "success";
    } elseif ($_GET['msg'] == 'platform_saved') {
        $message = "Plataforma registrada con éxito.";
        $message_type = "success";
    } elseif ($_GET['msg'] == 'deleted') {
        $message = "Registro eliminado.";
        $message_type = "warning";
    } elseif ($_GET['msg'] == 'error') {
        $message = "Ocurrió un error al procesar la solicitud.";
        $message_type = "danger";
    }
}

// 1. Guardar Credencial
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save') {
    $usuario = trim($_POST["usuario"]);
    $clave = trim($_POST["clave"]);
    $tipo = trim($_POST["tipo"]);
    $link_acceso = trim($_POST["link_acceso"]);
    $datos_link = trim($_POST["datos_link"]);
    $id_formulario = isset($_POST['id_formulario']) && !empty($_POST['id_formulario']) ? $_POST['id_formulario'] : null;
    $static_data = isset($_POST['static_data_hidden']) ? trim($_POST['static_data_hidden']) : "";
    $creado_por = $_SESSION["id"];

    // Si es un formulario dinámico, permitimos que usuario y clave estándar sean opcionales en la UI
    // pero los llenamos con "-" para la DB si vienen vacíos.
    if (!empty($id_formulario)) {
        if (empty($usuario)) $usuario = "-";
        if (empty($clave)) $clave = "-";
        if (empty($tipo)) $tipo = "Personalizado";
    }

    // Procesar campos dinámicos si existen y concatenarlos a datos_link
    $campos_dinamicos_str = "";
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'dyn_field_') === 0) {
            $id_campo = str_replace('dyn_field_', '', $key);
            
            // Obtener configuración del campo (nombre y mapeo)
            $sql_c = "SELECT nombre_campo, mapeo FROM formularios_campos WHERE id = ?";
            if ($stmt_c = $mysqli->prepare($sql_c)) {
                $stmt_c->bind_param("i", $id_campo);
                $stmt_c->execute();
                $res_c = $stmt_c->get_result();
                if ($row_c = $res_c->fetch_assoc()) {
                    $val_trim = trim($value);
                    $campos_dinamicos_str .= "\n" . $row_c['nombre_campo'] . ": " . $val_trim;
                    
                    // APLICAR MAPEO A LOS INPUTS FINALES
                    if ($row_c['mapeo'] == 'usuario' && !empty($val_trim)) {
                        $usuario = $val_trim;
                    } elseif ($row_c['mapeo'] == 'clave' && !empty($val_trim)) {
                        $clave = $val_trim;
                    }
                }
                $stmt_c->close();
            }
        }
    }

    if (!empty($campos_dinamicos_str)) {
        $datos_link .= (!empty($datos_link) ? "\n--- DATOS DEL FORMULARIO ---\n" : "") . $campos_dinamicos_str;
    }

    // Añadir datos estáticos si existen
    if (!empty($static_data)) {
        $datos_link .= "\n\n--- INFORMACIÓN ADICIONAL ---\n" . $static_data;
    }

    // Validar que la conexión exista
    if ($mysqli) {
        $sql = "INSERT INTO credenciales (usuario, clave, tipo, link_acceso, datos_link, creado_por) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssssi", $usuario, $clave, $tipo, $link_acceso, $datos_link, $creado_por);
            if ($stmt->execute()) {
                header("Location: index.php?msg=saved");
                exit; // Importante detener script aquí
            } else {
                $message = "Error SQL: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Error Prepare: " . $mysqli->error;
            $message_type = "danger";
        }
    } else {
        $message = "Error de conexión a la base de datos.";
        $message_type = "danger";
    }
}

// 2. Guardar Plataforma
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_platform') {
    $nombre_plataforma = trim($_POST["nombre_plataforma"]);
    $link_acceso = trim($_POST["link_acceso"]);
    
    if ($mysqli) {
        $sql = "INSERT INTO plataformas (nombre, link_acceso) VALUES (?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("ss", $nombre_plataforma, $link_acceso);
            if ($stmt->execute()) {
                header("Location: index.php?msg=platform_saved");
                exit;
            } else {
                $message = "Error al guardar plataforma: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// 3. Eliminar Credencial
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id_del = $_GET['delete'];
    $sql_del = "DELETE FROM credenciales WHERE id_credencial = ?";
    if ($stmt_del = $mysqli->prepare($sql_del)) {
        $stmt_del->bind_param("i", $id_del);
        if ($stmt_del->execute()) {
            header("Location: index.php?msg=deleted");
            exit;
        }
        $stmt_del->close();
    }
}

// Obtener datos para la vista
// Añadimos GROUP BY para evitar duplicidad si hay múltiples plataformas con el mismo link
$sql_creds = "SELECT c.*, u.nombre as creador_nombre, p.nombre as nombre_plataforma 
              FROM credenciales c 
              LEFT JOIN usuarios u ON c.creado_por = u.id 
              LEFT JOIN plataformas p ON TRIM(LOWER(c.link_acceso)) = TRIM(LOWER(p.link_acceso))
              GROUP BY c.id_credencial
              ORDER BY c.fecha DESC";
$result_creds = $mysqli->query($sql_creds);

$sql_platforms = "SELECT * FROM plataformas ORDER BY nombre";
$result_platforms = $mysqli->query($sql_platforms);

// --- VISTA ---
require_once PROJECT_ROOT . '/includes/header.php';
require_once PROJECT_ROOT . '/includes/navbar.php';
?>

<div class="container mt-4">
    <!-- Migas de Pan -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestión de Credenciales</li>
            <li class="breadcrumb-item"><a href="#" data-bs-toggle="modal" data-bs-target="#modalNuevaPlataforma" class="text-decoration-none"><i class="bi bi-globe"></i> Registrar Plataforma</a></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-shield-lock"></i> Gestión de Credenciales</h2>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCredencial">
                <i class="bi bi-plus-lg"></i> Nueva Credencial
            </button>
        </div>
    </div>

    <?php if(!empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: '<?php echo $message_type; ?>',
                    title: '<?php echo $message; ?>'
                });
            });
        </script>
    <?php endif; ?>

    <!-- Listado -->
    <div class="row" id="contenedorCredenciales">
        <?php if ($result_creds && $result_creds->num_rows > 0): ?>
            <?php while($row = $result_creds->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-primary me-2"><?php echo htmlspecialchars($row['tipo']); ?></span>
                                <?php if(!empty($row['nombre_plataforma'])): ?>
                                    <small class="text-white"><i class="bi bi-globe"></i> <?php echo htmlspecialchars($row['nombre_plataforma']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-light border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $row['id_credencial']; ?>)"><i class="bi bi-trash"></i> Eliminar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Usuario</small>
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($row['usuario']); ?>" readonly>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="copiarTexto('<?php echo addslashes($row['usuario']); ?>')"><i class="bi bi-clipboard"></i></button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Contraseña</small>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-sm bg-light" value="<?php echo htmlspecialchars($row['clave']); ?>" id="pass_<?php echo $row['id_credencial']; ?>" readonly>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="togglePassword(<?php echo $row['id_credencial']; ?>)"><i class="bi bi-eye" id="eye_<?php echo $row['id_credencial']; ?>"></i></button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="copiarTexto('<?php echo addslashes($row['clave']); ?>')"><i class="bi bi-clipboard"></i></button>
                                </div>
                            </div>
                            <?php if(!empty($row['link_acceso'])): ?>
                                <a href="<?php echo htmlspecialchars($row['link_acceso']); ?>" target="_blank" class="btn btn-sm btn-link p-0 mb-2 text-decoration-none"><i class="bi bi-link-45deg"></i> Acceder al sitio</a>
                            <?php endif; ?>
                            <p class="card-text small text-muted"><?php echo htmlspecialchars($row['datos_link']); ?></p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <div class="d-grid gap-2 mb-2">
                                <?php 
                                // Título: Plataforma o UNELA VIRTUAL por defecto
                                $titulo_plataforma = !empty($row['nombre_plataforma']) ? $row['nombre_plataforma'] : 'COLIBRÍ VIRTUAL';
                                // Sanitizar notas para JS (eliminar saltos de línea que rompen el onclick)
                                $notas_js = str_replace(array("\r", "\n"), " ", $row['datos_link']);
                                ?>
                                <button class="btn btn-success btn-sm" onclick="compartirWhatsApp('<?php echo addslashes($titulo_plataforma); ?>', '<?php echo addslashes($row['usuario']); ?>', '<?php echo addslashes($row['clave']); ?>', '<?php echo addslashes($row['link_acceso']); ?>', '<?php echo addslashes($row['tipo']); ?>', '<?php echo addslashes($_SESSION['nombre']); ?>', '<?php echo addslashes($notas_js); ?>')">
                                    <i class="bi bi-whatsapp"></i> Compartir por WhatsApp
                                </button>
                            </div>
                            <div class="text-end">
                                <small class="text-muted fst-italic" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-check"></i> Atendido por: 
                                    <strong><?php echo htmlspecialchars(!empty($row['creador_nombre']) ? $row['creador_nombre'] : 'Sistema'); ?></strong>
                                    <br>
                                    <i class="bi bi-clock"></i> <?php echo date("d/m/Y H:i", strtotime($row['fecha'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-inbox text-muted display-1"></i>
                <p class="text-muted mt-3">No hay credenciales registradas aún.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nueva Credencial -->
<div class="modal fade" id="modalNuevaCredencial" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="save">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Registrar Nueva Credencial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="mb-3 border-bottom pb-3">
                        <label class="form-label text-primary fw-bold"><i class="bi bi-ui-checks"></i> Elegir Plantilla / Sub-Módulo (Opcional)</label>
                        <select name="id_formulario" id="selectSubModulo" class="form-select border-primary" onchange="cargarCamposDinamicos()">
                            <option value="">-- Credencial Estándar --</option>
                            <?php 
                            // Obtener formularios dinámicos
                            $sql_forms_list = "SELECT id, nombre FROM formularios WHERE activo = 1 ORDER BY nombre";
                            $result_forms_list = $mysqli->query($sql_forms_list);
                            if ($result_forms_list && $result_forms_list->num_rows > 0) {
                                while($f_row = $result_forms_list->fetch_assoc()) {
                                    echo '<option value="' . $f_row['id'] . '">' . htmlspecialchars($f_row['nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Contenedor de Campos Dinámicos -->
                    <div id="contenedorCamposDinamicos" class="bg-light p-3 rounded mb-3" style="display:none;">
                        <h6 class="text-muted mb-3">Campos de la Plantilla:</h6>
                        <div id="dynamicFieldsBody"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Plataforma (Autocompleta el Link)</label>
                        <select id="selectPlataforma" class="form-select" onchange="actualizarLink()">
                            <option value="">-- Seleccionar Plataforma --</option>
                            <?php 
                            if ($result_platforms && $result_platforms->num_rows > 0) {
                                while($plat = $result_platforms->fetch_assoc()) {
                                    // VALUE es el LINK, TEXT es el NOMBRE
                                    echo '<option value="' . htmlspecialchars($plat['link_acceso']) . '">' . htmlspecialchars($plat['nombre']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3 campo-estandar">
                        <label class="form-label">Tipo de Acción</label>
                        <select name="tipo" class="form-select">
                            <option value="Cuenta Nueva">Cuenta Nueva</option>
                            <option value="Cambio de contraseña">Cambio de contraseña</option>
                            <option value="Actualización de datos">Actualización de datos</option>
                            <option value="Hosting / Web">Hosting / Web</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3 campo-estandar">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="usuario" class="form-control" placeholder="Ej: juan.perez">
                    </div>
                    <div class="mb-3 campo-estandar">
                        <label class="form-label">Contraseña</label>
                        <input type="text" name="clave" class="form-control" placeholder="Ej: Clave123">
                    </div>
                    <!-- Campo LINK: Se rellena vía JS pero también se puede editar -->
                    <div class="mb-3 campo-estandar">
                        <label class="form-label">Link de Acceso</label>
                        <input type="url" name="link_acceso" id="inputLinkAcceso" class="form-control" placeholder="https://...">
                    </div>
                    <div class="mb-3 campo-estandar">
                        <label class="form-label">Notas Adicionales</label>
                        <textarea name="datos_link" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarCredencial" onclick="this.disabled=true; this.innerHTML='Guardando...'; this.form.submit();">Guardar Credencial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nueva Plataforma -->
<div class="modal fade" id="modalNuevaPlataforma" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="save_platform">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">Registrar Nueva Plataforma</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <small><i class="bi bi-info-circle"></i> Esto añadirá una opción al selector de plataformas.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Plataforma</label>
                        <input type="text" name="nombre_plataforma" class="form-control" placeholder="Ej: Zoom Pro, Moodle, cPanel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link de Acceso</label>
                        <input type="url" name="link_acceso" class="form-control" placeholder="https://..." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Plataforma</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copiarTexto(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Copiado al portapapeles'
        });
    }).catch(err => {
        Swal.fire('Error', 'No se pudo copiar el texto', 'error');
    });
}

function togglePassword(id) {
    const input = document.getElementById('pass_' + id);
    const eye = document.getElementById('eye_' + id);
    if (input.type === "password") {
        input.type = "text";
        eye.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = "password";
        eye.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function compartirWhatsApp(plataforma, usuario, clave, link, tipo_accion, attendee, notas) {
    // Título en mayúsculas
    let titulo = plataforma.toUpperCase();
    
    // Obtener fecha y hora actual para el mensaje
    let ahora = new Date();
    let fechaHora = ahora.toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

    // Mensaje con bloque destacado para credenciales
    let mensaje = `🏛️ *${titulo}*\n\nHola! Aquí están tus datos de acceso para *${tipo_accion}*:\n\n` +
                  `────────────────\n` +
                  `👤 USUARIO: *${usuario}*\n` +
                  `🔑 CLAVE: *${clave}*\n` +
                  `────────────────`;
    
    if (link) {
        mensaje += `\n\n🔗 *Link de acceso:* ${link}`;
    } else {
        mensaje += `\n\n🔗 *Link de acceso:* https://renangalvan.net/inbox_colibri/`;
    }

    // Agregar notas si existen
    if (notas && notas.trim() !== '') {
        mensaje += `\n\n📝 *Notas:* ${notas}`;
    }
    
    mensaje += `\n\n_Atendido por: ${attendee} el ${fechaHora}_`;
    
    navigator.clipboard.writeText(mensaje).then(() => {
        Swal.fire({
            title: '¡Mensaje Copiado!',
            html: 'El mensaje ya está en tu portapapeles.<br>Ve a <b>WhatsApp</b> y pégalo (Ctrl+V).',
            icon: 'success',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#25D366' // Color WhatsApp
        });
    }).catch(err => {
        Swal.fire('Error', 'No se pudo copiar automáticamente. Por favor copia los datos manualmente.', 'error');
    });
}

function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?delete=' + id;
        }
    });
}

function cargarCamposDinamicos() {
    const idForm = document.getElementById('selectSubModulo').value;
    const contenedor = document.getElementById('contenedorCamposDinamicos');
    const dynamicBody = document.getElementById('dynamicFieldsBody');
    
    // Lista de contenedores de campos estándar para ocultar
    const camposEstándar = document.querySelectorAll('.campo-estandar');

    if (idForm) {
        contenedor.style.display = 'block';
        // Ocultar campos estándar
        camposEstándar.forEach(div => div.style.display = 'none');
        
        dynamicBody.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary" role="status"></div></div>';
        
        fetch('get_dynamic_fields.php?id_form=' + idForm)
            .then(response => response.text())
            .then(html => {
                dynamicBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                dynamicBody.innerHTML = '<p class="text-danger small">Error al cargar campos.</p>';
            });
    } else {
        contenedor.style.display = 'none';
        dynamicBody.innerHTML = '';
        // Volver a mostrar campos estándar
        camposEstándar.forEach(div => div.style.display = 'block');
    }
}

function actualizarLink() {
    const selector = document.getElementById('selectPlataforma');
    const inputLink = document.getElementById('inputLinkAcceso');
    const selectedOption = selector.options[selector.selectedIndex];
    
    // Si hay un valor seleccionado (el link), lo pone en el input
    if (selectedOption.value) {
        inputLink.value = selectedOption.value;
        // Guardar selección para el futuro
        localStorage.setItem('last_platform', selectedOption.value);
    } else {
        // Si el usuario selecciona "-- Seleccionar --", borramos la memoria
        localStorage.removeItem('last_platform');
    }
}

// Recuperar la última plataforma seleccionada al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const lastPlatform = localStorage.getItem('last_platform');
    const selector = document.getElementById('selectPlataforma');
    const inputLink = document.getElementById('inputLinkAcceso');

    if (lastPlatform && selector) {
        selector.value = lastPlatform;
        // Si el valor existe en el select, actualizamos también el input visualmente
        if (selector.value === lastPlatform) {
            inputLink.value = lastPlatform;
        }
    }
});
</script>

<?php 
require_once PROJECT_ROOT . '/includes/footer.php'; 
ob_end_flush(); // Enviar buffer de salida
?>