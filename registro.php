<?php
session_start();
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'registrar_usuarios')){
    header("location: dashboard.php?error=No tienes permiso para registrar usuarios");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="text-center">Registro de Usuario</h3>
                    </div>
                    <div class="card-body">
                        <?php 
                        session_start();
                        if(isset($_SESSION['error'])):
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>
                        <form action="registro_process.php" method="post">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Contraseña</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small>&copy; 2025 UNELA</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
