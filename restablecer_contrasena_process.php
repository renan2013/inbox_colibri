<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión y tiene permisos para editar usuarios
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !has_permission($mysqli, 'admin_usuarios')) {
    $_SESSION['error'] = "No tienes permiso para realizar esta acción.";
    header("Location: gestionar_usuarios.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Función para generar una contraseña aleatoria y segura
    function generateRandomPassword($length = 12) {
        $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
        $password = "";
        for ($i = 0, $n = strlen($charset); $i < $length; ++$i) {
            $password .= $charset[rand(0, $n - 1)];
        }
        return $password;
    }

    // Generar la nueva contraseña
    $new_password_plain = generateRandomPassword();
    $new_password_hashed = password_hash($new_password_plain, PASSWORD_BCRYPT);

    // Actualizar la contraseña en la base de datos
    $sql = "UPDATE usuarios SET password = ? WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("si", $new_password_hashed, $user_id);
        
        if ($stmt->execute()) {
            // Obtener el email del usuario para mostrarlo en el mensaje
            $sql_user = "SELECT nombre, email FROM usuarios WHERE id = ?";
            if ($stmt_user = $mysqli->prepare($sql_user)) {
                $stmt_user->bind_param("i", $user_id);
                $stmt_user->execute();
                $result_user = $stmt_user->get_result();
                $user = $result_user->fetch_assoc();
                
                // Guardar los detalles en la sesión para mostrarlos una vez
                $_SESSION['password_reset_details'] = [
                    'nombre' => $user['nombre'],
                    'email' => $user['email'],
                    'new_password' => $new_password_plain
                ];
                
                $stmt_user->close();
            }
        } else {
            $_SESSION['error'] = "Error al restablecer la contraseña.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error en la preparación de la consulta.";
    }
}

$mysqli->close();
header("Location: gestionar_usuarios.php");
exit();
?>