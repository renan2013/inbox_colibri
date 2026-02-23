<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

$page_title = "Añadir Nuevo Nivel de Precio";
require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>precios_conesup/index.php">Gestionar Precios de Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Añadir Nuevo Nivel de Precio</li>
        </ol>
    </nav>
</div>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Añadir Nuevo Nivel de Precio</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="crear_precio_process.php" method="POST">
                        <div class="mb-3">
                            <label for="nivel" class="form-label">Nombre del Nivel</label>
                            <input type="text" name="nivel" id="nivel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Guardar Nivel</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
