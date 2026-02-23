<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once "includes/db_connect.php";

echo "<h1>Inicializando Tablas del Sistema Académico...</h1>";

$queries = [
    // 1. Tabla de Sílabos (Cabecera)
    "CREATE TABLE IF NOT EXISTS silabos (
        id_silabo INT AUTO_INCREMENT PRIMARY KEY,
        id_plan INT NOT NULL,
        id_profesor INT NOT NULL,
        descripcion TEXT,
        objetivo_general TEXT,
        objetivos_especificos TEXT,
        metodologia TEXT,
        contenidos TEXT, 
        cronograma TEXT,
        bibliografia TEXT,
        modalidad VARCHAR(50),
        horario VARCHAR(100),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_silabo (id_plan, id_profesor),
        FOREIGN KEY (id_plan) REFERENCES plan_estudios(id_plan) ON DELETE CASCADE,
        FOREIGN KEY (id_profesor) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 2. Tabla de Rubros de Evaluación
    "CREATE TABLE IF NOT EXISTS silabo_evaluacion (
        id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
        id_silabo INT NOT NULL,
        rubro VARCHAR(100),
        porcentaje INT,
        FOREIGN KEY (id_silabo) REFERENCES silabos(id_silabo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 3. Tabla de Cronograma Detallado
    "CREATE TABLE IF NOT EXISTS silabo_cronograma (
        id_cronograma INT AUTO_INCREMENT PRIMARY KEY,
        id_silabo INT NOT NULL,
        semana VARCHAR(50),
        fecha VARCHAR(100),
        actividad TEXT,
        FOREIGN KEY (id_silabo) REFERENCES silabos(id_silabo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 4. Tabla de Contenidos Detallados (La que faltaba)
    "CREATE TABLE IF NOT EXISTS silabo_contenidos (
        id_contenido INT AUTO_INCREMENT PRIMARY KEY,
        id_silabo INT NOT NULL,
        unidad VARCHAR(100),
        tema VARCHAR(255),
        subtemas TEXT,
        FOREIGN KEY (id_silabo) REFERENCES silabos(id_silabo) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // 5. Tabla de Notas Parciales
    "CREATE TABLE IF NOT EXISTS notas_rubros (
        id_nota_rubro INT AUTO_INCREMENT PRIMARY KEY,
        id_matricula INT NOT NULL, 
        id_rubro INT NOT NULL,     
        calificacion_obtenida DECIMAL(5,2) DEFAULT 0,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_matricula) REFERENCES matriculas(id_matricula) ON DELETE CASCADE,
        FOREIGN KEY (id_rubro) REFERENCES silabo_evaluacion(id_evaluacion) ON DELETE CASCADE,
        UNIQUE KEY unique_nota (id_matricula, id_rubro)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($queries as $index => $sql) {
    echo "<p>Ejecutando consulta " . ($index + 1) . "... ";
    if ($mysqli->query($sql) === TRUE) {
        echo "<span style='color: green;'>OK</span></p>";
    } else {
        echo "<span style='color: red;'>Error: " . $mysqli->error . "</span></p>";
    }
}

echo "<h3>Proceso finalizado. Puedes borrar este archivo.</h3>";
$mysqli->close();
?>