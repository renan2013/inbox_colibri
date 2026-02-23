<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "includes/db_connect.php";

echo "<h1>Aplicando cambios en la base de datos para precios de cursos...</h1>";

$queries = [
    // 1. Crear tabla de precios por nivel
    "CREATE TABLE IF NOT EXISTS precios_cursos_conesup (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nivel VARCHAR(100) NOT NULL UNIQUE,
        precio DECIMAL(10, 2) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. Insertar los niveles académicos iniciales
    "INSERT INTO precios_cursos_conesup (nivel, precio) VALUES
    ('Bachillerato', 100.00),
    ('Licenciatura', 150.00),
    ('Maestría', 200.00),
    ('Doctorado', 250.00),
    ('Técnico', 80.00)
    ON DUPLICATE KEY UPDATE nivel=nivel", // No hacer nada si ya existen

    // 3. Añadir columna de precio a la tabla de plan de estudios
    "ALTER TABLE plan_estudios ADD COLUMN precio DECIMAL(10, 2) NOT NULL DEFAULT 0.00"
];

foreach ($queries as $index => $sql) {
    echo "<p>Ejecutando consulta " . ($index + 1) . "... ";
    // La tercera consulta puede fallar si la columna ya existe, lo manejaremos.
    if ($index == 2) {
        // Primero, verificar si la columna ya existe
        $checkColumn = $mysqli->query("SHOW COLUMNS FROM plan_estudios LIKE 'precio'");
        if ($checkColumn->num_rows > 0) {
            echo "<span style='color: orange;'>La columna 'precio' ya existe en 'plan_estudios'. No se requiere acción.</span></p>";
            continue; // Saltar esta consulta
        }
    }

    if ($mysqli->query($sql) === TRUE) {
        echo "<span style='color: green;'>OK</span></p>";
    } else {
        echo "<span style='color: red;'>Error: " . $mysqli->error . "</span></p>";
    }
}

echo "<h3>Proceso de migración finalizado. Ahora puedes borrar este archivo ('migration_precios.php').</h3>";
$mysqli->close();
?>
