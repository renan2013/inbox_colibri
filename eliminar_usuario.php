<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de administrar usuarios
if(!has_permission($mysqli, 'admin_usuarios')){
    $_SESSION['error'] = "No tienes permiso para eliminar usuarios.";
    header("location: dashboard.php");
    exit;
}

// Validar ID de usuario
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    $_SESSION['error'] = "ID de usuario no válido para eliminar.";
    header("location: gestionar_usuarios.php");
    exit;
}
$id_usuario = trim($_GET["id"]);

// No permitir que un usuario se elimine a sí mismo
if ($id_usuario == $_SESSION['id']) {
    $_SESSION['error'] = "No puedes eliminar tu propia cuenta.";
    header("location: gestionar_usuarios.php");
    exit;
}

// Eliminar el usuario de la base de datos
$sql_delete_user = "DELETE FROM usuarios WHERE id = ?";
if($stmt_delete_user = $mysqli->prepare($sql_delete_user)){
    $stmt_delete_user->bind_param("i", $id_usuario);
    if($stmt_delete_user->execute()){
        $_SESSION['success'] = "Usuario eliminado con éxito.";
        header("location: gestionar_usuarios.php");
        exit;
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario de la base de datos.";
        header("location: gestionar_usuarios.php");
        exit;
    }
    $stmt_delete_user->close();
} else {
    $_SESSION['error'] = "Error al preparar la eliminación del usuario.";
    header("location: gestionar_usuarios.php");
    exit;
}

$mysqli->close();
?>