<?php
// Iniciar sesión
session_start();

// Incluir archivo de conexión
require_once "includes/db_connect.php";

// Verificar si el usuario ya está logueado
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

$email = $password = "";
$login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["email"]))){
        $login_err = "Por favor, ingrese su email.";
    } else{
        $email = trim($_POST["email"]);
    }

    if(empty(trim($_POST["password"]))){
        $login_err = "Por favor, ingrese su contraseña.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($login_err)){
        $sql = "SELECT id, nombre, email, password, id_rol FROM usuarios WHERE email = ?";

        if($stmt = $mysqli->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if($stmt->execute()){
                $stmt->store_result();

                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $nombre, $email, $hashed_password, $id_rol);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Regenerate session ID
                            session_regenerate_id(true);

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nombre"] = $nombre;
                            $_SESSION["email"] = $email;
                            $_SESSION["id_rol"] = $id_rol;

                            // Unset old permissions to force a reload
                            unset($_SESSION['permissions']);

                            header("location: dashboard.php");
                        } else{
                            $login_err = "La contraseña que has ingresado no es válida.";
                        }
                    }
                } else{
                    $login_err = "No se encontró ninguna cuenta con ese email.";
                }
            } else{
                $_SESSION['error'] = "¡Ups! Algo salió mal. Por favor, intente más tarde.";
                header("location: login.php");
                exit;
            }
            $stmt->close();
        }
    }
    
    if(!empty($login_err)){
        $_SESSION['error'] = $login_err;
        header("location: login.php");
        exit;
    }

    $mysqli->close();
}
?>