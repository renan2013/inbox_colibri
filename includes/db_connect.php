<?php
date_default_timezone_set('America/Costa_Rica'); // Set timezone for Costa Rica
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u419870110_bpm'); // Cambia esto por tu usuario de MySQL
define('DB_PASSWORD', 'Bpm_unela_2025'); // Cambia esto por tu contraseña
define('DB_NAME', 'u419870110_bpm');

/* Intenta conectar a la base de datos */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Chequear conexión
if($mysqli === false){
    die("ERROR: No se pudo conectar. " . $mysqli->connect_error);
}

// Set MySQL connection timezone to Costa Rica (UTC-6)
$mysqli->query("SET time_zone = '-06:00';");