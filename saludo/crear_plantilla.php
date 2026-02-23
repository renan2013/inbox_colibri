<?php
$page_title = "Añadir Nueva Plantilla";
require_once '../includes/header.php';
require_once '../includes/permissions.php';

// Permisos
if (!has_permission($mysqli, 'gestionar_saludos')) {
    header("Location: ../dashboard.php");
    exit();
}

// Obtener categorías para el dropdown
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
    <h3>Añadir Nueva Plantilla</h3>
    <hr>
    
    <?php if (empty($categorias)): ?>
        <div class="alert alert-warning">
            No puedes crear plantillas porque no hay categorías. Por favor, <a href="gestionar_categorias.php">crea una categoría</a> primero.
        </div>
    <?php else: ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="crear_plantilla_process.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Plantilla</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoria_id" class="form-label">Categoría</label>
                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="imagen" class="form-label">Archivo de Imagen (JPG o PNG)</label>
                        <input class="form-control" type="file" id="imagen" name="imagen" accept="image/jpeg, image/png" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Plantilla</button>
                    <a href="gestionar_plantillas.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
