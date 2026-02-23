<?php
require_once 'includes/db_connect.php';

// Añadir columna de mapeo a los campos de formulario
$sql = "ALTER TABLE formularios_campos ADD COLUMN mapeo ENUM('ninguno', 'usuario', 'clave') DEFAULT 'ninguno' AFTER requerido";

if ($mysqli->query($sql)) {
    echo "Columna de mapeo añadida con éxito.";
} else {
    echo "Error o la columna ya existe: " . $mysqli->error;
}
?>