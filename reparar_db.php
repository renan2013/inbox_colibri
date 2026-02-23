<?php
require_once 'includes/db_connect.php';

echo "<h2>Reparando Base de Datos</h2>";

// 1. Asegurar mapeo en formularios_campos
$sql1 = "ALTER TABLE formularios_campos ADD COLUMN mapeo ENUM('ninguno', 'usuario', 'clave') DEFAULT 'ninguno' AFTER requerido";
if ($mysqli->query($sql1)) {
    echo "<p style='color:green;'>✅ Columna 'mapeo' añadida a 'formularios_campos'.</p>";
} else {
    echo "<p style='color:orange;'>ℹ️ Columna 'mapeo' ya existía o error: " . $mysqli->error . "</p>";
}

// 2. Asegurar datos_estaticos en formularios
$sql2 = "ALTER TABLE formularios ADD COLUMN datos_estaticos TEXT DEFAULT NULL AFTER descripcion";
if ($mysqli->query($sql2)) {
    echo "<p style='color:green;'>✅ Columna 'datos_estaticos' añadida a 'formularios'.</p>";
} else {
    echo "<p style='color:orange;'>ℹ️ Columna 'datos_estaticos' ya existía o error: " . $mysqli->error . "</p>";
}

echo "<br><a href='credenciales/index.php'>Volver al Gestor de Soporte</a>";
?>