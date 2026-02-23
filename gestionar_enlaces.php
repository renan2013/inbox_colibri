<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Es necesario incluir config.php para poder usar BASE_URL
    require_once 'includes/config.php';
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Título de la página que se usará en header.php
$page_title = 'Gestionar Enlaces';

$user_id = $_SESSION['id'];
$can_manage_enlaces = has_permission($mysqli, 'gestionar_enlaces');

// Obtener categorías
$categorias = [];
$sql_categorias = "SELECT id, nombre_categoria FROM categorias_enlaces ORDER BY nombre_categoria ASC";
$result_categorias = $mysqli->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener enlaces
$enlaces = [];
$sql_enlaces = "SELECT e.id, e.nombre_enlace, e.descripcion_enlace, e.url_enlace, e.fecha_creacion, c.nombre_categoria 
                FROM enlaces e 
                LEFT JOIN categorias_enlaces c ON e.id_categoria = c.id 
                ORDER BY c.nombre_categoria ASC, e.nombre_enlace ASC";
$result_enlaces = $mysqli->query($sql_enlaces);
if ($result_enlaces) {
    while ($row = $result_enlaces->fetch_assoc()) {
        $enlaces[] = $row;
    }
}

// Incluir el header HTML
require_once 'includes/header.php';
// Incluir la barra de navegación
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h2>Gestión de Enlaces</h2>

    <?php if ($can_manage_enlaces): ?>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addLinkModal">Añadir Nuevo Enlace</button>
    <?php endif; ?>

    <?php if (empty($enlaces)): ?>
        <div class="alert alert-info" role="alert">
            No hay enlaces registrados. <?php if ($can_manage_enlaces) echo '¡Añade uno ahora!'; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>URL</th>
                        <th>Categoría</th>
                        <?php if ($can_manage_enlaces): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enlaces as $enlace): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($enlace['nombre_enlace']); ?></td>
                            <td><?php echo htmlspecialchars($enlace['descripcion_enlace']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($enlace['url_enlace']); ?>" target="_blank"><?php echo htmlspecialchars($enlace['url_enlace']); ?></a></td>
                            <td><?php echo htmlspecialchars($enlace['nombre_categoria'] ?: 'Sin Categoría'); ?></td>
                            <?php if ($can_manage_enlaces): ?>
                            <td>
                                <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" data-bs-target="#editLinkModal" 
                                        data-id="<?php echo $enlace['id']; ?>" 
                                        data-nombre="<?php echo htmlspecialchars($enlace['nombre_enlace']); ?>" 
                                        data-descripcion="<?php echo htmlspecialchars($enlace['descripcion_enlace']); ?>" 
                                        data-url="<?php echo htmlspecialchars($enlace['url_enlace']); ?>" 
                                        data-categoria="<?php echo htmlspecialchars($enlace['nombre_categoria']); ?>">Editar</button>
                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $enlace['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para Añadir Enlace -->
<div class="modal fade" id="addLinkModal" tabindex="-1" aria-labelledby="addLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="procesar_enlace.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLinkModalLabel">Añadir Nuevo Enlace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="nombre_enlace" class="form-label">Nombre del Enlace</label>
                        <input type="text" class="form-control" id="nombre_enlace" name="nombre_enlace" required>
                    </div>
                    <div class="mb-3">
                        <label for="url_enlace" class="form-label">URL del Enlace</label>
                        <input type="url" class="form-control" id="url_enlace" name="url_enlace" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_enlace" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_enlace" name="descripcion_enlace" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="id_categoria" name="id_categoria">
                            <option value="">Sin Categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Enlace</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Enlace -->
<div class="modal fade" id="editLinkModal" tabindex="-1" aria-labelledby="editLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="procesar_enlace.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLinkModalLabel">Editar Enlace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_nombre_enlace" class="form-label">Nombre del Enlace</label>
                        <input type="text" class="form-control" id="edit_nombre_enlace" name="nombre_enlace" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_url_enlace" class="form-label">URL del Enlace</label>
                        <input type="url" class="form-control" id="edit_url_enlace" name="url_enlace" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcion_enlace" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_descripcion_enlace" name="descripcion_enlace" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_id_categoria" class="form-label">Categoría</label>
                        <select class="form-select" id="edit_id_categoria" name="id_categoria">
                            <option value="">Sin Categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript para rellenar el modal de edición
    document.getElementById('editLinkModal').addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nombre = button.getAttribute('data-nombre');
        var descripcion = button.getAttribute('data-descripcion');
        var url = button.getAttribute('data-url');
        var categoriaNombre = button.getAttribute('data-categoria');

        var modalTitle = this.querySelector('.modal-title');
        var modalBodyInputId = this.querySelector('.modal-body #edit_id');
        var modalBodyInputNombre = this.querySelector('.modal-body #edit_nombre_enlace');
        var modalBodyInputDescripcion = this.querySelector('.modal-body #edit_descripcion_enlace');
        var modalBodyInputUrl = this.querySelector('.modal-body #edit_url_enlace');
        var modalBodySelectCategoria = this.querySelector('.modal-body #edit_id_categoria');

        modalTitle.textContent = 'Editar Enlace: ' + nombre;
        modalBodyInputId.value = id;
        modalBodyInputNombre.value = nombre;
        modalBodyInputDescripcion.value = descripcion;
        modalBodyInputUrl.value = url;

        // Reset selection
        modalBodySelectCategoria.value = "";
        // Select the correct category in the dropdown
        for (var i = 0; i < modalBodySelectCategoria.options.length; i++) {
            if (modalBodySelectCategoria.options[i].text === categoriaNombre) {
                modalBodySelectCategoria.options[i].selected = true;
                break;
            }
        }
    });
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Eliminarás este enlace!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'procesar_enlace.php?action=delete&id=' + id;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
