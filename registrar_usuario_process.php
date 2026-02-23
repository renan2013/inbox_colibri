<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de crear usuarios
if(!has_permission($mysqli, 'crear_usuarios')){
    $_SESSION['error'] = "No tienes permiso para registrar usuarios.";
    header("location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $cedula = trim($_POST['cedula']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($apellidos) || empty($cedula) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("location: registrar_usuario.php");
        exit;
    }

    // Obtener el ID del rol 'Miembro' por defecto
    $default_id_rol = null;
    $sql_default_rol = "SELECT id FROM roles WHERE nombre = 'Miembro'";
    if ($stmt_default_rol = $mysqli->prepare($sql_default_rol)) {
        $stmt_default_rol->execute();
        $result_default_rol = $stmt_default_rol->get_result();
        if ($row_default_rol = $result_default_rol->fetch_assoc()) {
            $default_id_rol = $row_default_rol['id'];
        }
        $stmt_default_rol->close();
    }

    if ($default_id_rol === null) {
        $_SESSION['error'] = "Error: No se pudo encontrar el rol 'Miembro' por defecto.";
        header("location: registrar_usuario.php");
        exit;
    }

    // Verificar si el email ya existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("s", $param_email);
        $param_email = $email;
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $_SESSION['error'] = "Este correo electrónico ya está registrado.";
                header("location: registrar_usuario.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            header("location: registrar_usuario.php");
            exit;
        }
        $stmt->close();
    }

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insertar el nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, apellidos, cedula, email, password, id_rol) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssssi", $param_nombre, $param_apellidos, $param_cedula, $param_email, $param_hashed_password, $default_id_rol);
        
        $param_nombre = $nombre;
        $param_apellidos = $apellidos;
        $param_cedula = $cedula;
        $param_email = $email;
        $param_hashed_password = $hashed_password;
        
        if ($stmt->execute()) {
            // Guardar los detalles del nuevo usuario en la sesión para mostrarlos una vez.
            $_SESSION['new_user_details'] = [
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'email' => $email,
                'password' => $password // La contraseña en texto plano
            ];
            header("location: registrar_usuario.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            header("location: registrar_usuario.php");
            exit;
        }
        $stmt->close();
    }
    
    $mysqli->close();
} else {
    header("location: registrar_usuario.php");
    exit;
}
?>