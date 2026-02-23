<?php
require_once 'includes/db_connect.php';

// SQL para crear la tabla adjuntos_plantillas
$sql = "
CREATE TABLE `adjuntos_plantillas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `id_plantilla` INT NOT NULL,
  `nombre_original` VARCHAR(255) NOT NULL,
  `nombre_servidor` VARCHAR(255) NOT NULL,
  `ruta_archivo` VARCHAR(255) NOT NULL,
  `tipo_mime` VARCHAR(100) DEFAULT NULL,
  `tamano` INT DEFAULT NULL,
  `id_usuario_subida` INT NOT NULL,
  `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_plantilla`) REFERENCES `tarea_plantillas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario_subida`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

// Ejecutar la migración
if ($mysqli->multi_query($sql)) {
    echo "Tabla 'adjuntos_plantillas' creada exitosamente.";
} else {
    echo "Error durante la creación de la tabla 'adjuntos_plantillas': " . $mysqli->error;
}

$mysqli->close();
?>