<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    require_once 'includes/config.php';
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Verificar permisos para gestionar usuarios
if (!has_permission($mysqli, 'gestionar_usuarios')) {
    header("Location: dashboard.php?error=no_permission");
    exit();
}

$page_title = 'Gestionar Usuarios';

// Obtener todos los usuarios de la base de datos
$sql = "SELECT u.id, u.nombre, u.email, r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.id_rol = r.id";
$result = $mysqli->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

require_once 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestionar Usuarios</li>
        </ol>
    </nav>
    <h2>Gestionar Usuarios</h2>
    <a href="registrar_usuario.php" class="btn btn-primary mb-3">Registrar Nuevo Usuario</a>

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
    if(isset($_SESSION['password_reset_details'])):
        $resetDetails = $_SESSION['password_reset_details'];
    ?>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">¡Contraseña Restablecida Exitosamente!</h4>
            <p>Por favor, guarde esta información para compartirla con el usuario. Esta contraseña se muestra solo una vez.</p>
            <hr>
            <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($resetDetails['nombre']); ?></p>
            <p class="mb-1"><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($resetDetails['email']); ?></p>
            <p class="mb-0"><strong>Nueva Contraseña:</strong> <?php echo htmlspecialchars($resetDetails['new_password']); ?></p>
        </div>
    <?php 
        unset($_SESSION['password_reset_details']);
    endif; 
    ?>

    <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['rol_nombre']); ?></td>
                            <td>
                                <a href="editar_usuario.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                <button type="button" class="btn btn-sm btn-info" onclick="confirmarRestablecer(<?php echo $user['id']; ?>)">Restablecer Contraseña</button>
                                <form id="resetForm_<?php echo $user['id']; ?>" action="restablecer_contrasena_process.php" method="POST" style="display: none;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                </form>
                                <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $user['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            No hay usuarios registrados.
        </div>
    <?php endif; ?>
</div>

<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡Eliminarás este usuario permanentemente!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'eliminar_usuario.php?id=' + id;
        }
    });
}

function confirmarRestablecer(id) {
    Swal.fire({
        title: '¿Restablecer contraseña?',
        text: "Se generará una nueva contraseña aleatoria para este usuario.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('resetForm_' + id).submit();
        }
    });
}
</script>

<?php
$mysqli->close();
include 'includes/footer.php';
?>
