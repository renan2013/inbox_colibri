<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    require_once '../includes/config.php';
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$user_id = $_SESSION['id'];
$can_manage_claves = (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1);

if (!$can_manage_claves) {
    header("Location: " . BASE_URL . "dashboard.php?error=no_permission");
    exit();
}

$page_title = 'Gestionar Claves';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_clave') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = trim($_POST['clave'] ?? '');
        if (!empty($nombre) && !empty($usuario) && !empty($clave)) {
            $stmt = $mysqli->prepare("INSERT INTO claves (nombre, descripcion, usuario, clave) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $descripcion, $usuario, $clave);
            if ($stmt->execute()) {
                $message = "Clave añadida con éxito."; $message_type = "success";
            } else {
                $message = "Error al añadir la clave: " . $mysqli->error; $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Nombre, usuario y clave no pueden estar vacíos."; $message_type = "warning";
        }
    } elseif ($action === 'edit_clave') {
        $id = $_POST['id'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = trim($_POST['clave'] ?? '');
        if (!empty($id) && !empty($nombre) && !empty($usuario) && !empty($clave)) {
            $stmt = $mysqli->prepare("UPDATE claves SET nombre = ?, descripcion = ?, usuario = ?, clave = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $descripcion, $usuario, $clave, $id);
            if ($stmt->execute()) {
                $message = "Clave actualizada con éxito."; $message_type = "success";
            } else {
                $message = "Error al actualizar la clave: " . $mysqli->error; $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "ID, nombre, usuario y clave no pueden estar vacíos."; $message_type = "warning";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_clave') {
    $id = $_GET['id'] ?? '';
    if (!empty($id)) {
        $stmt = $mysqli->prepare("DELETE FROM claves WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Clave eliminada con éxito."; $message_type = "success";
        } else {
            $message = "Error al eliminar la clave: " . $mysqli->error; $message_type = "danger";
        }
        $stmt->close();
    }
}

$claves = [];
$sql_claves = "SELECT id, nombre, descripcion, usuario, clave FROM claves ORDER BY nombre ASC";
$result_claves = $mysqli->query($sql_claves);
if ($result_claves) {
    while ($row = $result_claves->fetch_assoc()) {
        $claves[] = $row;
    }
}

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Gestión de Claves</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addClaveModal">Añadir Nueva Clave</button>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr><th>Nombre</th><th>Descripción</th><th>Usuario</th><th>Clave</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php if (empty($claves)): ?>
                    <tr><td colspan="5" class="text-center">No hay claves registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($claves as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($item['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($item['clave']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editClaveModal"
                                        data-id="<?php echo $item['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($item['nombre']); ?>"
                                        data-descripcion="<?php echo htmlspecialchars($item['descripcion']); ?>"
                                        data-usuario="<?php echo htmlspecialchars($item['usuario']); ?>"
                                        data-clave="<?php echo htmlspecialchars($item['clave']); ?>">Editar</button>
                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $item['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Añadir Clave -->
<div class="modal fade" id="addClaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><form action="gestionar_claves.php" method="POST">
        <div class="modal-header"><h5 class="modal-title">Añadir Nueva Clave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="add_clave">
            <div class="mb-3"><label class="form-label">Nombre de la Plataforma/Servicio</label><input type="text" class="form-control" name="nombre" required></div>
            <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="3"></textarea></div>
            <div class="mb-3"><label class="form-label">Usuario</label><input type="text" class="form-control" name="usuario" required></div>
            <div class="mb-3"><label class="form-label">Clave</label><input type="text" class="form-control" name="clave" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form></div></div>
</div>

<!-- Modal para Editar Clave -->
<div class="modal fade" id="editClaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><form action="gestionar_claves.php" method="POST">
        <div class="modal-header"><h5 class="modal-title">Editar Clave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_clave">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" id="edit_nombre" name="nombre" required></div>
            <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea></div>
            <div class="mb-3"><label class="form-label">Usuario</label><input type="text" class="form-control" id="edit_usuario" name="usuario" required></div>
            <div class="mb-3"><label class="form-label">Clave</label><input type="text" class="form-control" id="edit_clave" name="clave" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary">Actualizar</button></div>
    </form></div></div>
</div>

<script>
    document.getElementById('editClaveModal').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        this.querySelector('.modal-body #edit_id').value = button.getAttribute('data-id');
        this.querySelector('.modal-body #edit_nombre').value = button.getAttribute('data-nombre');
        this.querySelector('.modal-body #edit_descripcion').value = button.getAttribute('data-descripcion');
        this.querySelector('.modal-body #edit_usuario').value = button.getAttribute('data-usuario');
        this.querySelector('.modal-body #edit_clave').value = button.getAttribute('data-clave');
    });
</script>

<script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Eliminarás esta clave!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'gestionar_claves.php?action=delete_clave&id=' + id;
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>