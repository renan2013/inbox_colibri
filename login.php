<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Colibrí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            padding: 2rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: #fff;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <img src="imgs/SVG/logo_color.svg" alt="Colibrí Logo" style="max-width: 250px; margin-bottom: 1rem;">
            <h2 class="h4 text-muted">Gestor de Proyectos</h2>
        </div>
        <p class="text-center text-muted">Ingrese sus credenciales para iniciar sesión.</p>
        
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
        <?php 
        endif; 
        if(isset($_SESSION['success'])):
        ?>
            <div class="alert alert-success" role="alert">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        <?php include 'includes/footer.php'; ?>
    </div>
</body>
</html>