<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Proteger la página: solo para usuarios con permiso de administrar usuarios
if(!has_permission($mysqli, 'admin_usuarios')){
    $_SESSION['error'] = "No tienes permiso para editar usuarios.";
    header("location: dashboard.php");
    exit;
}

// Validar ID de usuario
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    $_SESSION['error'] = "ID de usuario no válido.";
    header("location: gestionar_usuarios.php");
    exit;
}
$id_usuario = trim($_GET["id"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $cedula = trim($_POST['cedula']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Puede estar vacío
    $id_rol = trim($_POST['id_rol']);

    if (empty($nombre) || empty($cedula) || empty($email) || empty($id_rol)) {
        $_SESSION['error'] = "Nombre, cédula, email y rol son obligatorios.";
        header("location: editar_usuario.php?id=" . $id_usuario);
        exit;
    }

    // Verificar si el email ya existe para otro usuario
    $sql_check_email = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    if ($stmt_check_email = $mysqli->prepare($sql_check_email)) {
        $stmt_check_email->bind_param("si", $param_email, $param_id_usuario);
        $param_email = $email;
        $param_id_usuario = $id_usuario;
        
        if ($stmt_check_email->execute()) {
            $stmt_check_email->store_result();
            
            if ($stmt_check_email->num_rows == 1) {
                $_SESSION['error'] = "Este correo electrónico ya está registrado por otro usuario.";
                header("location: editar_usuario.php?id=" . $id_usuario);
                exit;
            }
        }
        $stmt_check_email->close();
    }

    // Construir la consulta de actualización
    $sql_update = "UPDATE usuarios SET nombre = ?, cedula = ?, email = ?, id_rol = ?";
    $params = "sssi";
    $values = [&$nombre, &$cedula, &$email, &$id_rol];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql_update .= ", password = ?";
        $params .= "s";
        $values[] = &$hashed_password;
    }

    $sql_update .= " WHERE id = ?";
    $params .= "i";
    $values[] = &$id_usuario;

    if ($stmt_update = $mysqli->prepare($sql_update)) {
        // Usar call_user_func_array para bind_param con un número variable de argumentos
        call_user_func_array([$stmt_update, 'bind_param'], array_merge([$params], $values));
        
        if ($stmt_update->execute()) {
            $_SESSION['success'] = "Usuario actualizado exitosamente.";
            header("location: gestionar_usuarios.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al actualizar el usuario. Por favor, inténtalo de nuevo más tarde.";
            header("location: editar_usuario.php?id=" . $id_usuario);
            exit;
        }
        $stmt_update->close();
    }
    
    $mysqli->close();
} else {
    header("location: gestionar_usuarios.php");
    exit;
}
?>