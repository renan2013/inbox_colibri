<?php
$page_title = "Gestionar Plantillas de Saludos";
require_once '../includes/header.php';
require_once '../includes/permissions.php';

// Permisos
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

// Lógica para obtener todas las plantillas con su categoría
$plantillas = [];
$sql = "SELECT p.id, p.nombre, p.ruta_imagen, c.nombre AS categoria_nombre 
        FROM saludo_plantillas p 
        JOIN saludo_categorias c ON p.categoria_id = c.id 
        ORDER BY c.nombre, p.nombre ASC";
if ($result = $mysqli->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $plantillas[] = $row;
    }
}
?>

<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gestionar Plantillas de Saludos</h3>
        <div>
            <a href="crear_plantilla.php" class="btn btn-primary">Añadir Nueva Plantilla</a>
            <a href="gestionar_categorias.php" class="btn btn-secondary">Gestionar Categorías</a>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Tabla de plantillas existentes -->
    <div class="card">
        <div class="card-header">
            Plantillas Existentes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Miniatura</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($plantillas)): ?>
                            <?php foreach ($plantillas as $plantilla): ?>
                                <tr>
                                    <td><?php echo $plantilla['id']; ?></td>
                                    <td>
                                        <img src="../<?php echo htmlspecialchars($plantilla['ruta_imagen']); ?>" alt="<?php echo htmlspecialchars($plantilla['nombre']); ?>" style="width: 100px; height: auto; border-radius: 5px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($plantilla['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($plantilla['categoria_nombre']); ?></td>
                                    <td>
                                        <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $plantilla['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay plantillas registradas.</td>
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
            text: "¡Eliminarás esta plantilla de saludo!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminar_plantilla.php?id=' + id;
            }
        });
    }
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
