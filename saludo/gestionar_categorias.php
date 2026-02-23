<?php
$page_title = "Gestionar Categorías de Saludos";
require_once '../includes/header.php';
require_once '../includes/permissions.php';

// Permisos
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

// Lógica para obtener todas las categorías
$categorias = [];
$sql = "SELECT id, nombre FROM saludo_categorias ORDER BY nombre ASC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}
?>

<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gestionar Categorías de Saludos</h3>
        <a href="gestionar_plantillas.php" class="btn btn-secondary">Gestionar Plantillas</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar nueva categoría -->
    <div class="card mb-4">
        <div class="card-header">
            Añadir Nueva Categoría
        </div>
        <div class="card-body">
            <form action="crear_categoria_process.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                    <input type="text" class="form-control" id="nombre_categoria" name="nombre" required>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de categorías existentes -->
    <div class="card">
        <div class="card-header">
            Categorías Existentes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($categorias)): ?>
                            <?php foreach ($categorias as $categoria): ?>
                                <tr>
                                    <td><?php echo $categoria['id']; ?></td>
                                    <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                    <td>
                                        <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $categoria['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay categorías registradas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Se eliminarán todas las plantillas asociadas a esta categoría!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminar_categoria.php?id=' + id;
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
