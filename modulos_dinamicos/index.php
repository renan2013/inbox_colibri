<?php
ob_start();
session_start();

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Proteger la página (Permiso administrativo o gestionar_etiquetas por ahora)
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: " . BASE_URL . "login.php");
    exit;
}

$page_title = 'Gestor de Sub-Módulos Dinámicos';
$message = "";
$message_type = "";

// 1. LÓGICA DE PROCESAMIENTO: Guardar Nuevo Formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_form') {
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $datos_estaticos = trim($_POST["datos_estaticos"]);
    
    if ($mysqli) {
        $sql = "INSERT INTO formularios (nombre, descripcion, datos_estaticos) VALUES (?, ?, ?)";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sss", $nombre, $descripcion, $datos_estaticos);
            if ($stmt->execute()) {
                header("Location: index.php?msg=saved");
                exit;
            } else {
                $message = "Error al guardar el módulo: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// 2. LÓGICA DE PROCESAMIENTO: Editar Formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_form') {
    $id_edit = $_POST["id"];
    $nombre = trim($_POST["nombre"]);
    $descripcion = trim($_POST["descripcion"]);
    $datos_estaticos = trim($_POST["datos_estaticos"]);
    
    if ($mysqli) {
        $sql = "UPDATE formularios SET nombre = ?, descripcion = ?, datos_estaticos = ? WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("sssi", $nombre, $descripcion, $datos_estaticos, $id_edit);
            if ($stmt->execute()) {
                header("Location: index.php?msg=updated");
                exit;
            } else {
                $message = "Error al actualizar el módulo: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
}

// 3. LÓGICA DE PROCESAMIENTO: Eliminar Formulario
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id_del = $_GET['delete'];
    $sql_del = "DELETE FROM formularios WHERE id = ?";
    if ($stmt_del = $mysqli->prepare($sql_del)) {
        $stmt_del->bind_param("i", $id_del);
        if ($stmt_del->execute()) {
            header("Location: index.php?msg=deleted");
            exit;
        }
        $stmt_del->close();
    }
}

// Obtener todos los formularios configurados
$sql_forms = "SELECT f.*, 
              (SELECT COUNT(*) FROM formularios_campos WHERE id_formulario = f.id) as total_campos,
              (SELECT COUNT(*) FROM formularios_registros WHERE id_formulario = f.id) as total_registros
              FROM formularios f 
              ORDER BY f.fecha_creacion DESC";
$result_forms = $mysqli->query($sql_forms);

// --- VISTA ---
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <!-- Migas de Pan -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestión de Sub-Módulos</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-columns-gap"></i> Sub-Módulos Dinámicos</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevoFormulario">
            <i class="bi bi-plus-lg"></i> Nuevo Sub-Módulo
        </button>
    </div>

    <!-- Mensajes y Alertas -->
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                if($_GET['msg'] == 'saved') echo "Sub-módulo creado con éxito.";
                if($_GET['msg'] == 'updated') echo "Sub-módulo actualizado con éxito.";
                if($_GET['msg'] == 'deleted') echo "Sub-módulo eliminado.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla de Módulos -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre del Sub-Módulo</th>
                            <th>Descripción</th>
                            <th class="text-center">Campos</th>
                            <th class="text-center">Registros</th>
                            <th>Fecha de Creación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_forms && $result_forms->num_rows > 0): ?>
                            <?php while($row = $result_forms->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                                    </td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($row['descripcion']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark"><?php echo $row['total_campos']; ?> campos</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $row['total_registros']; ?> envíos</span>
                                    </td>
                                    <td><?php echo date("d/m/Y", strtotime($row['fecha_creacion'])); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-warning" title="Editar Módulo" 
                                                    onclick="abrirEditarModulo(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nombre']); ?>', '<?php echo addslashes($row['descripcion']); ?>', '<?php echo addslashes($row['datos_estaticos']); ?>')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="configurar_campos.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Configurar Campos">
                                                <i class="bi bi-gear"></i>
                                            </a>
                                            <a href="ver_registros.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-success" title="Ver Datos">
                                                <i class="bi bi-table"></i>
                                            </a>
                                            <a href="form_publico.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info" title="Ver Formulario">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmarEliminacion(<?php echo $row['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No hay sub-módulos configurados todavía.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Formulario -->
<div class="modal fade" id="modalNuevoFormulario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="save_form">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Crear Nuevo Sub-Módulo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Sub-Módulo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Soporte Técnico Nivel 2" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción o Propósito</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Explique para qué servirá este módulo..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Datos Estáticos para el Cliente</label>
                        <textarea name="datos_estaticos" class="form-control" rows="4" placeholder="Información fija que siempre se enviará por WhatsApp (ej: Pasos a seguir, horario, link general...)"></textarea>
                        <div class="form-text text-muted">Estos datos aparecerán automáticamente al compartir por WhatsApp.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Módulo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Formulario -->
<div class="modal fade" id="modalEditarFormulario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="update_form">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Editar Sub-Módulo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Sub-Módulo</label>
                        <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción o Propósito</label>
                        <textarea name="descripcion" id="edit_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Datos Estáticos para el Cliente (Al final)</label>
                        <textarea name="datos_estaticos" id="edit_datos_estaticos" class="form-control" rows="4"></textarea>
                        <div class="form-text text-muted">Esta información se mostrará al final del formulario y se incluirá en el WhatsApp.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirEditarModulo(id, nombre, desc, static) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nombre').value = nombre;
    document.getElementById('edit_descripcion').value = desc;
    document.getElementById('edit_datos_estaticos').value = static;
    
    const modal = new bootstrap.Modal(document.getElementById('modalEditarFormulario'));
    modal.show();
}

function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar Sub-Módulo?',
        text: "Se borrarán todos sus campos y los registros que contenga. ¡No podrás revertirlo!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar todo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?delete=' + id;
        }
    });
}
</script>

<?php 
require_once '../includes/footer.php'; 
ob_end_flush();
?>