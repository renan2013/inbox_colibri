<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id']; // Asumiendo que user_id está en la sesión
$can_manage_enlaces = has_permission($mysqli, 'gestionar_enlaces');

if (!$can_manage_enlaces) {
    // Si no tiene permiso para gestionar enlaces, redirigir o mostrar error
    header("Location: gestionar_enlaces.php?error=no_permission");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nombre_enlace = $_POST['nombre_enlace'] ?? '';
        $url_enlace = $_POST['url_enlace'] ?? '';
        $descripcion_enlace = $_POST['descripcion_enlace'] ?? '';
        $id_categoria = $_POST['id_categoria'] ?? null;
        if (empty($id_categoria)) {
            $id_categoria = NULL; // Explicitly set to PHP NULL
        }

        if (!empty($nombre_enlace) && !empty($url_enlace)) {
            $stmt = $mysqli->prepare("INSERT INTO enlaces (nombre_enlace, url_enlace, descripcion_enlace, id_usuario_creador, id_categoria) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $nombre_enlace, $url_enlace, $descripcion_enlace, $user_id, $id_categoria);
            
            if ($stmt->execute()) {
                header("Location: gestionar_enlaces.php?success=added");
            } else {
                header("Location: gestionar_enlaces.php?error=add_failed");
            }
            $stmt->close();
        } else {
            header("Location: gestionar_enlaces.php?error=missing_fields");
        }
    } elseif ($action === 'edit') {
        // Lógica para editar enlace (requiere un campo 'id' en el formulario)
        $id = $_POST['id'] ?? '';
        $nombre_enlace = $_POST['nombre_enlace'] ?? '';
        $url_enlace = $_POST['url_enlace'] ?? '';
        $descripcion_enlace = $_POST['descripcion_enlace'] ?? '';
        $id_categoria = $_POST['id_categoria'] ?? null;
        if (empty($id_categoria)) {
            $id_categoria = NULL; // Explicitly set to PHP NULL
        }

        if (!empty($id) && !empty($nombre_enlace) && !empty($url_enlace)) {
            $stmt = $mysqli->prepare("UPDATE enlaces SET nombre_enlace = ?, url_enlace = ?, descripcion_enlace = ?, id_categoria = ? WHERE id = ?");
            $stmt->bind_param("sssis", $nombre_enlace, $url_enlace, $descripcion_enlace, $id_categoria, $id);
            
            if ($stmt->execute()) {
                header("Location: gestionar_enlaces.php?success=edited");
            } else {
                header("Location: gestionar_enlaces.php?error=edit_failed");
            }
            $stmt->close();
        } else {
            header("Location: gestionar_enlaces.php?error=missing_fields");
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'delete') {
        $id = $_GET['id'] ?? '';

        if (!empty($id)) {
            $stmt = $mysqli->prepare("DELETE FROM enlaces WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                header("Location: gestionar_enlaces.php?success=deleted");
            } else {
                header("Location: gestionar_enlaces.php?error=delete_failed");
            }
            $stmt->close();
        } else {
            header("Location: gestionar_enlaces.php?error=missing_id");
        }
    }
}

$mysqli->close();
exit();
