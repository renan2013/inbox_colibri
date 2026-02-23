<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso
if(!has_permission($mysqli, 'gestionar_plantillas')){ // Usando un permiso existente temporalmente
    header("location: dashboard.php?error=No tienes permiso para crear etiquetas");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nueva Etiqueta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Crear Nueva Etiqueta</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php 
                        if(isset($_SESSION['error'])):
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        <form action="crear_etiqueta_process.php" method="post">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre de la Etiqueta</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Crear Etiqueta</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>