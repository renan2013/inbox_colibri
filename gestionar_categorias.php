<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    require_once 'includes/config.php';
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$page_title = 'Gestionar Categorías de Enlaces';

$user_id = $_SESSION['id'];
$can_manage_enlaces = has_permission($mysqli, 'gestionar_enlaces');

// Redirigir si no tiene permiso para gestionar enlaces (incluye categorías)
if (!$can_manage_enlaces) {
    header("Location: dashboard.php?error=no_permission");
    exit();
}

$message = '';
$message_type = '';

// --- Lógica para Añadir/Editar/Eliminar Categorías ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $nombre_categoria = trim($_POST['nombre_categoria'] ?? '');
        if (!empty($nombre_categoria)) {
            $stmt = $mysqli->prepare("INSERT INTO categorias_enlaces (nombre_categoria) VALUES (?)");
            $stmt->bind_param("s", $nombre_categoria);
            if ($stmt->execute()) {
                $message = "Categoría añadida con éxito.";
                $message_type = "success";
            } else {
                $message = "Error al añadir categoría: " . $mysqli->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "El nombre de la categoría no puede estar vacío.";
            $message_type = "warning";
        }
    } elseif ($action === 'edit_category') {
        $id = $_POST['id'] ?? '';
        $nombre_categoria = trim($_POST['nombre_categoria'] ?? '');
        if (!empty($id) && !empty($nombre_categoria)) {
            $stmt = $mysqli->prepare("UPDATE categorias_enlaces SET nombre_categoria = ? WHERE id = ?");
            $stmt->bind_param("si", $nombre_categoria, $id);
            if ($stmt->execute()) {
                $message = "Categoría actualizada con éxito.";
                $message_type = "success";
            } else {
                $message = "Error al actualizar categoría: " . $mysqli->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "ID o nombre de categoría no pueden estar vacíos.";
            $message_type = "warning";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'delete_category') {
        $id = $_GET['id'] ?? '';
        if (!empty($id)) {
            $stmt = $mysqli->prepare("DELETE FROM categorias_enlaces WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Categoría eliminada con éxito.";
                $message_type = "success";
            } else {
                $message = "Error al eliminar categoría: " . $mysqli->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "ID de categoría no especificado.";
            $message_type = "warning";
        }
    }
}

// --- Obtener Categorías para mostrar ---
$categorias = [];
$sql_categorias = "SELECT id, nombre_categoria FROM categorias_enlaces ORDER BY nombre_categoria ASC";
$result_categorias = $mysqli->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

require_once 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Gestión de Categorías de Enlaces</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>" role="alert">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Añadir Nueva Categoría</button>

    <?php if (empty($categorias)): ?>
        <div class="alert alert-info" role="alert">
            No hay categorías registradas. ¡Añade una ahora!
        </div>
    <?php else: ?>
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
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                        data-id="<?php echo $categoria['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">Editar</button>
                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $categoria['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Añadir Categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="gestionar_categorias.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Añadir Nueva Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_category">
                    <div class="mb-3">
                        <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="gestionar_categorias.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Editar Categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_category">
                    <input type="hidden" name="id" id="edit_category_id">
                    <div class="mb-3">
                        <label for="edit_nombre_categoria" class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="edit_nombre_categoria" name="nombre_categoria" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nombre = button.getAttribute('data-nombre');

        var modalTitle = this.querySelector('.modal-title');
        var modalBodyInputId = this.querySelector('.modal-body #edit_category_id');
        var modalBodyInputNombre = this.querySelector('.modal-body #edit_nombre_categoria');

        modalTitle.textContent = 'Editar Categoría: ' + nombre;
        modalBodyInputId.value = id;
        modalBodyInputNombre.value = nombre;
    });
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Eliminarás esta categoría y sus enlaces asociados!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'gestionar_categorias.php?action=delete_category&id=' + id;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
