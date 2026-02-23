<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de administrar usuarios
if(!has_permission($mysqli, 'admin_usuarios')){
    header("location: dashboard.php?error=No tienes permiso para editar usuarios");
    exit;
}

// Validar ID de usuario
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: gestionar_usuarios.php?error=ID de usuario no válido");
    exit;
}
$id_usuario = trim($_GET["id"]);

// Obtener datos del usuario existente
$sql_user_existente = "SELECT id, nombre, cedula, email, id_rol FROM usuarios WHERE id = ?";
if($stmt_user_existente = $mysqli->prepare($sql_user_existente)){
    $stmt_user_existente->bind_param("i", $id_usuario);
    $stmt_user_existente->execute();
    $result_user_existente = $stmt_user_existente->get_result();
    if($result_user_existente->num_rows == 1){
        $user_existente = $result_user_existente->fetch_assoc();
        $nombre = $user_existente['nombre'];
        $cedula = $user_existente['cedula'];
        $email = $user_existente['email'];
        $id_rol_existente = $user_existente['id_rol'];
    } else {
        header("location: gestionar_usuarios.php?error=Usuario no encontrado");
        exit;
    }
    $stmt_user_existente->close();
} else {
    die("Error al preparar la consulta de usuario existente.");
}

// Obtener roles para el dropdown
$sql_roles = "SELECT id, nombre FROM roles ORDER BY nombre";
$result_roles = $mysqli->query($sql_roles);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - BPM Unela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Editar Usuario</h2>
    <p>Modifique los detalles del usuario.</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="editar_usuario_process.php?id=<?php echo $id_usuario; ?>" method="post">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre Completo</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" required>
        </div>
        <div class="mb-3">
            <label for="cedula" class="form-label">Cédula / Identificación</label>
            <input type="text" name="cedula" id="cedula" class="form-control" value="<?php echo htmlspecialchars($cedula ?? ''); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        <div class="mb-3">
            <label for="id_rol" class="form-label">Rol</label>
            <select name="id_rol" id="id_rol" class="form-select" required>
                <?php
                if ($result_roles->num_rows > 0) {
                    while($rol = $result_roles->fetch_assoc()) {
                        $selected = ($rol['id'] == $id_rol_existente) ? 'selected' : '';
                        echo '<option value="' . $rol['id'] . '" ' . $selected . '>' . htmlspecialchars($rol['nombre']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="gestionar_usuarios.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>