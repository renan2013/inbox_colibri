<?php
// Script para añadir columna de datos estáticos
require_once 'includes/db_connect.php';

$sql = "ALTER TABLE formularios ADD COLUMN datos_estaticos TEXT DEFAULT NULL AFTER descripcion";

if ($mysqli->query($sql)) {
    echo "Columna 'datos_estaticos' añadida con éxito.";
} else {
    echo "Error o la columna ya existe: " . $mysqli->error;
}
?>