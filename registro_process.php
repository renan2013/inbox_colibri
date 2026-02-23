<?php
session_start();
require_once 'includes/db_connect.php';
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'registrar_usuarios')){
    header("location: dashboard.php?error=No tienes permiso para registrar usuarios");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("location: registro.php");
        exit;
    }

    // Verificar si el email ya existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $param_email);
        $param_email = $email;
        
        if ($stmt->execute()) {
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                $_SESSION['error'] = "Este correo electrónico ya está registrado.";
                header("location: registro.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            header("location: registro.php
            ");
            exit;
        }
        $stmt->close();
    }

    // Hashear la contraseña
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insertar el nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $param_nombre, $param_email, $param_hashed_password);
        
        $param_nombre = $nombre;
        $param_email = $email;
        $param_hashed_password = $hashed_password;
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            header("location: login.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            header("location: registro.php");
            exit;
        }
        $stmt->close();
    }
    
    $conn->close();
} else {
    header("location: registro.php");
    exit;
}
?>