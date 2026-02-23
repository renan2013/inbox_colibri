<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/config.php'; // Asegurar que config.php se incluya al principio
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$page_title = 'Gestionar Categorías de Soporte';
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $nombre_categoria = trim($_POST['nombre'] ?? '');
        if (!empty($nombre_categoria)) {
            $stmt = $mysqli->prepare("INSERT INTO soporte_categorias (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombre_categoria);
            if ($stmt->execute()) {
                $message = "Categoría añadida con éxito.";
                $message_type = "success";
            } else {
                $message = "Error al añadir categoría: " . $mysqli->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    } elseif ($action === 'edit_category') {
        $id = $_POST['id'] ?? '';
        $nombre_categoria = trim($_POST['nombre'] ?? '');
        if (!empty($id) && !empty($nombre_categoria)) {
            $stmt = $mysqli->prepare("UPDATE soporte_categorias SET nombre = ? WHERE id = ?");
            $stmt->bind_param("si", $nombre_categoria, $id);
            if ($stmt->execute()) {
                $message = "Categoría actualizada con éxito.";
                $message_type = "success";
            } else {
                $message = "Error al actualizar categoría: " . $mysqli->error;
                $message_type = "danger";
            }
            $stmt->close();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete_category') {
    $id = $_GET['id'] ?? '';
    if (!empty($id)) {
        $stmt = $mysqli->prepare("DELETE FROM soporte_categorias WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Categoría eliminada con éxito.";
            $message_type = "success";
        } else {
            $message = "Error al eliminar categoría: " . $mysqli->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

$categorias = [];
$sql_categorias = "SELECT id, nombre FROM soporte_categorias ORDER BY nombre ASC";
$result_categorias = $mysqli->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Gestión de Categorías de Soporte</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Añadir Nueva Categoría</button>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre de Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr><td colspan="3" class="text-center">No hay categorías registradas.</td></tr>
                <?php else: ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                        data-id="<?php echo $categoria['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($categoria['nombre']); ?>">Editar</button>
                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $categoria['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><form action="gestionar_categorias_soporte.php" method="POST">
        <div class="modal-header"><h5 class="modal-title">Añadir Nueva Categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="add_category">
            <div class="mb-3"><label for="nombre" class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
    </form></div></div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content"><form action="gestionar_categorias_soporte.php" method="POST">
        <div class="modal-header"><h5 class="modal-title">Editar Categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_category">
            <input type="hidden" name="id" id="edit_category_id">
            <div class="mb-3"><label for="edit_nombre_categoria" class="form-label">Nombre</label><input type="text" class="form-control" id="edit_nombre_categoria" name="nombre" required></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary">Actualizar</button></div>
    </form></div></div>
</div>

<script>
    document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nombre = button.getAttribute('data-nombre');
        this.querySelector('.modal-body #edit_category_id').value = id;
        this.querySelector('.modal-body #edit_nombre_categoria').value = nombre;
        this.querySelector('.modal-title').textContent = 'Editar Categoría: ' + nombre;
    });
</script>

<?php
$mysqli->close();
?>
<script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Eliminarás esta categoría de soporte!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'gestionar_categorias_soporte.php?action=delete_category&id=' + id;
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>
