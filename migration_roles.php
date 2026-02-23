<?php
require_once 'includes/db_connect.php';

// Desactivar temporalmente las restricciones de clave externa
$conn->query('SET foreign_key_checks = 0');

// SQL para la migración
$sql = "
-- 1. Crear la tabla de roles
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Crear la tabla de permisos
CREATE TABLE `permisos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) UNIQUE NOT NULL,
  `descripcion` TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Crear la tabla de relación rol-permiso
CREATE TABLE `rol_permisos` (
  `id_rol` INT NOT NULL,
  `id_permiso` INT NOT NULL,
  PRIMARY KEY (`id_rol`, `id_permiso`),
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_permiso`) REFERENCES `permisos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Modificar la tabla de usuarios
ALTER TABLE `usuarios`
  DROP COLUMN `rol`,
  ADD COLUMN `id_rol` INT NULL AFTER `password`,
  ADD FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`);

-- 5. Insertar roles y permisos iniciales
INSERT INTO `roles` (`nombre`) VALUES ('Administrador'), ('Gestor de Proyectos'), ('Miembro');

INSERT INTO `permisos` (`nombre`, `descripcion`) VALUES
('admin_usuarios', 'Permite crear, editar y eliminar otros usuarios.'),
('ver_dashboard_completo', 'Permite ver las estadísticas y tareas de todos los usuarios.'),
('crear_tareas', 'Permite crear nuevas tareas.'),
('asignar_tareas', 'Permite asignar tareas a otros usuarios.'),
('editar_tareas_propias', 'Permite editar las tareas asignadas a uno mismo.'),
('editar_tareas_todas', 'Permite editar cualquier tarea del sistema.'),
('eliminar_tareas', 'Permite eliminar cualquier tarea del sistema.'),
('comentar_tareas', 'Permite añadir comentarios en las tareas.'),
('gestionar_plantillas', 'Permite crear, editar y eliminar plantillas de tareas.'),
('crear_usuarios', 'Permite crear nuevos usuarios en el sistema.');

-- 6. Asignar permisos a los roles
-- Administrador (todos los permisos)
INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`) SELECT (SELECT id FROM roles WHERE nombre = 'Administrador'), id FROM permisos;

-- Gestor de Proyectos
INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`) VALUES
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'ver_dashboard_completo')),
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'crear_tareas')),
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'asignar_tareas')),
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'editar_tareas_todas')),
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'comentar_tareas')),
((SELECT id FROM roles WHERE nombre = 'Gestor de Proyectos'), (SELECT id FROM permisos WHERE nombre = 'gestionar_plantillas'));

-- Miembro
INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`) VALUES
((SELECT id FROM roles WHERE nombre = 'Miembro'), (SELECT id FROM permisos WHERE nombre = 'editar_tareas_propias')),
((SELECT id FROM roles WHERE nombre = 'Miembro'), (SELECT id FROM permisos WHERE nombre = 'comentar_tareas'));

-- 7. Asignar el rol de Administrador al usuario existente (ej. ID 1)
UPDATE `usuarios` SET `id_rol` = (SELECT id FROM roles WHERE nombre = 'Administrador') WHERE `id` = 1;
";

// Ejecutar la migración
if ($conn->multi_query($sql)) {
    echo "Migración completada exitosamente.";
} else {
    echo "Error durante la migración: " . $conn->error;
}

// Reactivar las restricciones de clave externa
$conn->query('SET foreign_key_checks = 1');

$conn->close();
?>