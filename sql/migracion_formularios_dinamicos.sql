-- Estructura para Formularios Dinámicos (Sub-módulos)

CREATE TABLE IF NOT EXISTS `formularios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `formularios_campos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_formulario` int(11) NOT NULL,
  `nombre_campo` varchar(100) NOT NULL,
  `tipo_campo` enum('text','textarea','number','date','select','password','email','url') NOT NULL DEFAULT 'text',
  `opciones` text DEFAULT NULL COMMENT 'Para tipos select, valores separados por coma',
  `requerido` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_formulario`) REFERENCES `formularios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `formularios_registros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_formulario` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_formulario`) REFERENCES `formularios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `formularios_valores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_registro` int(11) NOT NULL,
  `id_campo` int(11) NOT NULL,
  `valor` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_registro`) REFERENCES `formularios_registros` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_campo`) REFERENCES `formularios_campos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
