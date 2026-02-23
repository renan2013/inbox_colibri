<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de crear usuarios
if(!has_permission($mysqli, 'crear_usuarios')){
    header("location: dashboard.php?error=No tienes permiso para registrar usuarios");
    exit;
}

$page_title = 'Registrar Nuevo Usuario';
require_once 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="gestionar_usuarios.php">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo Usuario</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-light">
                <div class="card-header">
                    <h3 class="text-center">Registrar Nuevo Usuario</h3>
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
                    <?php 
                    endif; 
                    if(isset($_SESSION['new_user_details'])):
                        $userDetails = $_SESSION['new_user_details'];
                    ?>
                        <div class="alert alert-success" role="alert">
                            <h4 class="alert-heading">¡Usuario Registrado Exitosamente!</h4>
                            <p>Por favor, guarde esta información para compartirla con el nuevo usuario. Esta contraseña se muestra solo una vez.</p>
                            <hr>
                            <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($userDetails['nombre'] . ' ' . $userDetails['apellidos']); ?></p>
                            <p class="mb-1"><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($userDetails['email']); ?></p>
                            <p class="mb-0"><strong>Contraseña:</strong> <?php echo htmlspecialchars($userDetails['password']); ?></p>
                        </div>
                    <?php 
                        unset($_SESSION['new_user_details']);
                    endif; 
                    ?>
                    <form action="registrar_usuario_process.php" method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombres</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="cedula" class="form-label">Cédula / Identificación</label>
                            <input type="text" name="cedula" id="cedula" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordVisibility">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="generatePasswordBtn">Generar</button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const generateBtn = document.getElementById('generatePasswordBtn');
    const togglePasswordBtn = document.getElementById('togglePasswordVisibility');
    const togglePasswordIcon = togglePasswordBtn.querySelector('i');

    generateBtn.addEventListener('click', function() {
        const generatedPassword = generateRandomPassword();
        passwordInput.value = generatedPassword;
    });

    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        if (type === 'password') {
            togglePasswordIcon.classList.remove('bi-eye');
            togglePasswordIcon.classList.add('bi-eye-slash');
        } else {
            togglePasswordIcon.classList.remove('bi-eye-slash');
            togglePasswordIcon.classList.add('bi-eye');
        }
    });

    function generateRandomPassword(length = 12) {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
        let password = "";
        for (let i = 0, n = charset.length; i < length; ++i) {
            password += charset.charAt(Math.floor(Math.random() * n));
        }
        return password;
    }
});
</script>
<?php include 'includes/footer.php'; ?>
