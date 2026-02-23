<?php
// Iniciar sesión
session_start();

// Si el usuario ya ha iniciado sesión, redirigirlo al dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Si no, redirigirlo a la página de login
header("location: login.php");
exit;
?>