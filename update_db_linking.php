<?php
require_once 'includes/db_connect.php';

echo "<h2>Actualizando Estructura para Edición Dinámica</h2>";

// 1. Añadir id_formulario e id_registro a credenciales
$sql1 = "ALTER TABLE credenciales 
         ADD COLUMN id_formulario INT NULL AFTER creado_por,
         ADD COLUMN id_registro INT NULL AFTER id_formulario";

if ($mysqli->query($sql1)) {
    echo "<p style='color:green;'>✅ Columnas de vinculación añadidas a 'credenciales'.</p>";
} else {
    echo "<p style='color:orange;'>ℹ️ Las columnas ya existen o error: " . $mysqli->error . "</p>";
}

echo "<br><a href='credenciales/index.php'>Volver</a>";
?>