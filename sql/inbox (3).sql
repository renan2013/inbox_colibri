-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 23-02-2026 a las 19:18:25
-- Versión del servidor: 11.8.3-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u419870110_bpm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adjuntos`
--

CREATE TABLE `adjuntos` (
  `id` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_servidor` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `tamano` int(11) DEFAULT NULL,
  `id_usuario_subida` int(11) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `adjuntos`
--

INSERT INTO `adjuntos` (`id`, `id_tarea`, `nombre_original`, `nombre_servidor`, `ruta_archivo`, `tipo_mime`, `tamano`, `id_usuario_subida`, `fecha_subida`) VALUES
(28, 48, 'ORGANIGRAMA-NOMBRAMIENTOS\'25 (oficial) -21-08-25.docx.pdf', 'temp_68b86e14e21a6.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68b86e14e21a6.pdf', 'application/pdf', 550819, 1, '2025-09-03 16:34:32'),
(29, 47, 'Guía Práctica para Docentes_ .pdf', 'temp_68b9dc64b8988.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68b9dc64b8988.pdf', 'application/pdf', 593308, 1, '2025-09-04 18:37:37'),
(30, 58, 'Silabo_Metodos_Intervencion_enriquecido.docx ENVIAR A VICERRECTORIA 11-09-25.docx', 'temp_68d5a861c1689.docx', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68d5a861c1689.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 33893, 4, '2025-09-25 20:39:07'),
(31, 58, 'Primera Semana Moode TB 691.png', 'temp_68d5a9b51e1d4.png', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68d5a9b51e1d4.png', 'image/png', 124051, 4, '2025-09-25 20:44:41'),
(32, 58, 'NavigacionCarpetasDrive.png', 'temp_68d5b169a2e07.png', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68d5b169a2e07.png', 'image/png', 102835, 4, '2025-09-25 21:17:40'),
(33, 59, 'quiz.jpg', 'temp_68dc5d576740b.jpg', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68dc5d576740b.jpg', 'image/jpeg', 15172, 1, '2025-09-30 22:44:48'),
(34, 60, 'PASOS -- ACCESO a TODOS LOS CURSOS -  ADMINISTRADORES de MOODLE.pdf', 'temp_68dd8694915f7.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68dd8694915f7.pdf', 'application/pdf', 294464, 4, '2025-10-01 19:52:59'),
(35, 61, 'Como chequear REGISTROS de actividad.pdf', 'temp_68dd8c5cf3fe0.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68dd8c5cf3fe0.pdf', 'application/pdf', 195193, 4, '2025-10-01 20:17:39'),
(36, 63, 'BROCHUR UNELA PDF.pdf', 'temp_68e022e253b91.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68e022e253b91.pdf', 'application/pdf', 2449274, 3, '2025-10-03 19:24:26'),
(37, 64, 'Cuatrimestre - hoja de calculo.png', 'temp_68f11d57d1cfb.png', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_68f11d57d1cfb.png', 'image/png', 219021, 4, '2025-10-16 16:29:23'),
(38, 70, 'DOCTORADO - PROGRAMACION de CURSOS - Hojas de cálculo de Google (2).pdf', 'temp_6914c2a983711.pdf', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_6914c2a983711.pdf', 'application/pdf', 245242, 4, '2025-11-12 17:24:16'),
(39, 72, 'PLANIFICACIÓN CAMPAÑA PUBLICITARIA UNELA RENÁN.docx', 'temp_693b07d7e3e84.docx', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_693b07d7e3e84.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 17665, 3, '2025-12-11 18:12:02'),
(40, 72, 'I Cuatrimestre 2026.jpg', 'temp_693b080ab7f63.jpg', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_693b080ab7f63.jpg', 'image/jpeg', 657155, 3, '2025-12-11 18:12:02'),
(41, 72, 'MBT-I CUT 2026.jpg', 'temp_693b084ac911b.jpg', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_693b084ac911b.jpg', 'image/jpeg', 139266, 3, '2025-12-11 18:12:02'),
(42, 72, 'TÉC-I Cuatrimestre 2026.jpg', 'temp_693b08740e6fb.jpg', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_693b08740e6fb.jpg', 'image/jpeg', 117919, 3, '2025-12-11 18:12:02'),
(43, 72, 'MOF-I CUATRIMESTRE 2026.jpg', 'temp_693b0886eb01e.jpg', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_693b0886eb01e.jpg', 'image/jpeg', 97546, 3, '2025-12-11 18:12:02'),
(44, 73, 'SESIONES ZOOM 2026 I Cuatrimestre.xlsx', 'temp_696157d409d94.xlsx', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_696157d409d94.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 34000, 1, '2026-01-09 19:32:46'),
(45, 73, '(2nda edición SESIONES ZOOM 2026 I Cuatrimestre.xlsx', 'temp_6967cb20ad2ca.xlsx', '/home/u419870110/domains/unela.org/public_html/bpm_unela/uploads/temp_6967cb20ad2ca.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 18732, 4, '2026-01-14 16:58:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adjuntos_plantillas`
--

CREATE TABLE `adjuntos_plantillas` (
  `id` int(11) NOT NULL,
  `id_plantilla` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_servidor` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) DEFAULT NULL,
  `tamano` int(11) DEFAULT NULL,
  `id_usuario_subida` int(11) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `arreglos_pago`
--

CREATE TABLE `arreglos_pago` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `monto_total_acordado` decimal(10,2) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','incumplido','finalizado','cancelado') NOT NULL DEFAULT 'activo',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id_asistencia` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `estado` enum('presente','ausente','tarde','justificado') DEFAULT 'presente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id_asistencia`, `id_tarea`, `id_estudiante`, `estado`) VALUES
(1, 74, 3, 'presente'),
(2, 74, 1, 'presente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `boletas`
--

CREATE TABLE `boletas` (
  `id` int(11) NOT NULL,
  `numero_boleta` varchar(20) DEFAULT NULL,
  `periodo` varchar(100) DEFAULT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_creador` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `saldo_pendiente` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_pagado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','pagada','pago_parcial','en_arreglo','anulada') NOT NULL DEFAULT 'pendiente',
  `ruta_firma` varchar(255) DEFAULT NULL,
  `ruta_pdf` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `boletas`
--

INSERT INTO `boletas` (`id`, `numero_boleta`, `periodo`, `id_estudiante`, `id_creador`, `total`, `saldo_pendiente`, `monto_pagado`, `estado`, `ruta_firma`, `ruta_pdf`, `fecha_creacion`) VALUES
(11, 'B-000011', NULL, 1, 1, 100000.00, 100000.00, 0.00, 'pendiente', '', 'uploads/boletas_generadas/boleta_b_000011.pdf', '2026-02-10 21:00:01'),
(14, 'B-000014', 'I Cuatrimestre 2026', 3, 1, 100000.00, 100000.00, 0.00, 'pendiente', '', 'uploads/boletas_generadas/boleta_b_000014.pdf', '2026-02-10 21:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_enlaces`
--

CREATE TABLE `categorias_enlaces` (
  `id` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias_enlaces`
--

INSERT INTO `categorias_enlaces` (`id`, `nombre_categoria`) VALUES
(4, 'Gestor de sylabus'),
(1, 'Inteligencia Artificial'),
(2, 'Plataforma'),
(3, 'Recurso');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `claves`
--

CREATE TABLE `claves` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `usuario` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `claves`
--

INSERT INTO `claves` (`id`, `nombre`, `descripcion`, `usuario`, `clave`, `fecha_creacion`) VALUES
(1, 'Cuenta gmail', 'Cuenta de correo de DTI Unela.', 'dti@unela.ac.cr', 'Dtiunela2025', '2025-10-22 17:57:44'),
(2, 'Zoom Administrativo', 'Cuenta de zoom exclusivo para administración', 'unelazoom@gmail.com', 'Unelazoom2025!', '2025-10-23 05:22:44'),
(3, 'NIC', 'Dashboard del dominio unela.ac.cr', 'renangalvan@gmail.com', 'Unelanic2025', '2025-10-23 05:30:47'),
(4, 'Hostinger Unela', 'Hosting de Unela', 'silva.mer76@gmail.com', 'Unela_host_2025', '2025-10-23 05:31:44'),
(5, 'Classbox', 'Administrador de contenidos de website unela.ac.c.r', 'merlin', 'merlin2025!', '2025-10-23 05:34:59'),
(6, 'Clave Router Extensor', '', '.', '.', '2025-10-23 05:52:04'),
(7, 'Moodle Administrador', 'Nueva instalación de la plataforma Moodle para Unela 2026\r\nCorreo de respaldo\r\nrenangalvan@gmail.com \r\nBase de datos Merlin2026!', 'administrador_unela', 'Cambio2026!', '2025-10-23 05:52:30'),
(8, 'Wifi Unela', 'Usuario y clave de acceso', 'Unela', 'Accesounela', '2025-10-27 20:43:57'),
(9, 'Factura electrónica Unela', 'Asociación Universidad Evangélica de las Américas, cédula jurídica 3-002-066646, correo: merlin@unela.ac.cr Teléfono: 22217870, San José, Distrito Hospital, Calle 2, Avenida 14-16.', '.', '.', '2025-10-31 18:54:17'),
(10, 'Cuenta de IA de UNela', 'Aquí está la cuenta independiente creada en Gmail para usar la IA de Gemini Pro. Si pide confirmación, lo enviará al correo de renangalvan@gmail.com o al telf. 87777849', 'recursounela@gmail.com', 'Iaunela2026', '2025-12-11 17:26:42'),
(12, 'Instagram', 'Confirmación al teléfono 8722-0999 de UNELA', 'unela2026', 'Patasdepollo25', '2025-12-11 21:10:49'),
(13, 'Facebook', 'Pagina nueva creada por Vermilium', 'cris@unela.ac.cr', 'UNELA25_sj_cr20', '2026-02-18 18:12:43'),
(14, 'cris@unela.ac.cr (Google Workspace para Educación UNELA)', 'La cuenta en la cual hay mucha bibliografía digital', 'cris@unela.ac.cr', 'Qbrochet7ku#89', '2026-02-19 17:25:06'),
(15, 'registro@unela.ac.cr (Google Workspace para Educación UNELA)', 'Tiene 2- factores de autenticación, debido al contenido : datos estudiantiles, etc.', 'registro@unela.ac.cr', 'Pguade6bye#679', '2026-02-19 17:27:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `id_tarea`, `id_usuario`, `comentario`, `fecha_creacion`) VALUES
(5, 32, 1, 'Espero que esata reunión sea el inicio de una nueva etapa en el desarrollo de los procesos de UNELA, cada aporte será bienvenido.', '2025-08-30 17:19:39'),
(6, 32, 4, 'Aqui hay enlace -  Informe hecho el año pasado: (copy/paste ....)\r\nhttps://docs.google.com/document/d/1WPrnmLCkDH518GKwfoj6Q3b5BxyeGqUA5wILGLhJl9s/edit?usp=sharing', '2025-08-31 11:40:25'),
(8, 40, 1, 'Renan no olvides preguntar x cosas', '2025-09-03 02:29:12'),
(9, 47, 4, 'Me parece buenos los consejos para la administración de las herramientas en el momento de la instrucción.   Thomas', '2025-09-04 19:52:21'),
(10, 72, 4, 'Les felicito por el excelente trabajo en el área publicitario.\r\n Posiblemente yo puedo ayudar con un granito de arena en la parte de un posible sub-estrategia que se centra en el uso de datos que son direcciones de correos electrónicos.  ¿Valdría la pena entrar en esto de corrreos electrónicos?  ¿Es viable? Es eficiente? o no?', '2025-12-19 16:09:00'),
(11, 73, 4, 'Mensaje para Renan:\r\n Le falta crear usario:\r\n 11360-0900	Henry Martínez	Jason	85484679	jahemar88@gmail.com         - pues, quise matricularle en TB 660 con José Flores, pero no está en sistema Moodle', '2026-01-13 16:20:32'),
(12, 73, 4, 'La hoja de cálculo (2nda edición Sesiones Zoom 2026 - tiene los enlaces de Zoom para a todos los cursos.', '2026-01-14 17:02:34'),
(13, 73, 4, 'La hoja de cálculo : 2nda edicición SESIONES ZOOM 2026  - tiene los enlaces de todos los cursos.', '2026-01-14 17:04:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `credenciales`
--

CREATE TABLE `credenciales` (
  `id_credencial` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `link_acceso` text DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `datos_link` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `credenciales`
--

INSERT INTO `credenciales` (`id_credencial`, `usuario`, `clave`, `link_acceso`, `tipo`, `fecha`, `datos_link`, `creado_por`) VALUES
(28, 'haroldsegura', 'Harold2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-01-22 15:44:05', 'Debe ingresar al link y cambiar su contraseña y volver a ingresar.', 1),
(29, 'josueballestero', 'Josue2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-01-22 16:43:37', 'Debe cambiar esta contraseña provisional y guardarla.', 1),
(30, 'albertomadrigal', 'Alberto2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-01-22 16:59:40', 'Debe cambiar esta contraseña y volver a ingresar con las nuevas.', 1),
(31, 'pgilberto', 'Pgilberto2026!', 'http://unela.ac.cr/virtual', 'Cuenta Nueva', '2026-01-23 00:04:59', 'La cuenta se le envio a Gilberto@unela.ac.cr', 1),
(32, 'pgilberto', 'Gil2026!', 'http://unela.ac.cr/virtual', 'Cuenta Nueva', '2026-01-23 04:38:10', 'Cierre su sesión actual y podrá ingresar con este nombre de usuario y contraseña', 1),
(34, 'andreamonge', 'Andrea2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-02-04 15:49:05', '', 1),
(36, 'elizabethquiros', 'Elizabeth2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-02-11 18:47:10', '', 1),
(37, 'danilolopez', 'Danilo2026!', 'http://unela.ac.cr/virtual', 'Cambio de contraseña', '2026-02-16 16:37:42', '', 1),
(38, 'emilioazcarraga', '8567486558', 'http://unela.ac.cr/virtual', 'Cuenta Nueva', '2026-02-23 17:10:34', 'por favor guarde sus claves en un lugar seguro', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas_arreglo`
--

CREATE TABLE `cuotas_arreglo` (
  `id` int(11) NOT NULL,
  `arreglo_id` int(11) NOT NULL,
  `monto_cuota` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('pendiente','pagada') NOT NULL DEFAULT 'pendiente',
  `pago_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos_activos`
--

CREATE TABLE `cursos_activos` (
  `id_curso_activo` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `periodo` varchar(100) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_final` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_final` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cursos_activos`
--

INSERT INTO `cursos_activos` (`id_curso_activo`, `id_plan`, `id_profesor`, `periodo`, `fecha_inicio`, `fecha_final`, `hora_inicio`, `hora_final`) VALUES
(8, 42, 9, 'I Cuatrimestre 2026', '2026-01-06', '2026-04-28', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enlaces`
--

CREATE TABLE `enlaces` (
  `id` int(11) NOT NULL,
  `nombre_enlace` varchar(255) NOT NULL,
  `descripcion_enlace` text DEFAULT NULL,
  `url_enlace` varchar(255) NOT NULL,
  `id_usuario_creador` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `enlaces`
--

INSERT INTO `enlaces` (`id`, `nombre_enlace`, `descripcion_enlace`, `url_enlace`, `id_usuario_creador`, `id_categoria`, `fecha_creacion`) VALUES
(3, 'Unela Virtual', 'Plataforma virtual de Unela', 'https://unela.ac.cr/virtual', 1, 2, '2025-08-30 21:29:29'),
(4, 'Classbox', 'Administrador de contenidos de la página web', 'https://unela.ac.cr/classbox/auth/login.php', 1, 2, '2025-08-30 21:36:12'),
(5, 'Eduaide', 'Maravillosas herramientas para generar material de estudio', 'https://www.eduaide.ai/', 1, 3, '2025-09-02 16:41:34'),
(6, 'Draw.io', 'Creador de todo tipo de diagramas', 'https://app.diagrams.net/', 1, NULL, '2025-10-15 18:09:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id`, `nombre`, `fecha_creacion`) VALUES
(1, 'Cursos Moodle - III Cuatrimestre 2025', '2025-09-25 16:10:07'),
(2, 'Reunión DTI', '2025-09-25 16:30:46'),
(3, 'Estrategias / tecnicas de montar materiales, etc en Moodle', '2025-09-25 20:27:47'),
(4, 'PUBLICIDAD', '2025-10-16 18:17:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `expedientes_digitales`
--

CREATE TABLE `expedientes_digitales` (
  `id_expediente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `grado_a_matricular` varchar(50) DEFAULT NULL,
  `especialidad_deseada` varchar(255) DEFAULT NULL,
  `genero` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `lugar_nacimiento` varchar(255) DEFAULT NULL,
  `nacionalidad` varchar(100) DEFAULT NULL,
  `cedula_residencia` varchar(50) DEFAULT NULL,
  `estado_civil` varchar(50) DEFAULT NULL,
  `domicilio_direccion` text DEFAULT NULL,
  `domicilio_provincia` varchar(100) DEFAULT NULL,
  `domicilio_canton` varchar(100) DEFAULT NULL,
  `domicilio_distrito` varchar(100) DEFAULT NULL,
  `contacto_tel_habitacion` varchar(50) DEFAULT NULL,
  `contacto_tel_celular` varchar(50) DEFAULT NULL,
  `contacto_otro_emergencias` varchar(50) DEFAULT NULL,
  `procedencia_secundaria_institucion` varchar(255) DEFAULT NULL,
  `procedencia_secundaria_ano_graduacion` varchar(4) DEFAULT NULL,
  `procedencia_secundaria_grado_obtenido` varchar(255) DEFAULT NULL,
  `procedencia_universidad` varchar(255) DEFAULT NULL,
  `procedencia_universidad_ano_graduacion` varchar(4) DEFAULT NULL,
  `procedencia_universidad_grado_obtenido` varchar(50) DEFAULT NULL,
  `procedencia_universidad_especialidad` varchar(255) DEFAULT NULL,
  `laboral_institucion` varchar(255) DEFAULT NULL,
  `laboral_fecha_ingreso` date DEFAULT NULL,
  `laboral_puesto` varchar(255) DEFAULT NULL,
  `laboral_telefono` varchar(50) DEFAULT NULL,
  `laboral_extension` varchar(20) DEFAULT NULL,
  `laboral_fax` varchar(50) DEFAULT NULL,
  `laboral_correo_electronico` varchar(255) DEFAULT NULL,
  `registro_fecha_matricula` date DEFAULT NULL,
  `registro_doc_titulo_sec` tinyint(1) DEFAULT 0,
  `registro_doc_titulo_univ` tinyint(1) DEFAULT 0,
  `registro_doc_certificaciones` tinyint(1) DEFAULT 0,
  `registro_doc_cedula` tinyint(1) DEFAULT 0,
  `registro_doc_fotografia` tinyint(1) DEFAULT 0,
  `registro_observaciones` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `expedientes_digitales`
--

INSERT INTO `expedientes_digitales` (`id_expediente`, `id_usuario`, `grado_a_matricular`, `especialidad_deseada`, `genero`, `fecha_nacimiento`, `lugar_nacimiento`, `nacionalidad`, `cedula_residencia`, `estado_civil`, `domicilio_direccion`, `domicilio_provincia`, `domicilio_canton`, `domicilio_distrito`, `contacto_tel_habitacion`, `contacto_tel_celular`, `contacto_otro_emergencias`, `procedencia_secundaria_institucion`, `procedencia_secundaria_ano_graduacion`, `procedencia_secundaria_grado_obtenido`, `procedencia_universidad`, `procedencia_universidad_ano_graduacion`, `procedencia_universidad_grado_obtenido`, `procedencia_universidad_especialidad`, `laboral_institucion`, `laboral_fecha_ingreso`, `laboral_puesto`, `laboral_telefono`, `laboral_extension`, `laboral_fax`, `laboral_correo_electronico`, `registro_fecha_matricula`, `registro_doc_titulo_sec`, `registro_doc_titulo_univ`, `registro_doc_certificaciones`, `registro_doc_cedula`, `registro_doc_fotografia`, `registro_observaciones`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 3, NULL, '', NULL, '0000-00-00', '', 'Perú', '', NULL, '', '', '', '', '', '', '', '', '', '', '', '', NULL, '', '', '0000-00-00', '', '', '', '', '', NULL, 0, 0, 0, 0, 0, NULL, '2026-01-29 05:44:21', '2026-01-29 05:44:21'),
(2, 15, 'Bachillerato', 'Maestria en Teologia', 'Masculino', '1980-01-08', 'San Jose, Costa Rica', 'Costa Rica', '487658658', 'Soltero', 'Puriscal', '', '', '', '875464385', '6587658', '', '', '', '', '', '', NULL, '', '', '0000-00-00', '', '', '', '', '', NULL, 0, 0, 0, 0, 0, NULL, '2026-01-30 02:52:14', '2026-01-30 02:52:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matriculas`
--

CREATE TABLE `matriculas` (
  `id_matricula` int(11) NOT NULL,
  `id_estudiante` int(11) NOT NULL,
  `id_curso_activo` int(11) NOT NULL,
  `calificacion` decimal(5,2) DEFAULT NULL,
  `email_enviado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio_email` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `matriculas`
--

INSERT INTO `matriculas` (`id_matricula`, `id_estudiante`, `id_curso_activo`, `calificacion`, `email_enviado`, `fecha_envio_email`) VALUES
(27, 1, 8, 29.00, 1, '2026-02-10 13:40:52'),
(28, 3, 8, 100.00, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_rubros`
--

CREATE TABLE `notas_rubros` (
  `id_nota_rubro` int(11) NOT NULL,
  `id_matricula` int(11) NOT NULL,
  `id_rubro` int(11) NOT NULL,
  `calificacion_obtenida` decimal(5,2) DEFAULT 0.00,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notas_rubros`
--

INSERT INTO `notas_rubros` (`id_nota_rubro`, `id_matricula`, `id_rubro`, `calificacion_obtenida`, `fecha_registro`) VALUES
(81, 27, 60, 20.00, '2026-02-10 19:40:43'),
(82, 27, 61, 30.00, '2026-02-10 19:40:43'),
(83, 27, 62, 50.00, '2026-02-10 19:40:43'),
(84, 28, 60, 100.00, '2026-02-10 21:55:37'),
(85, 28, 61, 100.00, '2026-02-10 21:55:37'),
(86, 28, 62, 100.00, '2026-02-10 21:55:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `boleta_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `metodo_pago` varchar(50) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `registrado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `nombre`, `descripcion`) VALUES
(1, 'admin_usuarios', 'Permite crear, editar y eliminar otros usuarios.'),
(2, 'ver_dashboard_completo', 'Permite ver las estadísticas y tareas de todos los usuarios.'),
(3, 'crear_tareas', 'Permite crear nuevas tareas.'),
(4, 'asignar_tareas', 'Permite asignar tareas a otros usuarios.'),
(5, 'editar_tareas_propias', 'Permite editar las tareas asignadas a uno mismo.'),
(6, 'editar_tareas_todas', 'Permite editar cualquier tarea del sistema.'),
(7, 'eliminar_tareas', 'Permite eliminar cualquier tarea del sistema.'),
(8, 'comentar_tareas', 'Permite añadir comentarios en las tareas.'),
(9, 'gestionar_plantillas', 'Permite crear, editar y eliminar plantillas de tareas.'),
(10, 'crear_usuarios', 'Permite crear nuevos usuarios en el sistema.'),
(11, 'gestionar_usuarios', 'Permite ver y gestionar la lista de usuarios.'),
(12, 'gestionar_enlaces', 'Permite gestionar (crear, editar, eliminar) los enlaces del sistema.'),
(13, 'enviar_notas', 'Permite enviar calificaciones y notificaciones a estudiantes.'),
(14, 'gestionar_precios', 'Permite crear, editar y eliminar los precios de los cursos por nivel académico.'),
(15, 'ver_menu_registro', 'Permite ver y acceder a todas las opciones del menú de Registro.'),
(17, 'gestionar_expedientes', 'Permite ver, crear, editar y eliminar expedientes digitales de los estudiantes.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_estudios`
--

CREATE TABLE `plan_estudios` (
  `id_plan` int(11) NOT NULL,
  `id_programa` int(11) NOT NULL,
  `cuatrimestre` varchar(255) NOT NULL,
  `codigo` varchar(255) NOT NULL,
  `materia` varchar(255) NOT NULL,
  `creditos` int(11) NOT NULL,
  `requisitos` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `plan_estudios`
--

INSERT INTO `plan_estudios` (`id_plan`, `id_programa`, `cuatrimestre`, `codigo`, `materia`, `creditos`, `requisitos`, `precio`) VALUES
(8, 1, 'I Cuatrimestre', 'EG 101', 'Introducción a la Filosofia', 2, '', 0.00),
(16, 1, 'III Cuatrimestre', 'TP 284', 'Oratoria Sacra II', 2, '', 0.00),
(17, 1, 'IV Cuatrimestre', 'P 234', 'Práctica profesional IV', 2, '', 0.00),
(18, 1, 'IV Cuatrimestre', 'TS 346', 'Pensamiento de la Iglesia II', 2, '', 0.00),
(19, 1, 'IV Cuatrimestre', 'LB 359', 'Griego Instrumental I', 3, '', 0.00),
(20, 1, 'III Cuatrimestre', 'PP 335', 'Griego Instrumental II', 4, '', 0.00),
(22, 3, 'I Cuatrimestre', 'NIV B2', 'Griego Instrumental', 2, '', 0.00),
(23, 3, 'I Cuatrimestre', 'NIV B3', 'Fundamentos de Hermenéutica', 2, '', 0.00),
(24, 3, 'I Cuatrimestre', 'NIV B4', 'Fundamentos Bíblicos y Teológicos de la Eclesiología (Semipresencial)', 2, '', 0.00),
(25, 3, 'I Cuatrimestre', 'NIV B5', 'Epistemología de la Iglesia y la Sociedad', 2, '', 0.00),
(26, 3, 'II Cuatrimestre', 'TI 10', 'Metodología de la investigación', 5, '', 0.00),
(27, 3, 'II Cuatrimestre', 'TP 30', 'Metodología Pedagógica de la teología', 5, '', 0.00),
(28, 3, 'II Cuatrimestre', 'TB-10', 'Hermenéutica Bíblica y Social', 5, '', 0.00),
(29, 3, 'II Cuatrimestre', 'TM-10', 'Perspectiva Bíblica de la Missio Dei y el papel del pueblo de Dios', 5, '', 0.00),
(30, 3, 'II Cuatrimestre', 'TH-10', 'Análisis Histórico-Social de la Iglesia y de la realidad latinoamericana', 4, '', 0.00),
(31, 3, 'III Cuatrimestre', 'EP-30', 'Pastoral Contextual', 5, '', 0.00),
(32, 3, 'III Cuatrimestre', 'EI-30', 'Liderazgo en la Iglesia', 5, '', 0.00),
(33, 3, 'III Cuatrimestre', 'EI-30', 'Psicología de la experiencia religiosa', 5, '', 0.00),
(34, 3, 'III Cuatrimestre', 'ES-30', 'Teología y desarrollo social', 5, '', 0.00),
(35, 3, 'III Cuatrimestre', 'ET-30', 'Teología latinoamericana de la Iglesia', 5, '', 0.00),
(36, 3, 'IV Cuatrimestre', 'ET-50', 'Iglesia y Teología', 5, '', 0.00),
(37, 3, 'IV Cuatrimestre', 'ES-50', 'Iglesia y Sociedad', 4, '', 0.00),
(38, 3, 'IV Cuatrimestre', 'EU-50', 'Iglesia y Cultura', 4, '', 0.00),
(39, 3, 'V Cuatrimestre', 'ED-90', 'TFG Tracto I', 6, '', 0.00),
(40, 3, 'V Cuatrimestre', 'ED-90', 'TFG Tracto II', 6, '', 0.00),
(41, 3, 'V Cuatrimestre', 'ED-90', 'TFG Tracto III', 6, '', 0.00),
(42, 4, 'I Cuatrimestre', 'CH694', 'Orientación Prematrimonial', 2, '', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plataformas`
--

CREATE TABLE `plataformas` (
  `id_plataforma` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `link_acceso` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Volcado de datos para la tabla `plataformas`
--

INSERT INTO `plataformas` (`id_plataforma`, `nombre`, `link_acceso`) VALUES
(1, 'Unela Virtual', 'http://unela.ac.cr/virtual'),
(2, 'Inbox', 'http://unela.org/bpm_unela');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precios_cursos_conesup`
--

CREATE TABLE `precios_cursos_conesup` (
  `id` int(11) NOT NULL,
  `nivel` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `precios_cursos_conesup`
--

INSERT INTO `precios_cursos_conesup` (`id`, `nivel`, `precio`) VALUES
(13, 'Bachillerato', 40000.00),
(14, 'Licenciatura', 50000.00),
(15, 'Maestria', 100000.00),
(16, 'Doctorado', 120000.00),
(17, 'Técnico', 45000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas`
--

CREATE TABLE `programas` (
  `id_programa` int(11) NOT NULL,
  `nombre_programa` varchar(255) NOT NULL,
  `categoria` varchar(255) DEFAULT NULL,
  `informacion` text DEFAULT NULL,
  `oferta` text DEFAULT NULL,
  `perfil` text DEFAULT NULL,
  `costo_matricula` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Costo de la matrícula asociado al programa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programas`
--

INSERT INTO `programas` (`id_programa`, `nombre_programa`, `categoria`, `informacion`, `oferta`, `perfil`, `costo_matricula`) VALUES
(1, 'Teología', 'Bachillerato', 'Aqui va toda la información', 'Aqui va la oferta', 'Aqui va el perfil', 0.00),
(2, 'Orientacion Familiar', 'Maestría', 'didfysuiyfiery fiurywe8fhkweurfyiusdf noisoigpoidfguiogueor', 'wriuygeiuygkxisvnxckjhvkfjdhgdkjgh', 'dhurhori', 0.00),
(3, 'Eclesiologia...', 'Doctorado', 'Aqui va la descripción de la carrera', 'Aquí debe ir la oferta académica.', 'Aquí debe ir el perfil', 0.00),
(4, 'Maestria en Orientación a la Familia', 'Maestría', '', '', '', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos_corporativos`
--

CREATE TABLE `recursos_corporativos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(100) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reuniones_zoom`
--

CREATE TABLE `reuniones_zoom` (
  `id` int(11) NOT NULL,
  `titulo_curso` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_reunion` datetime NOT NULL,
  `enlace_zoom` varchar(255) NOT NULL,
  `id_creador` int(11) NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(5, 'Estudiante'),
(2, 'Gestor de Proyectos'),
(3, 'Miembro'),
(4, 'Profesor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id_rol` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id_rol`, `id_permiso`) VALUES
(1, 1),
(1, 2),
(2, 2),
(1, 3),
(2, 3),
(1, 4),
(2, 4),
(1, 5),
(3, 5),
(1, 6),
(2, 6),
(1, 7),
(1, 8),
(2, 8),
(3, 8),
(1, 9),
(2, 9),
(1, 10),
(1, 11),
(1, 12),
(2, 12),
(1, 13),
(4, 13),
(1, 14),
(1, 15),
(1, 17);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saludos_enviados`
--

CREATE TABLE `saludos_enviados` (
  `id` int(11) NOT NULL,
  `plantilla_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_personalizado` varchar(255) DEFAULT NULL,
  `mensaje_personalizado` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `creado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saludo_categorias`
--

CREATE TABLE `saludo_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saludo_plantillas`
--

CREATE TABLE `saludo_plantillas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ruta_imagen` varchar(512) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabos`
--

CREATE TABLE `silabos` (
  `id_silabo` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `id_profesor` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `objetivo_general` text DEFAULT NULL,
  `objetivos_especificos` text DEFAULT NULL,
  `metodologia` text DEFAULT NULL,
  `contenidos` text DEFAULT NULL,
  `cronograma` text DEFAULT NULL,
  `bibliografia` text DEFAULT NULL,
  `modalidad` varchar(50) DEFAULT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `silabos`
--

INSERT INTO `silabos` (`id_silabo`, `id_plan`, `id_profesor`, `descripcion`, `objetivo_general`, `objetivos_especificos`, `metodologia`, `contenidos`, `cronograma`, `bibliografia`, `modalidad`, `horario`, `fecha_creacion`) VALUES
(1, 22, 11, '<p>Descripcion del curso</p>', '<p>Aprender griego</p>', '<p>conocer escritos antiguos</p>', '<p>El profesor dar&aacute; la clase.....</p>', '<p>aqui va todo el contenido</p>', '<p>aqui va todo el cronograma</p>', '<p>Bibliograf&iacute;a sugerida</p>\r\n<p>jhfgjgfjgdsfjsdgfj</p>\r\n<p>gdfashdfahsgdfhasdfhasdfg</p>', 'Virtual', '', '2026-01-22 20:23:33'),
(2, 19, 11, '<p>hgdfhsdfhgd hdsagfdhasfd hdgasfdhgfasdh hdgfasdhgasfd hdgfsahgdfh<br>jhgjhgfjsfdgjfhg jfhsdgfjhsgdfjhgsfjh jfhgsdjhfjshgfj jfhsdgfjhgsdjhfg jfhgdsjhgfjhsdgf jfhgsdjfgj<br>jdfhkghkhjgkjdhg jkghdkjfhgkdfjhgk kjgjhdfkgjhdkg kgjhdfjkghdjkg kgjhdkjghdfkjgh kgjhfdjkghdkjg klghjdfkljghdfkj</p>', '<p>ghfdjkhgd jhgdjhasgd jashdgjhagd jhdhagsjhdgjad jahdgjkasgdjasgdjha jhagfdjhas</p>', '<p>1.-hjsadfgjgdjsgdjasgdjgdj<br>2.-jhsagdfjgdjsgdjgdjsdgj<br>3.-kjhgfjkgdjhfgdhjf jkhdsgfjhsdgfjhsd jfhdsgfhjdgsfj</p>', '<p>hgfdhgasfd kjdfghsajhkdgjdf jhdgjhagdhjagd jdhgasjdgasjd jdhgasdhjgasjhdg jasgdjhas</p>', '<p>1.jgfjdsgfhjsgfjhsdgfhjsdgfjhdsgfjsdfgjh<br>&nbsp; &nbsp; &nbsp; &nbsp;jhdgfjhdsgfjhgdsfjhgdsjhfgjdhsfgdhjsfgsjdhgfjsdh<br>&nbsp; &nbsp; &nbsp; &nbsp;jhfkjshfjkhsdfkjhdfkjhdfkhsdk<br>&nbsp; &nbsp; &nbsp; &nbsp;hgdfjkghfjkhgdhjfgjsd</p>\r\n<p>&nbsp;</p>\r\n<p>2.fgjhsdgfjsdgfjsdgfjsgdfjsgfjsdgfjsdgfj<br>&nbsp; &nbsp; &nbsp; &nbsp;jhdgfkjsdghfjhgsdfjhgsfjhgdsfjhgdsjhfdjsh</p>\r\n<p>gdfhgasfdhgasfdhasdfhasfdh</p>', NULL, '<p>AQUI VA TODA LA BIBLIOGRAFIA</p>', 'Virtual', 'Martes 06:00', '2026-01-22 20:26:06'),
(3, 19, 1, '<p>aqui va la descripcion</p>', '', '', '', '', NULL, '', 'Presencial', 'Martes', '2026-01-24 16:00:43'),
(5, 42, 1, '<p class=\"MsoNormal\"><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">La orientaci&oacute;n prematrimonial constituye un proceso formativo fundamental para la prevenci&oacute;n de crisis conyugales y familiares, ya que el matrimonio no puede comprenderse &uacute;nicamente como la uni&oacute;n de dos individuos, sino como la conformaci&oacute;n de un sistema relacional que se inserta en contextos familiares, sociales, culturales y espirituales m&aacute;s amplios. Desde una perspectiva sist&eacute;mica, la pareja es entendida como un subsistema que se ve profundamente influenciado por las familias de origen, los patrones transgeneracionales, los estilos de comunicaci&oacute;n aprendidos y las experiencias relacionales previas.<br><br>Este curso aborda la preparaci&oacute;n prematrimonial desde el enfoque sist&eacute;mico-familiar, reconociendo que muchas de las dificultades que emergen en la vida matrimonial &mdash;conflictos recurrentes, problemas de comunicaci&oacute;n, manejo inadecuado de l&iacute;mites, dificultades en la toma de decisiones, crisis emocionales o espirituales&mdash; tienen sus ra&iacute;ces en din&aacute;micas relacionales no resueltas, expectativas impl&iacute;citas y modelos familiares internalizados. Por ello, la orientaci&oacute;n prematrimonial no se limita a transmitir informaci&oacute;n sobre el matrimonio, sino que promueve un proceso reflexivo y formativo que permita a la pareja identificar, comprender y resignificar dichos patrones antes de asumir el compromiso conyugal.<br><br>El curso se fundamenta en los aportes de la Teor&iacute;a General de Sistemas y de la Terapia Familiar Sist&eacute;mica, las cuales conciben los problemas humanos como fen&oacute;menos relacionales y circulares, m&aacute;s que como fallas individuales aisladas. Desde esta perspectiva, el fortalecimiento de la pareja implica trabajar aspectos como la diferenciaci&oacute;n personal, la comunicaci&oacute;n funcional, el establecimiento de l&iacute;mites saludables, la negociaci&oacute;n de roles, el manejo constructivo del conflicto y la integraci&oacute;n consciente de la historia familiar de cada miembro. Estos elementos resultan esenciales para la construcci&oacute;n de un v&iacute;nculo conyugal estable, flexible y resiliente.<br><br>Asimismo, el curso integra una visi&oacute;n cristoc&eacute;ntrica del matrimonio, entendiendo este como un pacto relacional con implicaciones espirituales, &eacute;ticas y comunitarias. Sin caer en reduccionismos, se promueve una comprensi&oacute;n equilibrada que articula los principios b&iacute;blicos con los aportes de la psicolog&iacute;a y la terapia familiar, favoreciendo una formaci&oacute;n integral. La fe se presenta como un recurso que aporta sentido, valores y motivaci&oacute;n al proceso de preparaci&oacute;n, al tiempo que se reconoce la importancia del conocimiento cient&iacute;fico y de las habilidades relacionales para el bienestar conyugal.<br><br>La justificaci&oacute;n acad&eacute;mica de este curso radica en la necesidad de formar profesionales y agentes de acompa&ntilde;amiento capaces de intervenir de manera preventiva y sist&eacute;mica en el &aacute;mbito prematrimonial. En contextos donde el aumento de separaciones, conflictos conyugales y problem&aacute;ticas familiares evidencia carencias en la preparaci&oacute;n para la vida matrimonial, resulta imprescindible ofrecer una formaci&oacute;n s&oacute;lida que permita a las parejas comprender su relaci&oacute;n como un sistema en construcci&oacute;n y asumir el matrimonio con mayor conciencia, responsabilidad y madurez relacional. De este modo, el curso contribuye al fortalecimiento de las familias y, por extensi&oacute;n, de las comunidades a las que estas pertenecen.</span></p>', '<p class=\"MsoListBulletCxSpFirst\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span style=\"color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\">Comprender la relaci&oacute;n de pareja desde una perspectiva sist&eacute;mica y familiar, reconociendo c&oacute;mo la historia personal, la familia de origen, los patrones relacionales y el contexto influyen en la construcci&oacute;n del v&iacute;nculo matrimonial.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span style=\"color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\">Integrar conocimientos psicol&oacute;gicos, sist&eacute;micos y b&iacute;blico&ndash;teol&oacute;gicos para la orientaci&oacute;n prematrimonial, promoviendo una comprensi&oacute;n integral del matrimonio que contemple dimensiones emocionales, relacionales, sociales y espirituales.</span></p>\r\n<p class=\"MsoListBulletCxSpLast\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span style=\"color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\">Desarrollar una reflexi&oacute;n cr&iacute;tica y &eacute;tica sobre el matrimonio como sistema relacional y pacto cristiano, aplicando principios sist&eacute;micos y valores cristianos al acompa&ntilde;amiento preventivo de parejas en proceso de preparaci&oacute;n matrimonial.</span></p>', '<p class=\"MsoListBulletCxSpFirst\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span style=\"color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\">Analizar el matrimonio como un sistema relacional, identificando sus componentes, l&iacute;mites, roles, reglas expl&iacute;citas e impl&iacute;citas, as&iacute; como las interacciones que configuran la din&aacute;mica de la pareja.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Explicar los principios fundamentales del enfoque sist&eacute;mico-familiar (interdependencia, causalidad circular, homeostasis, comunicaci&oacute;n y diferenciaci&oacute;n), aplic&aacute;ndolos al an&aacute;lisis y acompa&ntilde;amiento de parejas en etapa prematrimonial.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Reconocer la influencia de la familia de origen y de los patrones transgeneracionales en la elecci&oacute;n de pareja, las expectativas conyugales y las formas de afrontar los conflictos dentro del matrimonio.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Identificar estilos de comunicaci&oacute;n, manejo de conflictos y regulaci&oacute;n emocional presentes en la relaci&oacute;n de pareja, proponiendo estrategias sist&eacute;micas que favorezcan v&iacute;nculos funcionales y saludables.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Evaluar factores de riesgo y factores protectores en la etapa prematrimonial, considerando aspectos personales, relacionales, familiares y espirituales que inciden en la estabilidad matrimonial.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Incorporar herramientas de psicolog&iacute;a positiva, inteligencia emocional y fortalecimiento de recursos personales para promover la resiliencia, la toma de decisiones conscientes y el crecimiento mutuo en la vida conyugal.</span></p>\r\n<p class=\"MsoListBulletCxSpMiddle\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Articular una visi&oacute;n cristoc&eacute;ntrica del matrimonio, integrando principios b&iacute;blicos relacionados con el amor, el compromiso, el perd&oacute;n, la responsabilidad y la espiritualidad, en coherencia con los aportes de la psicolog&iacute;a sist&eacute;mica.</span></p>\r\n<p class=\"MsoListBulletCxSpLast\" style=\"margin-left: 18.0pt; mso-add-space: auto; text-indent: -18.0pt; mso-list: l0 level1 lfo1; tab-stops: 18.0pt;\"><!-- [if !supportLists]--><span lang=\"EN-US\" style=\"font-family: Symbol; mso-fareast-font-family: Symbol; mso-bidi-font-family: Symbol; color: black; mso-themecolor: text1;\"><span style=\"mso-list: Ignore;\">&middot;<span style=\"font: 7.0pt \'Times New Roman\';\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span></span></span><!--[endif]--><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Demostrar habilidades de pensamiento cr&iacute;tico, comunicaci&oacute;n escrita y an&aacute;lisis aplicado, mediante la participaci&oacute;n en foros acad&eacute;micos, la elaboraci&oacute;n de tareas reflexivas y el desarrollo de un proyecto final integrador orientado a la preparaci&oacute;n prematrimonial.</span></p>', '<p class=\"MsoNormal\"><span style=\"color: black; mso-themecolor: text1; mso-ansi-language: ES-CR;\">Las experiencias de aprendizaje del curso est&aacute;n dise&ntilde;adas para promover un proceso formativo activo, reflexivo y aplicado, acorde con el enfoque sist&eacute;mico y el nivel universitario del programa. Se combinan estrategias asincr&oacute;nicas en plataforma virtual con sesiones sincr&oacute;nicas de integraci&oacute;n (Zoom).<br><br></span><span lang=\"EN-US\" style=\"color: black; mso-themecolor: text1;\">Entre las principales experiencias de aprendizaje se incluyen:<br>&bull; Foros de discusi&oacute;n y reflexi&oacute;n cr&iacute;tica.<br>&bull; An&aacute;lisis de casos y ejercicios sist&eacute;micos.<br>&bull; Trabajos escritos y tareas reflexivas.<br>&bull; Aplicaci&oacute;n de instrumentos y autoevaluaciones.<br>&bull; Sesiones sincr&oacute;nicas (Zoom) de integraci&oacute;n.<br>&bull; Proyecto final integrador.<br><br>En conjunto, estas estrategias buscan formar estudiantes capaces de comprender la relaci&oacute;n de pareja de manera sist&eacute;mica, reflexiva y &eacute;tica, promoviendo una preparaci&oacute;n prematrimonial consciente, preventiva y orientada al fortalecimiento de la vida matrimonial y familiar.</span></p>', '<p><strong>1.-Contenido del primer capitulo</strong></p>\r\n<p>Aqui va todo jksdfgjsdgfjdsgfj kjfsdghfkhsdfksdhfk jksfdhgfjgsdjhgfjsd jsdgfjsdgf jfsdgfjgsdfjg<br>djhfgsdjhfgsjdgfjhsdgf jhfsdgfjhdsfjhsdgf jfhsdgfjhgsdjfgsjdhfg jfdgsjhfgjhsdgfjsdft</p>\r\n<p><strong><br>2.-Contenido del capitulo 2</strong></p>\r\n<p>Aqui va todo el contelkhjdfklsdf fkdjshfkjsdkf kjfjsdhfkjhskfh kfsdjhfjkhsdkjf<br>vjhsgfjgsdfjhgsjf jfsdhgfjhgsfjhsgfj jfsdgfjhgsdfjh</p>', NULL, '<p>Aqui va la bibiografia</p>', 'Virtual', 'Lunes 18:00', '2026-01-24 21:07:01'),
(6, 8, 1, '<p>jgdhjgdhjgdhjgdgaj</p>', '<p>iyriwyri</p>', '<p>hkjfdskfhsdkf</p>', '<p>gdjsgfjdsgfj</p>', '<p>1juyuyturyturyut</p>', NULL, '<p>atruewtru</p>', 'Virtual', NULL, '2026-01-26 01:00:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_contenidos`
--

CREATE TABLE `silabo_contenidos` (
  `id_contenido` int(11) NOT NULL,
  `id_silabo` int(11) NOT NULL,
  `unidad` varchar(100) DEFAULT NULL,
  `tema` varchar(255) DEFAULT NULL,
  `subtemas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_cronograma`
--

CREATE TABLE `silabo_cronograma` (
  `id_cronograma` int(11) NOT NULL,
  `id_silabo` int(11) NOT NULL,
  `semana` varchar(50) DEFAULT NULL,
  `fecha` varchar(100) DEFAULT NULL,
  `actividad` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `silabo_cronograma`
--

INSERT INTO `silabo_cronograma` (`id_cronograma`, `id_silabo`, `semana`, `fecha`, `actividad`) VALUES
(11, 2, '1', '2026-01-08', 'jdghfkjhfkdsfgksdhfkdsfhk\r\ndgfhsdgfhsdgfhdsgfhsdgfhsdgfhsdf\r\ndhgahdghasdhasgdhasgdh'),
(12, 2, '2', '2026-01-15', 'jdhfgjhgfjsdgfhjsdgfjgsdfj\r\ndghfashjgdfaghsfdhgafsdghasfdhgasdfh\r\ngdfashgdfghsafdghasfdh'),
(13, 2, '3', '2026-01-22', 'dfghdfhdfghasfdhg'),
(14, 2, '4', '', ''),
(16, 3, '2', '2026-01-31', 'hdghjgdjhsagdjhasgdjhadga'),
(91, 5, '1', '', 'Reunión Zoom 1\r\nIntroduccion\r\nForo 1'),
(92, 5, '2', '', 'fhjdsgfhjsdgfjgfjgsdjfgsjdf'),
(93, 5, '3', '', 'dsyfgyjdstfsdgfjsdfgjhsdf'),
(94, 5, '4', '', 'difdsyufsjdhfghjsdgfjhsdgfj'),
(95, 5, '5', '', 'fjdsfghjsdgfjdsgfjhsdgfjhsdgfhjs'),
(96, 5, '6', '', 'fdsjhfgjsdhgfhjsdgfjhsdgfjhsd'),
(97, 5, '7', '', 'jhgfdhjsdgfjgsdfjsdgjf'),
(98, 5, '8', '', 'fhgsdjhfgjshgfjhsdgfjsdgfjhsdgfuj'),
(100, 6, '2', '2026-02-02', 'yriuewyriwyriwyi');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `silabo_evaluacion`
--

CREATE TABLE `silabo_evaluacion` (
  `id_evaluacion` int(11) NOT NULL,
  `id_silabo` int(11) NOT NULL,
  `rubro` varchar(100) DEFAULT NULL,
  `porcentaje` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `silabo_evaluacion`
--

INSERT INTO `silabo_evaluacion` (`id_evaluacion`, `id_silabo`, `rubro`, `porcentaje`) VALUES
(1, 1, 'Examen parcial', 20),
(2, 1, 'Trabajos', 60),
(3, 1, 'Proyecto final', 20),
(31, 2, 'Examen', 25),
(32, 2, 'Trabajos', 30),
(33, 2, 'Exposicion', 25),
(34, 2, 'Ensayos', 20),
(40, 3, 'examen', 30),
(60, 5, 'Examen Parcial', 50),
(61, 5, 'Examnen Final', 30),
(62, 5, 'Quiz', 20),
(63, 6, 'proyectos', 50),
(64, 6, 'examen', 50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte`
--

CREATE TABLE `soporte` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `problema` text DEFAULT NULL,
  `solucion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `id_creador` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `soporte`
--

INSERT INTO `soporte` (`id`, `titulo`, `problema`, `solucion`, `categoria_id`, `fecha_creacion`, `id_creador`) VALUES
(4, 'El estudiante reporta no ver ciertas actividades o recursos de un curso.', '<p><strong>El estudiante reporta el 29/9/2025</strong></p>\r\n<ol>\r\n<li>No ver el programa del curso que imparte Rafael Saenz</li>\r\n<li>El profesor tambien reporta el problema\r\n<ol>\r\n<li>Se le sugirio que para descartar alguna falta de atenci&oacute;n de detalle al ver el curso ingresar y cambiar su rol a usuario</li>\r\n<li>Si al cambiar de rol se compruaba que efectivamente no existe el recurso, corregirlo y si fuera de un grado mayor reportarlo a DTI</li>\r\n</ol>\r\n</li>\r\n</ol>', '<p><strong>Pasos para revisar el \"problema\"</strong></p>\r\n<ol>\r\n<li>Ingresar con permisos de administrador a la plataforma Moodle y revisar el contenido del curso</li>\r\n<li>Cambiar a modo edici&oacute;n</li>\r\n<li>Cambiar&nbsp; a rol de estudiante para ver lo que ve el estudiante</li>\r\n<li>Si la actividad o recurso no existiera coordinar con el profesor para generarla</li>\r\n<li>Reportar al estudiante el resultado de la revision y agradecerle su observaci&oacute;n</li>\r\n</ol>', 1, '2025-09-23 16:43:03', 1),
(6, 'Sale el Error.  FORBIDDEN. ( Prohibido) al guardar QUIZ en Moodle.', '<p>https://cursos.unela.ac.cr/course/view.php?id=429<br><br><br>&nbsp;<br><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAr8AAAC7CAYAAABsF/bRAAABVWlDQ1BJQ0MgUHJvZmlsZQAAGJV1kL1Lw1AUxU+1EqmKoo4KGRQUqkhrFcfaQQWRUBU/tjRNUyWtjyQi4uYUnJw66SQ4uxXBQcTBVRQL/gcO4iBksSHel6hpFS8c7o/DefddLtACmTE9CqBUtozs3Ky4vrEpCi+IYRACupCUFZOlJWmRIvjuzeU8IcL7wxifdVSvvfWdHh7YtuYeX7b3/s03VSyvmgr1OmlEYYYFRIaIpT2LcSah36CliG3OWsAnnHMBX/iZlWyG+Ja4RynKeeJH4niuwdcauKTvKl878O071fLqMu+kAcxDgogEplH6Jzfp5zLYAcM+DGxBQxEWvUqTw6BDJV5AGQrGEfenTZBS/L6/7xZ621Vg6oy+eg29whJwdQd0z4Te8D2tUgFuBCYb8s81I07ULCQTAXfQrLaK572vAcIo4NY876Pqee450PoMXDufsJpjecBwxREAAAA4ZVhJZk1NACoAAAAIAAGHaQAEAAAAAQAAABoAAAAAAAKgAgAEAAAAAQAAAr+gAwAEAAAAAQAAALsAAAAARxNcCwAAQABJREFUeAHtnQd4HNXVho8syR3cbUyz6TWUEHovARJI6C00U0IChBpKKEkIJRB6Tyih9x5q6ITeCb334hgDtnHFtqz/vtc6+4/XM7sjaSVr7e/oWe3uzJ2ZO+/c8t1zz8zWfPzxx40mEwEREAEREAEREAEREIE5gECnOeAcdYoiIAIiIAIiIAIiIAIiEAnUde3a1ZIvcREBERABERABERABERCB2ZVATWOw2fXkdF4iIAIiIAIiIAIiIAIikCSgsIckDX0WAREQAREQAREQARGYrQlI/M7Wl1cnJwIiIAIiIAIiIAIikCQg8Zukoc8iIAIiIAIiIAIiIAKzNQGJ39n68urkREAEREAEREAEREAEkgTqkl/0WQRmJYFpDQ02ZcoUmzJ5sjWEz9OmTbPG8JKJgAiIgAiIgAiIQKUISPxWiqT202ICiN1JEycF0ftDi/ehDUVABERABERABEQgDwGJ3zyUlKZNCODdnTB+vE35QaK3TQBrpyIgAiIgAiIgAjMRkPidCYkWtAeBSRMn2oRx49rjUDqGCIiACIiACIiACBQISPwWUOhDexGYOGGCTQweX5kIiIAIiIAIiIAItDcBid/2Jj6HH6+c8K2trbXOXbpYXX291dbVWadO0x9Iws1vDVOn2tRwQ9zkECZByIRMBERABERABERABJpLQD9v3FxiSt9iAqVCHRC93Xr0iMI3zwEQwHiPJYLz0FIaERABERABERABJyDx6yT03qYEEKljvvsu9Rhduna1HnPNlbqu3MLxY8faD5MmlUum9SIgAiIgAiIgAiIQCehHLlQQ2oUAT3VIs27du7dY+LI/RDP7kImACIiACIiACIhAHgISv3koKU2rCPAc37THmeHxJdShtcY+2JdMBERABERABERABMoRkPgtR2g2XH/55Zcbr/aySSlhCcT4tjTUIS3f7It9ykRABERABERABESgFIGKiN/2FlOlToh1r776anyVS9fW6//73//awQcfbOuuu258JTnxmeXtaZ6fK664wniRN5a1pcWfLE75EYssj+8zzzxjZ511Vnw1N19Z+0zbz333328fffRR2qoOseztd96xRx97rJCXkd98Y7fdcYdNDU+8kJUmMD6E2Nx622025vvvSyfs4GsbGxvt9n/9y/73v/91yJy+/8EHdtMtt1Q8b48/8YS98eab9smnn9rd99xT8f0nd5inHWht3Xvo4YftnXffjYdtK2bJc6r0Z8rhDTfeWPH2sqOXb8rhk089VWmcufc3LrRjtPljxozJvY0S5ifQavGLiENIdTSb1QIYYXnQQQdFLCuuuKINGzYscoIVzNrbOCb5eeWVV+ycc86J+eEzy9oyP1PCo8mKzR9nVrwc0Xv22Wfbs88+G8UvQrg5xiPS8np/r7jySnu5hPD/Ntycd1foeCeGH+NwS1vm6yr9fu3119vTifOnIb45CI268Pg3WWkC340aZSecdJJ9/fXXpRN28LUfhsHZVVdf3SGeaJJW9u9/4AG7/oYbjE66UoYgOvf88+3Tzz6zN996y6646qpK7Tp1P+XaATZqbd275rrr7LnnnovHL2aGGH74kUdS8zarFhbniUHkjTffbA9VOJ8dvXxfdc01dl0o37PKRod27OqQBwZfssoTaFVP6sIXYbfHHntUPnct3OPyyy8ft0QAY/49fmmHf3BxkbnCCisUjggjZ1ZY2A4f/JiIcMQlRr6K89MW15B432JDpKYZ4veQQw6Jrx122CHmdfXVV7cFF1xwpuSerngF++ZZwq21zz//3C4L13Hl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cRd33qXS+z6zjs22aZaVvjgtU7Y8wivN6BxLdZB58l1uH1nXkPxklbdkXvOeZ3IbnASEw2SxK5Vnrl1Lynry+MWfy9W9UvlhX92bfhWyeL9Z39PaDYRMKSvX3rCtxwGn7ScK89BmZVlanorT+iPsipeX+t7W5btU+aN9nRXl23l4W+/f/b1cu+vpSr1Xug6UOtbssK4i4nd2ADG7noN7edPOr9S6tPQtXda1W9eZxC8j4PHBa9AjeLAqYeyLfba34Tnnub6VMOKEGcUnBQC/WuYxspU4hvbRsQlQhhG+PMe3nPHIqrSbJstt1xHXM91LjHxHso5S9wjnIC8el9qRGDU3L3Nq+U7jxA+AcG1bY4QqcUOyrPkEKhL20PzDaos5jcDYMP02JUyzFRuPtEqL/y1OV+o7Ht+JxNXJREAEREAEREAERKAMgfT5uTIbabUINJdA9/DoljRDtOK1bamxrYRvS+lpOxEQAREQARGY8wjI8zvnXfNZdsaTwk0gE0Jgf5oRC4UHOG8cMDdK4fGdFaEOafnXMhEQAREQAREQgeogIPFbHddptsklXtpSN6YhghHA/GoNv8Dm8a/cxc4PWHB3MsJXone2KRI6EREQAREQARFoVwISv+2KWweDQDkBLEoiIAIiIAIiIAIi0FYEFPPbVmS130wC3OTWPTwbUiYCIiACIiACIiAC7U1Ant/2Jq7jFQgQujAhxO2mPQWikEgfREAEREAEREAERKCCBCR+KwhTu2oZgSnhV4r4MQeJ4Jbx01YiIAIiIAIiIAL5CUj85mellG1MgJ9C5mdDEcN4hbnJrTG8ZCIgAiIgAiIgAiJQKQISv5Uiqf2IgAiIgAiIgAiIgAh0eAK64a3DXyJlUAREQAREQAREQAREoFIEJH4rRVL7EQEREAEREAEREAER6PAEJH47/CVSBkVABERABERABERABCpFQOK3UiS1HxEQAREQAREQAREQgQ5PQOK3w18iZVAEREAEREAEREAERKBSBCR+K0VS+xEBERABERABERABEejwBCR+O/wlUgZFQAREQAREQAREQAQqRUDit1IktR8REAEREAEREAEREIEOT0Dit8NfImVQBERABERABERABESgUgQkfitFUvsRAREQAREQAREQARHo8AQkfjv8JVIGRUAEREAEREAEREAEKkWgrlI7qvR+/ve//8VdzjPPPJXetfYnAiKQg8ArIx+yBz691IZP+NCmNv5gjY2NqVvV1NRYXU0XG9x9Edt4yN624oCNUtM1Z+GECRNshRVWsIkTJ1rXrl1t2rRp8dWpUyfjhTU0NMQ88Z08TJ482Wpra+2///2v9erVqzmHU1oREAERaDcCUxobrN5qzSZMtdC4Tj9ufWjXutXaFAvrasI6WZsSyCV+11133dRM/Oc//4nLy61P3bjMwkmTJpVJodUiIAJtRQDhe+mbhwRRafGVoXvj4RHFDTWT7PPxb8Zt9l7mrFYL4KlTp1p9Xb1df8f1tvjii0eRO/fcc0cxPH78+HjcHj16WLdu3ez777+P4vezzz6z7bbbLorituKi/c7eBEaNHm3nnHehffDhR2EwNSWebOfO9bboIgvbQQfsZ3169569Aejs2pzAtMZpVh/kzZi68fZE41v21tiP4zGX7r2QrT11aes1NQz2u06zTjWamG/Li5FL/JKBFVdc0ZZffvmYl1dffdVeeeWVGfJVbv0Midvxy2233WYXX3xx7DSThx02bJjtscceyUUd/vOHH34YRcCiiy7a4fNazRls7zIzYsQI69+/f/RadhRueHxxsHbt2sm6z1Vro74J/oiG9NwFZ6v16V9vE8Y22KRJ06K3uLXe3+hlDsJ7rrnmii+8vOeee6499NBD9t1330Wx26dPH9tggw1s//33t/r6euvZs6fVhD+8xJW0119/3fr162fzzjtv5m7J35NPPmlrrLFGzEtmQq3osATuvvffds31N1rD1IZYvnr1mttq62rtu29H2ZtvvWP7HnCI7bLTDrb5zzftEOcwcuRIe/fdd22ttdZqdX7efvvtOKOyxBJLtHpf2kE2Ado1RO0FY+60o965ziaN/zq0V8H7G6xTp1qb1q2fnb30rva7blsUZrqy99b8NbObhpgyZYo999xzM9QBHCfffPONDRw4sDBLSD2hL0m24bnFL8LXxeLll18+k/gtt775l6kyW1x44YW29NJLR/Hue/zggw/siiuuiF/9nHxdS96Tnm88Ucstt5ztuuuu9qMf/SjX7v75z3/aL3/5SxswYEDJ9LfffnsQF5Ps2GOPLZmueCXTwAcddFBhMXncaKON7NBDDy0UjsLKHB/uu+++WIh8MJRjk6pK0h5lhkbwzDPPtH//+9+F6fplllnGTjrpJMPDie2zzz6xc0vCW3vtte3EE0+0Sy65xK655prCKqb5t9pyK9tjz8oM6EZM+tD6Deps0xpCY11bY72DuB2dIoARvqwjTbeetdajV52NGPlhIV8t/UAYg4XZQEIZYHXeeefZX//61/i+7LLLxt2+9dZbdsABB8QyzPsPP4TQjPDnYRHljr3vvvsa+/AZrKz0559/vq233nq20047ZSWJg+s//elPxsAJoVwJS7YrhHMw6D3uuONmaMArcZy0faSVvW222cYOPPDAtORVvwzhe9U110fRu+P229hWW/wilqM333rbjjvh5Hh+iGLSYHkEcPL6xY3Cv9///vexrffvrXl/7LHHjL6DNgTL24+kHZNtO3fuHOtY2vpKLIPHpZdeaosttlhhdw8++KBdcMEFdscdd8RwJfop2sWVVlqpkIZBL2GQ1P/ivswTXX/99fb111/HPu2RRx7xxR3qfWoIdagL4QwHvHOeXfTu9daAd6HzXKHF6hbaukabFpq8mh/G2KHPn27vLv6ZnbfEAYaXuJQH+LrrrovsbrrppniutJUbb7yxbb755oU+n4HNb3/7W7vnnnuspRoCh+dXX31lP/vZz3Izzdu+5t5hSkJ0HM4jHwB6v0h7iR1//PFxHbODf/7znw1e3j/kFr8px62KRYwM8EonRS4F5NRTT40C2EVw2skgYPfee++0VTMtO+2002Kl/vTTT2NhpBLTaSK8y9lVV11lq6yySlnxW24/5dbfddddcUr4xRdfjOfft29f23PPPcttNtP6O++801ZZeZXCTMBMCap8QXuUGcQcjdHf/va3WD6Zskc8IS6owHgy8WAiuHbccccC0S5duhQ+L7nkkka5I79PPfWUnXHGGbbIoovYOuusU0jT0g81dZODEKizbp3rbGDn/vbd5G/N+puN/nZq8IxNj1Grq6uxXv3qbK76zta3c1/7+odvbHJo4C1sWynzRuznP/+54YFlYNK9e/coiIkL3myzzWzTTad74urq6qLnN8+xR40aFYUvaT/55BMbOnRons3aPc3pp59ueOO+/PJLO+uss2LnfsMNN7R5PtLKHrHXs6MR6nDNdTdE4XvWaSeHwcXgeJoI3SefemaGU6bvwDu85hqr5QqBoH4StuNGqE6ljBAfBiRurelHyGeljLLjAqN4n/BLWvF31uGUQdDjpHErTvevf/3LV8V3HAaI345qDUHE1k00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G25josAdXNPLejd2t0nh0WcN9Y3Wo2sQvrOZ4a3lnqAsHcATkHA6FIvsUhgIHywX9kh/wqArKbhzid9SB26rdR4T29qnHLRV/rRfEZjdCSCAH/j0Uhs+4UOb2vhDFJtp54wgravpYniLNx6yd6uFL8dgOo04LbynyZvc0o7PMvLAzAKNmz/yJyutlouACIhAexL4Ogi0nuHHFoLsDe/TY3jHTRgfQsTCtH+n6RPwUxsb4g9fzNU9f+hhe57D7HasDit+ZzfQOh8REAEREAEREAEREIFZTyD3o85mfVaVAxEQAREQAREQAREQARFoHQGJ39bx09YiIAIiIAIiIAIiIAJVREDit4oulrIqAiIgAiIgAiIgAiLQOgISv63jp61FQAREQAREQAREQASqiIDEbxVdLGVVBERABERABERABESgdQQkflvHT1uLgAiIgAiIgAiIgAhUEQGJ3yq6WMqqCIiACIiACIiACIhA6whI/LaOn7YWAREQAREQAREQARGoIgISv1V0sZRVERABERABERABERCB1hGQ+G0dP20tAiIgAiIgAiIgAiJQRQQkfqvoYimrIiACIiACIiACIiACrSNQ99EnH7VuD9paBERABERABERABERABKqEQE1jsCrJq7IpAiIgAiIgAiIgAiIgAq0ioLCHVuHTxiIgAiIgAiIgAiIgAtVEQOK3mq6W8ioCIiACIiACIiACItAqAhK/rcKnjUVABERABERABERABKqJgMRvNV0t5VUEREAEREAEREAERKBVBCR+W4VPG4uACIiACIiACIiACFQTAYnfarpayqsIiIAIiIAIiIAIiECrCEj8tgqfNhYBERABERABERABEagmAhK/1XS1lFcREAEREAEREAEREIFWEZD4bRU+bSwCIiACIiACIiACIlBNBCR+q+lqKa8iIAIiIAIiIAIiIAKtIiDx2yp82lgEREAEREAEREAERKCaCNQlM/vUU0/ZqFGjkotsvfXWs549e86wLO+Xk08+2dZZZx1bc801Mzc544wzbPXVV7c11lgjNc0VV1xhPXr0sO22226m9aXWzZRYC0RABERABERABERABOZ4AjOI3yOPPNJGjhxpiy22WAHMCius0GLx++ijj9rQoUNLit9vvvnGJk6cWDhe8YdXXnnF+vTpkyp+S60r3o++i4AIiIAIiIAIiIAIiMAM4hccBx54oO2///4zkZkwYYIhZnnHkzvvvPMW0nzwwQfxc01NjT377LO25JJL2korrRSXIWzvvvtua2xstI033ti6dOkSl3/33Xf29ttv2+abbz6D2Gblt99+a4888ogNHDgwpk3+K7Vu9OjR9vjjj1vv3r1t7bXXNvKDvfrqq9a/f3977733rKGhwTbYYAPr1EkRH0mu+iwCIiACIiACIiACcwKBmcTv559/bq+99lo8927dukVhinBFTH799dc2ZMgQ22uvvQyv6yKLLBLTXXLJJVEYv/DCC7bFFlsY+3jppZfiuoMPPtjwHr/77ru26qqr2p133hmXf/rpp3bppZfavffea2eeeabtvPPOcTnimvR9+/a1SZMm2fDhw+3QQw8tu450bIfXur6+PuaVMA6E8FFHHRXzs+yyy0YBvP7669tVV10V96l/IiACIiACIiACIiACcw6BmcTvhRdeaLfffnsksMwyy9htt91mb7zxhr3//vsxJALP7bbbbhu9uQcddFCBFMKXEIZ+/frZlClTCss32mijuA/CKfDk4vFF2K644op2+eWX26abblpIy4dnnnnGunfvbi+//HL0Fs8333yF9aXWkWeOdc0118T0O+64YzzunnvuGb/jdb766qvjfoljnjZtmry/BbL6IAIiIAIiIAIiIAJzBoGZ5v65SQ0vLS+EL/bwww/bWmutVQhZWHfdde3++++fgRA3pCF8MTyvbmyHDRgwwBZeeGF74oknfFXqO+EO3PxWW1trdXV1xrHcSq274YYb7LrrrrN55pknvh577DF77rnnfNN44x1f8AyPHTt2phv7Cgn1QQREQAREQAREQAREYLYlMJP4TTtTPLXff/99YdWYMWMKQtcXImzTDKHpNm7cuJm283X+joBObpM8bql1eJtPOOEEGzFiROF10UUX+W6NEA7M44ALK/RBBERABERABERABERgjiGQS/xuuOGGRvzs66+/HkMbiNvdZJNNckEiLaEOeGKJGV555ZVLbkc87gMPPGDEBBNqkfQwl1pHfm6++eYYb8wBvvjii+i9LnkwrRQBERABERABERABEZijCMwgfrOegEDcLU+BWG655WL4QufOnW2zzTYrC4r9Eb6AxxbhSkiFP+1hhx12sEGDBkVxu++++8bPt956azwG8bk8Io0nRnBM99byOWvd4YcfbksvvbQtuOCCtsACC8TPiGeZCIiACIiACIiACIiACDiBmvAkh0b/Uu6dcAQeXZb2CLJS2xImgYCde+65SyWbYR03yJHexXJyZal148ePj/G8xP4SMywTAREQAREQAREQAREQASfQLPHrG+ldBERABERABERABERABKqRwAxhD9V4AsqzCIiACIiACIiACIiACOQlIPGbl5TSiYAIiIAIiIAIiIAIVD0Bid+qv4Q6AREQAREQAREQAREQgbwEJH7zklI6ERABERABERABERCBqicg8Vv1l1AnIAIiIAIiIAIiIAIikJeAxG9eUkonAiIgAiIgAiIgAiJQ9QQkfqv+EuoEREAEREAEREAEREAE8hKQ+M1LSulEQAREQAREQAREQASqnoDEb9VfQp2ACIiACIiACIiACIhAXgISv3lJKZ0IiIAIiIAIiIAIiEDVE5D4rfpLqBMQAREQAREQAREQARHIS0DiNy8ppRMBERABERABERABEah6AhK/VX8JdQIiIAIiIAIiIAIiIAJ5CUj85iWldCIgAiIgAiIgAiIgAlVPQOK36i+hTkAEREAEREAEREAERCAvAYnfvKSUTgREQAREQAREQAREoOoJSPxW/SXUCYiACIiACIiACIiACOQlIPGbl5TSiYAIiIAIiIAIiIAIVD0Bid+qv4Q6AREQAREQAREQAREQgbwEJH7zklI6ERABERABERABERCBqicg8Vv1l1AnIAIiIAIiIAIiIAIikJeAxG9eUkonAiIgAiIgAiIgAiJQ9QQkfqv+EuoEREAEREAEREAEREAE8hKQ+M1LSulEQAREQAREQAREQASqnoDEb9VfQp2ACIiACIiACIiACIhAXgISv3lJKZ0IiIAIiIAIiIAIiEDVE5D4rfpLqBMQAREQAREQAREQARHIS0DiNy8ppRMBERABERABERABEah6AhK/VX8JdQIiIAIiIAIiIAIiIAJ5CUj85iWldCIgAiIgAiIgAiIgAlVPQOK36i+hTkAEREAEREAEREAERCAvAYnfvKSUTgREQAREQAREQAREoOoJSPxW/SXUCYiACIiACIiACIiACOQlIPGbl5TSiYAIiIAIiIAIiIAIVD0Bid+qv4Q6AREQAREQAREQAREQgbwEJH7zklI6ERABERABERABERCBqicg8Vv1l1AnIAIiIAIiIAIiIAIikJeAxG9eUkonAiIgAiIgAiIgAiJQ9QQkfqv+EuoEREAEREAEREAEREAE8hKQ+M1LSulEQAREQAREQAREQASqnoDEb9VfQp2ACIiACIiACIiACIhAXgISv3lJKZ0IiIAIiIAIiIAIiEDVE5D4rfpLqBMQAREQAREQAREQARHIS0DiNy8ppRMBERABERABERABEah6AhK/VX8JdQIiIAIiIAIiIAIiIAJ5CUj85iWldCIgAiIgAiIgAiIgAlVPQOK36i+hTkAEREAEREAEREAERCAvAYnfvKSUTgREQAREQAREQAREoOoJSPxW/SXUCYiACIiACIiACIiACOQlIPGbl5TSiYAIiIAIiIAIiIAIVD0Bid+qv4Q6AREQAREQAREQAREQgbwEJH7zklI6ERABERABERABERCBqicg8Vv1l1AnIAIiIAIiIAIiIAIikJeAxG9eUkonAiIgAiIgAiIgAiJQ9QQkfqv+EuoEREAEREAEREAEREAE8hKQ+M1LSulEQAREQAREQAREQASqnoDEb9VfQp2ACIiACIiACIiACIhAXgISv3lJKZ0IiIAIiIAIiIAIiEDVE5D4rfpLqBMQAREQAREQAREQARHIS0DiNy8ppRMBERABERABERABEah6AhK/VX8JdQIiIAIiIAIiIAIiIAJ5CUj85iWldCIgAiIgAiIgAiIgAlVPQOK36i+hTkAEREAEREAEREAERCAvAYnfvKSUTgREQAREQAREQAREoOoJSPxW/SXUCYiACIiACIiACIiACOQlIPGbl5TSiYAIiIAIiIAIiIAIVD0Bid+qv4Q6AREQAREQAREQAREQgbwEJH7zklI6ERABERABERABERCBqicg8Vv1l1AnIAIiIAIiIAIiIAIikJeAxG9eUkonAiIgAiIgAiIgAiJQ9QQkfqv+EuoEREAEREAEREAEREAE8hKQ+M1LSulEQAREQAREQAREQASqnoDEb9VfQp2ACIiACIiACIiACIhAXgISv3lJKZ0IiIAIiIAIiIAIiEDVE5D4rfpLqBMQAREQAREQAREQARHIS0DiNy8ppRMBERABERABERABEah6AhK/VX8JdQIiIAIiIAIiIAIiIAJ5CUj85iWldCIgAiIgAiIgAiIgAlVPQOK36i+hTkAEREAEREAEREAERCAvAYnfvKSUTgREQAREQAREQAREoOoJSPxW/SXUCYiACIiACIiACIiACOQl8H9KcjSb5sd6NwAAAABJRU5ErkJggg==\"></p>', '', 1, '2025-10-29 19:52:33', 4),
(7, 'Configuración de Zoom para el libre acceso de participantes', '<p>Buscan permitir que cualquier usuario sea el primero para iniciar la sesi&oacute;n sin la necesidad de ser anfitri&oacute;n .<br><img src=\"https://drive.google.com/file/d/1KY3VSCFXKa7xXv_8I3w6uRMqX1BbmUcT/view?usp=sharing\" alt=\"\"></p>', '<p>Puede estudiar los procedimientos generales para la configuraci&oacute;n de sesiones de ZOOM:<br><br></p>\r\n<p><iframe title=\"YouTube video player\" src=\"https://www.youtube.com/embed/KLlTp0zNqnE?si=iqrl_Sto-3dFk_9x\" width=\"560\" height=\"315\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" allowfullscreen=\"allowfullscreen\" referrerpolicy=\"strict-origin-when-cross-origin\"></iframe><br><br></p>\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 99.9004%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>https://drive.google.com/file/d/1KY3VSCFXKa7xXv_8I3w6uRMqX1BbmUcT/view?usp=sharing</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>.......<br>Tambi&eacute;n - vease las configuraciones que utilizamos de costumbre para las clases UNELA, las cuales no depende de anfitri&oacute;n para ser el primer participante.<br><br></p>', 2, '2025-11-03 19:21:16', 4),
(8, 'Nuevo', '<p>hgdfhgfdhafdh</p>', '<p>atsdgfASDFGadsg</p>', 1, '2026-01-23 23:11:23', 1),
(9, 'ruwtrewurturtewurtutr', '<p>tryutweuteuwqteuqteuqteuqteuqetuqteu</p>', '<p>teywqeuwqteuqteuqteu</p>\r\n<p>trwqeyryterqyeryqeryqery</p>', 1, '2026-01-26 01:18:44', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte_articulos_tags`
--

CREATE TABLE `soporte_articulos_tags` (
  `articulo_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte_categorias`
--

CREATE TABLE `soporte_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `soporte_categorias`
--

INSERT INTO `soporte_categorias` (`id`, `nombre`) VALUES
(1, 'Usuario Unela Virtual'),
(2, 'Zoom');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte_tags`
--

CREATE TABLE `soporte_tags` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `id_creador` int(11) NOT NULL,
  `id_asignado` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `id_usuario_completado` int(11) DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id`, `titulo`, `descripcion`, `estado`, `prioridad`, `id_creador`, `id_asignado`, `fecha_creacion`, `fecha_vencimiento`, `id_usuario_completado`, `fecha_completado`) VALUES
(32, 'Reunión equipo TI Unela', '<p><strong>Tenemos el agrado de invitarlo a nuestra primera reuni&oacute;n del equipo de TI de Unela </strong></p>\r\n<p>Temas a tratar:</p>\r\n<ol>\r\n<li>Presentaci&oacute;n del equipo TI Unela</li>\r\n<li>Informaci&oacute;n del modelo de programaci&oacute;n de cursos por Don Tomas Soerens&nbsp;</li>\r\n<li>Presentaci&oacute;n de Inbox como modelo BPM para plataforma centralizada de trabajos</li>\r\n</ol>\r\n<hr>\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 9.47903%;\"><col style=\"width: 90.521%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Dia:</td>\r\n<td>&nbsp;<strong>Martes 2 de Set.</strong></td>\r\n</tr>\r\n<tr>\r\n<td>Hora:</td>\r\n<td><strong>&nbsp;7 pm</strong></td>\r\n</tr>\r\n<tr>\r\n<td>Via:</td>\r\n<td><strong>Zoom</strong></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', 'completada', 'media', 1, NULL, '2025-09-02 06:00:00', '2025-09-02', NULL, NULL),
(40, 'Reunión con CONESUP', '<p><strong>Detalles de la reuni&oacute;n:</strong></p>\r\n<ul>\r\n<li>\r\n<p><strong>Fecha:</strong>&nbsp;Jueves 04 de septiembre de 2025</p>\r\n</li>\r\n<li>\r\n<p><strong>Hora:</strong>&nbsp;10:00 a.m. a 11:30 a.m.</p>\r\n</li>\r\n<li>\r\n<p><strong>Modalidad:</strong>&nbsp;Virtual</p>\r\n</li>\r\n<li>\r\n<p><strong>Enlace de acceso:&nbsp;</strong></p>\r\n<p>Adjunto el link de acceso correcto&nbsp;</p>\r\n<p><a href=\"https://teams.microsoft.com/l/meetup-join/19%3ameeting_MTMxOGM0MmUtYjhmMy00ZjUyLWFlNDItMDRlNGY2YWZkYTI0%40thread.v2/0?context=%7b%22Tid%22%3a%220fa1fe2a-d55b-4665-95e7-53a56927d833%22%2c%22Oid%22%3a%22af1937d7-708a-4abf-9350-c6132b615969%22%7d\" target=\"_blank\" rel=\"noopener\" data-saferedirecturl=\"https://www.google.com/url?q=https://teams.microsoft.com/l/meetup-join/19%253ameeting_MTMxOGM0MmUtYjhmMy00ZjUyLWFlNDItMDRlNGY2YWZkYTI0%2540thread.v2/0?context%3D%257b%2522Tid%2522%253a%25220fa1fe2a-d55b-4665-95e7-53a56927d833%2522%252c%2522Oid%2522%253a%2522af1937d7-708a-4abf-9350-c6132b615969%2522%257d&amp;source=gmail&amp;ust=1756928917350000&amp;usg=AOvVaw1kRm4Ylh-LBX6IO4AoM-KQ\">https://teams.microsoft.com/l/<wbr>meetup-join/19%3ameeting_<wbr>MTMxOGM0MmUtYjhmMy00ZjUyLWFlND<wbr>ItMDRlNGY2YWZkYTI0%40thread.<wbr>v2/0?context=%7b%22Tid%22%3a%<wbr>220fa1fe2a-d55b-4665-95e7-<wbr>53a56927d833%22%2c%22Oid%22%<wbr>3a%22af1937d7-708a-4abf-9350-<wbr>c6132b615969%22%7d</a>&nbsp;</p>\r\n</li>\r\n</ul>', 'completada', 'alta', 1, NULL, '2025-09-04 06:00:00', '2025-09-04', NULL, NULL),
(41, 'Preparar la publicidad para el curso de IA para docentes.', '<p>El proposito de este curso es proveer las herramientas mas sobresalientes en el campo de la inteligencia artificial aplicada a la educaci&oacute;n</p>\r\n<p>Necesitamos definir la fecha exacta del inicio y final del curso y detalles.</p>', 'pendiente', 'media', 1, NULL, '2025-09-02 06:00:00', '2025-09-05', NULL, NULL),
(46, 'Revisar el contenido de la pagina web de Unela', '<p>La direccion es unela.ac.cr</p>\r\n<p>Coordinar con Merlin las claves de acceso de classbox</p>', 'pendiente', 'media', 1, NULL, '2025-09-02 06:00:00', '2025-09-16', NULL, NULL),
(47, 'Documento para profesores para el nuevo cuatrimestre', '<p><strong>Se nos ha solicitado preparar un documento en los pr&oacute;ximos 2 dias con sugerencias y recomendaciones que hablamos ayer.</strong></p>\r\n<p>Ideas:</p>\r\n<ol>\r\n<li>En base al documento que podamos <strong>crear podemos hacer un PDF</strong> con todos los detalles de la informaci&oacute;n</li>\r\n<li>Prepara un <strong>video con estas fuentes en NotebookLM</strong></li>\r\n<li><strong>Enviar los enlaces</strong> de las herramientas a usar por el docente: Plataforma Moodle, Presentaci&oacute;n ( Antes de iniciar la clase)</li>\r\n</ol>\r\n<p>Si desean preparan el documento y lo adjuntan por este medio para el d&iacute;a de ma&ntilde;ana preparar los materiales para enviars&eacute;lo a los profesores.&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>\r\n<p>(Leonel Jimenez)</p>\r\n<p class=\"MsoNormal\" style=\"text-align: justify;\"><strong>Recomendaciones para el uso de Zoom</strong></p>\r\n<p class=\"MsoNormal\" style=\"text-align: justify;\">Recomendaciones para mejorar la calidad y la eficiencia de tus reuniones o clases virtuales en Zoom. Seguir estos consejos ayudar&aacute; a crear un ambiente m&aacute;s profesional y productivo para todos los participantes.</p>\r\n<p class=\"MsoNormal\" style=\"text-align: justify;\"><strong>Antes de la reuni&oacute;n</strong></p>\r\n<ul style=\"margin-top: 0cm;\" type=\"disc\">\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l1 level1 lfo1; tab-stops: list 36.0pt;\"><strong>Verifica tu conexi&oacute;n y equipo:</strong> Aseg&uacute;rate de que tu conexi&oacute;n a internet sea estable y que la c&aacute;mara y el micr&oacute;fono de tu computadora o dispositivo funcionen correctamente.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l1 level1 lfo1; tab-stops: list 36.0pt;\"><strong>Prepara tus documentos:</strong> Ten listos y a mano todos los archivos, presentaciones o documentos que planeas compartir. Abrirlos de antemano te ahorrar&aacute; tiempo y evitar&aacute; interrupciones durante la reuni&oacute;n.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l1 level1 lfo1; tab-stops: list 36.0pt;\"><strong>Busca buena iluminaci&oacute;n:</strong> Col&oacute;cate en un lugar con buena luz. Lo ideal es tener una fuente de luz natural o artificial frente a ti, no detr&aacute;s. Esto evitar&aacute; que tu rostro se vea oscuro o en contraluz, haciendo tu imagen m&aacute;s clara.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l1 level1 lfo1; tab-stops: list 36.0pt;\"><strong>Env&iacute;a un recordatorio:</strong> Si eres el anfitri&oacute;n, es una buena pr&aacute;ctica enviar un recordatorio a los participantes un par de horas antes del evento. Esto ayuda a que todos se conecten a tiempo y est&eacute;n preparados.</li>\r\n</ul>\r\n<p class=\"MsoNormal\" style=\"text-align: justify;\"><strong>Durante la reuni&oacute;n</strong></p>\r\n<ul style=\"margin-top: 0cm;\" type=\"disc\">\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l0 level1 lfo2; tab-stops: list 36.0pt;\"><strong>Inicia a tiempo:</strong> Si eres el anfitri&oacute;n, inicia la reuni&oacute;n a la hora programada para respetar el tiempo de los dem&aacute;s. Conectarse unos minutos antes tambi&eacute;n te da margen para solucionar cualquier problema t&eacute;cnico.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l0 level1 lfo2; tab-stops: list 36.0pt;\"><strong>Mant&eacute;n la c&aacute;mara encendida:</strong> Encender la c&aacute;mara ayuda a crear un ambiente m&aacute;s personal y colaborativo. Permite a los dem&aacute;s verte y leer tus expresiones, facilitando la comunicaci&oacute;n.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l0 level1 lfo2; tab-stops: list 36.0pt;\"><strong>Silencia los micr&oacute;fonos:</strong> Mant&eacute;n tu micr&oacute;fono apagado cuando no est&eacute;s hablando. Esto evita que ruidos de fondo, como conversaciones, mascotas o notificaciones, interrumpan a la persona que est&aacute; presentando.</li>\r\n<li class=\"MsoNormal\" style=\"text-align: justify; mso-list: l0 level1 lfo2; tab-stops: list 36.0pt;\"><strong>Mencionar el usa las reacciones para participar:</strong> Si necesitas pedir la palabra, en lugar de encender el micr&oacute;fono de golpe, utiliza la funci&oacute;n de \"Levantar la mano\" (Raise Hand) en las reacciones de Zoom. El anfitri&oacute;n o la persona a cargo de la reuni&oacute;n te dar&aacute; la palabra en el momento adecuado.</li>\r\n</ul>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', 'completada', 'alta', 1, NULL, '2025-09-03 06:00:00', '2025-09-05', NULL, NULL),
(48, 'Crear organigrama para subirlo a la página web de Unela', '<p>Se recibi&oacute; un documento de Don Gilberto especificando el modelo del organigrama para subirlo a la pagina web</p>\r\n<p>Se adjunta el documento recibido</p>\r\n<p>&nbsp;</p>', 'en_proceso', 'media', 1, NULL, '2025-09-03 06:00:00', '2025-09-05', NULL, NULL),
(49, 'Renión con Merlin para firma de certificados en Unela San José', '<p>Reunion el miercoles 3 de Set para firma de certificados del curso de Moodle para profesores.</p>', 'completada', 'alta', 1, NULL, '2025-09-03 06:00:00', '2025-09-03', NULL, NULL),
(50, 'Creación de cuenta de Gilberto Abarca', '<p>Aqui le paso los links respectivos para acceder al sistema Inbox BPM</p>\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 7.96208%;\"><col style=\"width: 92.1205%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Usuario:</td>\r\n<td>unelagil@gmail.com</td>\r\n</tr>\r\n<tr>\r\n<td>clave:</td>\r\n<td>Gil2025</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>\r\n<p>Este mensaje ha sido enviado desde el sistema Inbox BPM</p>', 'completada', 'baja', 1, NULL, '2025-09-04 06:00:00', '2025-09-04', NULL, NULL),
(52, 'Reunión con Amada Naranjo', '<p>Preparar el cronograma de trabajo de 3 meses</p>\r\n<p>La Reuni&oacute;n es el 18 de Octubre</p>', 'completada', 'media', 1, NULL, '2025-10-09 06:00:00', '2025-10-18', NULL, NULL),
(54, 'Cambio tecnológico equipo de administración de Unela', '<p>Cambio tecnol&oacute;gico:</p>\r\n<ol>\r\n<li>Cambio de disco duro por uno de estado solido</li>\r\n<li>Actualizacin de suite de ofimatica</li>\r\n<li>Limpieza general</li>\r\n</ol>\r\n<p>&nbsp;</p>', 'completada', 'alta', 1, NULL, '2025-09-25 06:00:00', '2025-09-29', NULL, NULL),
(55, 'Reunión de trabajo con el equipo de DTI', '<p><strong>Reuni&oacute;n virtual</strong></p>\r\n<p>Hora: 7 pm.</p>\r\n<p>Link generado por Thomas Soerens</p>\r\n<p>[Aqui se puede porner el link de zoom]</p>\r\n<p>&nbsp;</p>\r\n<p>&nbsp;</p>', 'completada', 'media', 1, NULL, '2025-09-29 06:00:00', '2025-09-29', NULL, NULL),
(58, 'Ejempo de curso - desde sílabo hasta curso montado en Moode', '<p>Se toma un ejemplo del curso preparado por Profesora Noelia Robinson; Tom&aacute;s comparte t&eacute;cnicas de montar el curso.&nbsp; (por lo menos en lo b&aacute;sico) Es una alternativa, no necesariamente ultima palabra sobre el asunto.<br>&nbsp;Quizas lo m&aacute;s novedoso es el uso de tinyMCE (un editor que permite un cambio de vista entre texto y la .html que lo ordena) dentro del modulo de MOODLE que se llama&nbsp; \"area de texto y medios\"&nbsp; con el fin de presentar un panorama de las actividades obligatorias en cada semana.&nbsp; Se incorpora (encrustados) enlaces a RESUMEN, QUIZ, TAREA, FORO, Rubricas en un solo campo de html.<br><br>//////////. una tabla - editable en tinyMCE en Moodle, dentro del modulo \"area de texto y medios\"&nbsp;</p>\r\n<div dir=\"ltr\" align=\"left\">\r\n<table style=\"width: 100.097%; height: 534.667px; border-collapse: collapse;\" border=\"1\"><colgroup> <col style=\"width: 8.37918%;\" width=\"66\"> <col style=\"width: 9.64875%;\" width=\"76\"> <col style=\"width: 81.8874%;\" width=\"645\"> </colgroup>\r\n<tbody>\r\n<tr style=\"height: 48.3333px;\">\r\n<td style=\"padding: 4px;\">\r\n<p dir=\"ltr\">SESI&Oacute;N</p>\r\n</td>\r\n<td style=\"padding: 4px;\">\r\n<p dir=\"ltr\">FECHA</p>\r\n</td>\r\n<td style=\"padding: 4px;\">\r\n<p dir=\"ltr\">CONTENIDO</p>\r\n</td>\r\n</tr>\r\n<tr style=\"height: 486.333px;\">\r\n<td style=\"padding: 4px; background-color: #d9f4e7;\">\r\n<p dir=\"ltr\"># 1</p>\r\n</td>\r\n<td style=\"padding: 4px; background-color: #d9f4e7;\"><br><br>\r\n<p dir=\"ltr\">22-09-25</p>\r\n<br><br></td>\r\n<td style=\"padding: 4px; background-color: #d9f4e7;\">\r\n<p dir=\"ltr\"><strong>Documentos SEMANA 1</strong></p>\r\n<p dir=\"ltr\"><strong>Tarea: &nbsp;<a href=\"https://docs.google.com/document/d/1DJ_Z9krnKKa88NuN2NLwc-lUguaDC_Ty/edit?usp=drive_link&amp;ouid=107324284667957052676&amp;rtpof=true&amp;sd=true\">&ldquo;TAREA - SEMANA 1\"</a></strong></p>\r\n<p dir=\"ltr\"><strong>(r&uacute;brica para la tarea) &nbsp; &nbsp; <a href=\"https://docs.google.com/document/d/1t2AWmh3BQwM1V304ensmivbUvoRE874u/edit?usp=drive_link&amp;ouid=107324284667957052676&amp;rtpof=true&amp;sd=true\">rubrica_tarea_semana_1</a></strong></p>\r\n<p dir=\"ltr\">&nbsp;</p>\r\n<p dir=\"ltr\"><strong>Quiz: &nbsp; <a href=\"https://docs.google.com/document/d/1nkrcrrLljoYfeMZT1niyDkWaXsJC11W_/edit?usp=drive_link&amp;ouid=107324284667957052676&amp;rtpof=true&amp;sd=true\">quiz_semana_1.docx metodos de intervencion</a></strong></p>\r\n<p style=\"font-weight: bold;\">&nbsp; (r&uacute;brica para quiz) &nbsp; <a href=\"https://docs.google.com/document/d/1be5auvPFz6xQkNF5VfrByFA3RSu1ShSi/edit?usp=drive_link&amp;ouid=107324284667957052676&amp;rtpof=true&amp;sd=true\">rubrica quiz</a></p>\r\n<ol>\r\n<li dir=\"ltr\" style=\"font-weight: bold;\" aria-level=\"1\">\r\n<p dir=\"ltr\" role=\"presentation\"><a href=\"https://drive.google.com/drive/u/0/folders/1bnOoYy8Zl2iPKNQMi7m9kDJo0WcyLbCP\">Video # 1: &ldquo;Los cinco axiomas de la comunicaci&oacute;n&rdquo;</a></p>\r\n</li>\r\n</ol>\r\n<h6 dir=\"ltr\">Video recomendado &ndash; Semana 1</h6>\r\n<p dir=\"ltr\">Para complementar la introducci&oacute;n a la posmodernidad y a las terapias posmodernas, se sugiere ver el siguiente video de YouTube (duraci&oacute;n ~8&nbsp;minutos) que explica de manera accesible la filosof&iacute;a posmoderna y su relaci&oacute;n con la terapia narrativa:</p>\r\n<p dir=\"ltr\"><a href=\"https://www.youtube.com/watch?v=MrqcYar4JZY\">Posmodernismo / filosof&iacute;a posmoderna / terapia narrativa</a></p>\r\n<p dir=\"ltr\">Este material de &ldquo;psiqueacad&eacute;mica&rdquo; relaciona las bases filos&oacute;ficas de la posmodernidad con la construcci&oacute;n del relato en la terapia. Visual&iacute;zalo antes de participar en el foro de la semana.</p>\r\n<strong id=\"docs-internal-guid-e9dec8bf-7fff-eb26-3934-1068068658b9\"></strong></td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>\r\n<hr>\r\n<h4>Arriba es el html (codigo fuente) hecho en Moodle mismo, con enlaces al grupo de documentos (bien completo)que Noelia me mand&oacute; para poner en la carpeta (y sub-carpetas semanales) que est&aacute; en DRIVE de materialdidactica@<br>/////////<br>&nbsp; ABAJO - En documentos adjuntos, hay un archivo que se llama NavigacionCarpetasDrive -&nbsp; puede abrirle para ver la representaci&oacute;n de directorio de documentos en DRIVE - de donde saco enlaces para pegar dentro de la ventana tinyMCE.&nbsp;<br>&nbsp;La imagen es del<br>directorio de documentos que residen en DRIVE -- de d&oacute;nde hago&nbsp; CLIC DERECHO &gt;&gt; Compartir&gt;&gt;Copia enlace&nbsp; -- para activir LINKS en el cuerpo de informaci&oacute;n ARRIBA<br><br></h4>\r\n<h4>&nbsp;</h4>\r\n<p>&nbsp;</p>', 'completada', 'media', 4, NULL, '2025-09-25 06:00:00', '2025-10-02', NULL, NULL),
(59, 'Problema en un quiz del curso TB 691 Fund. Bibl. Teología - Maestría OF', '<p>Merlin reporta que se ha programado un curso para ser resuelto en solo 2 d&iacute;as, pero un trabajo en la plataforma debe tener un tiempo de una semana para que el estudiante pueda completarlo</p>\r\n<p>Adjunto captura que me env&iacute;o</p>', 'completada', 'alta', 1, NULL, '2025-09-30 06:00:00', '2025-10-01', NULL, NULL),
(60, 'Cómo acceder a Todos los Cursos en Moodle', '<p>Subido un .pdf con la informaci&oacute;n</p>', 'completada', 'media', 4, NULL, '2025-10-01 06:00:00', '2025-10-06', NULL, NULL),
(61, 'Cómo chequear registros de actividades', '<p>Subido .pdf</p>', 'completada', 'media', 4, NULL, '2025-10-01 06:00:00', '2025-10-05', NULL, NULL),
(63, 'Rediseño Brochur', '<p>Buenas tardes,</p>\r\n<p>&nbsp;</p>\r\n<p>Ren&aacute;n le adjunto el brochur para redise&ntilde;ar.</p>\r\n<p>Por favor quitar el circulo donde dice UNELA y</p>\r\n<p>en lugar donde hay dos circulos CONESUP y UNELA&nbsp;</p>\r\n<p>dejar uno en una cara y el otro en otra cara.</p>\r\n<p>Donde dice carreras el Doctorado agregar modalidad semipresencial.</p>\r\n<p>&nbsp;</p>\r\n<p>Te agradezco mucho se me lo envias para envi&aacute;rselo a Amada antes de&nbsp;</p>\r\n<p>imprimir para que lo aprueben. Muchas gracias.</p>', 'completada', 'media', 3, NULL, '2025-10-03 06:00:00', '2025-10-08', NULL, NULL),
(64, 'Ejemplo CUATRIMESTRAL de la oferta academica --  codigos, cursos, profesores y sesiones ZOOM - <', '<p>MERLIN hace muy bien lo que se necesita como primer paso para montar cursos en plataforma, o sea: prepara detalles de la OFERTA para X cuatrimestre<br><br>&nbsp;LINK in GOOGLE DRIVE&nbsp; &nbsp; &nbsp; &nbsp;<br>Ejemplo CUATRIMESTRAL de la oferta academica -- &nbsp;codigos, cursos, profesores y sesiones ZOOM - &lt;<br>///////// vaya al:&nbsp;<br>https://docs.google.com/spreadsheets/d/1qhRWXfx7rRM5x1PmOCMxHMRJb-SA22j3/edit?usp=drive_link&amp;ouid=115858947524132765737&amp;rtpof=true&amp;sd=true</p>\r\n<p>&nbsp;</p>\r\n<p><a href=\"https://docs.google.com/spreadsheets/d/1qhRWXfx7rRM5x1PmOCMxHMRJb-SA22j3/edit?usp=drive_link&amp;ouid=115858947524132765737&amp;rtpof=true&amp;sd=true\">&ldquo;Ejemplo CUATRIMESTRAL de la oferta academica -- &nbsp;codigos, cursos, profesores y sesiones ZOOM\"</a></p>\r\n<p dir=\"ltr\">&nbsp;</p>', 'completada', 'media', 4, NULL, '2025-10-16 06:00:00', '2025-10-16', NULL, NULL),
(65, 'PUBLICIDAD', '<p>Buenas tardes,</p>\r\n<p>&nbsp;</p>\r\n<p>Ren&aacute;n, por favor puede dise&ntilde;ar la publicidad para la revista LA FUENTE.</p>\r\n<p>Se tiene que enviar antes del 20 de octubre.</p>\r\n<p>Puede redise&ntilde;ar el brochur anterior y agregarle inicio de lecciones</p>\r\n<p>enero 2026.</p>\r\n<p>Muchas gracias.</p>', 'completada', 'media', 3, NULL, '2025-10-16 06:00:00', '2025-10-20', NULL, NULL),
(67, 'Crear correos @unela.ac.cr', '<p><img src=\"imgs/logo_ia_unela.png\" alt=\"Logo Unela\" width=\"250\" height=\"113\"></p>\r\n<p>Creaci&oacute;n de correo electr&oacute;nico institucional con la extensi&oacute;n @unela.ac.cr de google&nbsp;</p>\r\n<p>A solicitud de Phd Gilberto Abarca</p>\r\n<table style=\"border-collapse: collapse; width: 71.9697%;\" border=\"1\"><colgroup><col style=\"width: 15.3263%;\"><col style=\"width: 30.0455%;\"><col style=\"width: 54.6282%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Fecha:</td>\r\n<td>22/10/2025</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Usuario:</td>\r\n<td>dcbachillerato@unela.ac.cr</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Clave:</td>\r\n<td>J7M9H&gt;VWRyn79rU=</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>\r\n<table style=\"border-collapse: collapse; width: 71.9697%;\" border=\"1\"><colgroup><col style=\"width: 15.3263%;\"><col style=\"width: 30.0455%;\"><col style=\"width: 54.6282%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Fecha:</td>\r\n<td>22/10/2025</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Usuario:</td>\r\n<td>dcmaestria@unela.ac.cr</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Clave:</td>\r\n<td>Y&lt;*qb29=98D&lt;7bR%</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>\r\n<table style=\"border-collapse: collapse; width: 71.9697%;\" border=\"1\"><colgroup><col style=\"width: 15.3263%;\"><col style=\"width: 30.0455%;\"><col style=\"width: 54.6282%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Fecha:</td>\r\n<td>22/10/2025</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Usuario:</td>\r\n<td>dcdoctorado@unela.ac.cr</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Clave:</td>\r\n<td>Z5QZX&lt;XXedX&gt;2hN=</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>Atentamente</p>\r\n<p>DTI</p>\r\n<p>Unela</p>', 'completada', 'media', 1, NULL, '2025-10-21 06:00:00', '2025-10-22', NULL, NULL),
(68, 'Investigar sobre la seguridad de doblle paso de acceso de las cuentas de zoom de Unela', '<p>La idea es que los usuarios autorizados de Unela puedan acceder a las cuentas de Zoom para crear clases o editarlas sin problemas.</p>\r\n<p>Necesitamos poder acceder sin problemas para dar soporte a las clases.</p>', 'pendiente', 'media', 1, NULL, '2025-10-27 06:00:00', '2025-11-10', NULL, NULL),
(69, 'Acceso a la plataforma de Moodle con permisos de administrador', '<p>Ren&aacute;n Galvan ha tenido problemas de acceso por el doble paso que solicita la plataforma, solicito investigar c&oacute;mo solucionarlo en el caso de Ren&aacute;n, ya que Leonel si puede acceder sin problemas. Se le solicita tener una reuni&oacute;n en l&iacute;nea para ajustar detalles y encontrar las causas de ese impase.</p>', 'pendiente', 'media', 1, NULL, '2025-10-27 06:00:00', '2025-11-10', NULL, NULL),
(70, 'Corrección a la información sobre DOCTORADO en ECLESIOLOGIA', '<p>Me refiero a la programaci&oacute;n ( la secuencia de cursos) que se ve en estos documentos:</p>\r\n<p>&nbsp;</p>\r\n<p class=\"p1\"><a href=\"matricula/generar_programa_completo.php\"><strong>https://unela.org/bpm_unela/matricula/generar_programa_completo.php</strong></a><br><br>y&nbsp;</p>\r\n<p class=\"p1\"><a href=\"matricula/generar_programa_completo.php\"><strong>https://unela.org/bpm_unela/matricula/generar_programa_completo.php</strong></a><br><br>Desde el punto de vista de formar cohortes hechos de gente que ingresan juntos y avanzan juntos en sus estudios,&nbsp; es recomendable incluir los cursos de nivelaci&oacute;n como distribuidos en cuatrimestres (como optativos para los que los necesitan) en vez de ubicarlos en un bloque por si solos)<br>&nbsp;&nbsp;<br>&nbsp; &nbsp;Y documento adjunto,&nbsp; .pdf ,&nbsp; es una presentaci&oacute;n de una programaci&oacute;n de cursos Eclesiologia, y me gustar&iacute;a saber -- &iquest;cu&aacute;l es la forma , y con qui&eacute;n? para editar la programaci&oacute;n que sali&oacute; en los documentos arriba.&nbsp;<br><br><br><br></p>', 'pendiente', 'media', 4, NULL, '2025-11-12 06:00:00', '2025-11-21', NULL, NULL),
(71, 'Reunion con Amada Naranjo', '<p>Renan tiene que reunirce para coordinar asuntos de matricula</p>', 'completada', 'alta', 1, NULL, '2025-11-20 06:00:00', '2025-11-25', NULL, NULL),
(72, 'PUBLICIDAD EN FACEBOOK', '<p>Buenos d&iacute;as,</p>\r\n<p>&nbsp;</p>\r\n<p>Ren&aacute;n, adjunto los documentos para la publicidad en Facebook. Adem&aacute;s, el cronograma para las pautas publicitarias.</p>\r\n<p>&nbsp;</p>\r\n<p>Muchas gracias,</p>\r\n<p>&nbsp;</p>\r\n<p>Merlin Silva</p>', 'completada', 'media', 3, NULL, '2025-12-11 06:00:00', '2025-12-12', NULL, NULL),
(73, 'Crear cursos para el primer cuatrimestre 2026', '<p>Para crear los curss para el primer cuatrimestre del 2026 se debr&aacute;n cumplir los siguientes pasos:</p>\r\n<p>Crear los nuevos cursos (si se puede reutilizar un curso ya exiustente eso agiliza el proceso, no olvidar actualizar las fechas en todo, trabajos, etc)</p>\r\n<p>Registrar a los nuevos estudiantes en la nueva plataforma de moodle. unela.ac.cr/virtual</p>\r\n<p>Programar las reuniones de zoom y ponerlas dentro del curso para que el estudiante tenga toda la informacion</p>\r\n<p>Poner lo eventos en el almanaque general de moodle para que sea visible para todos</p>\r\n<p>&nbsp;</p>\r\n<p>Se adjunta el documento xml del cronograma de cursos</p>\r\n<p>&nbsp;</p>', 'en_proceso', 'alta', 1, NULL, '2026-01-09 06:00:00', '2026-01-12', NULL, NULL),
(74, 'Clase 1: Griego Instrumental I', 'Sesión presencial/virtual número 1 del curso Griego Instrumental I', 'pendiente', 'media', 11, NULL, '2026-01-22 06:00:00', '0000-00-00', NULL, NULL),
(87, 'Cronograma de publicidad en redes sociales 2026 - UNELA', '<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"><col style=\"width: 9.9835%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>', 'pendiente', 'media', 1, NULL, '2026-01-01 06:00:00', '2026-12-31', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_asignaciones`
--

CREATE TABLE `tarea_asignaciones` (
  `id` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarea_asignaciones`
--

INSERT INTO `tarea_asignaciones` (`id`, `id_tarea`, `id_usuario`, `fecha_asignacion`) VALUES
(54, 40, 1, '2025-09-02 20:01:18'),
(55, 32, 1, '2025-09-02 20:03:51'),
(56, 41, 6, '2025-09-02 20:19:35'),
(57, 41, 3, '2025-09-02 20:19:36'),
(58, 41, 1, '2025-09-02 20:19:37'),
(67, 46, 5, '2025-09-03 02:49:17'),
(72, 48, 1, '2025-09-03 16:34:31'),
(73, 49, 3, '2025-09-03 16:36:35'),
(74, 49, 1, '2025-09-03 16:36:36'),
(86, 50, 7, '2025-09-04 18:11:45'),
(91, 47, 6, '2025-09-05 16:31:01'),
(92, 47, 5, '2025-09-05 16:31:04'),
(93, 47, 1, '2025-09-05 16:31:06'),
(94, 47, 4, '2025-09-05 16:31:08'),
(97, 52, 1, '2025-09-22 22:16:18'),
(99, 54, 3, '2025-09-23 16:32:16'),
(100, 54, 1, '2025-09-23 16:32:17'),
(106, 55, 5, '2025-09-25 16:31:18'),
(107, 55, 1, '2025-09-25 16:31:19'),
(108, 55, 4, '2025-09-25 16:31:20'),
(156, 59, 5, '2025-09-30 22:55:21'),
(157, 59, 4, '2025-09-30 22:55:22'),
(158, 60, 4, '2025-10-01 20:18:51'),
(159, 61, 4, '2025-10-01 20:19:11'),
(162, 58, 4, '2025-10-01 20:22:01'),
(164, 63, 1, '2025-10-03 19:24:25'),
(177, 64, 3, '2025-10-16 16:29:34'),
(178, 64, 1, '2025-10-16 16:29:35'),
(179, 64, 4, '2025-10-16 16:29:36'),
(180, 65, 1, '2025-10-16 18:22:58'),
(182, 67, 7, '2025-10-22 19:18:37'),
(183, 68, 4, '2025-10-28 02:03:44'),
(184, 69, 4, '2025-10-28 02:06:01'),
(187, 70, 1, '2025-11-12 20:04:06'),
(188, 71, 1, '2025-11-20 20:24:56'),
(189, 72, 1, '2025-12-11 18:12:01'),
(205, 73, 5, '2026-01-14 17:05:24'),
(206, 74, 11, '2026-01-22 19:43:36'),
(219, 87, 3, '2026-01-28 19:30:19'),
(220, 87, 1, '2026-01-28 19:30:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_etiquetas`
--

CREATE TABLE `tarea_etiquetas` (
  `id_tarea` int(11) NOT NULL,
  `id_etiqueta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarea_etiquetas`
--

INSERT INTO `tarea_etiquetas` (`id_tarea`, `id_etiqueta`) VALUES
(59, 1),
(55, 2),
(63, 2),
(58, 3),
(60, 3),
(61, 3),
(64, 3),
(65, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea_plantillas`
--

CREATE TABLE `tarea_plantillas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `prioridad_default` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `id_creador` int(11) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarea_plantillas`
--

INSERT INTO `tarea_plantillas` (`id`, `titulo`, `descripcion`, `prioridad_default`, `id_creador`, `fecha_creacion`) VALUES
(11, 'Crear correo @unela.ac.cr', '<p><img src=\"imgs/logo_ia_unela.png\" alt=\"Logo Unela\" width=\"250\" height=\"113\"></p>\r\n<p>Creaci&oacute;n de correo electr&oacute;nico institucional con la extension @unela.ac.cr de google&nbsp;</p>\r\n<table style=\"border-collapse: collapse; width: 71.9697%;\" border=\"1\"><colgroup><col style=\"width: 15.3263%;\"><col style=\"width: 30.0455%;\"><col style=\"width: 54.6282%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Fecha:</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Usuario:</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>Clave:</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>Atentamente</p>\r\n<p>DTI</p>\r\n<p>Unela</p>', 'media', 1, '2025-10-22 18:15:05'),
(12, 'Matricular usuario a Unela Virtual', '<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 12.7329%;\"><col style=\"width: 23.3396%;\"><col style=\"width: 63.9275%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Cuat</td>\r\n<td>Programa</td>\r\n<td>Curso</td>\r\n</tr>\r\n<tr>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\"><colgroup><col style=\"width: 12.2671%;\"><col style=\"width: 46.4286%;\"><col style=\"width: 29.1925%;\"><col style=\"width: 12.2671%;\"></colgroup>\r\n<tbody>\r\n<tr>\r\n<td>Nro</td>\r\n<td>Nombre Completo</td>\r\n<td>Email</td>\r\n<td>Cuat.</td>\r\n</tr>\r\n<tr>\r\n<td>1</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>2</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>3</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>4</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>5</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>6</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>7</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>8</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>9</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n<tr>\r\n<td>10</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n<td>&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<p>&nbsp;</p>', 'media', 1, '2026-01-12 17:49:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(255) DEFAULT NULL,
  `cedula` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `fecha_nacimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `cedula`, `email`, `password`, `id_rol`, `creado_en`, `fecha_nacimiento`) VALUES
(1, 'Renan Galvan', NULL, '123456789', 'renangalvan@gmail.com', '$2y$10$yglUOCZkZXWJvcMZMZgkhOha96UdNbnHfvMOIf22l8OnFkqly5wYi', 1, '2025-08-25 20:45:22', NULL),
(3, 'Merlin Salas', NULL, '9045876895', 'merlin@unela.ac.cr', '$2y$10$EnPS4biMVQp2I/ofntO7j.hiBTmwAsW9xUIxFkfMhmu0EJCYR1I6.', 1, '2025-08-29 19:42:39', NULL),
(4, 'Tomas Soerens', NULL, '367456345', 'tsoerens@unela.ac.cr', '$2y$10$AUawGkJHz1c7c0H2ilFh6.QRRP7Fjy2prT7xmL3XFQLAI0le7O/4C', 1, '2025-08-29 21:19:13', NULL),
(5, 'Leonel Jiménez Nieto', NULL, '7654873465', 'leojj.lj@gmail.com', '$2y$10$Liac4gX5RTFemm/3LoemMOyWcj3AIQpk48dJgb3LkAIp8byPnH1/6', 2, '2025-08-29 21:32:48', NULL),
(6, 'Amada Naranjo Montero', NULL, NULL, 'amadanaranjo@yahoo.com', '$2y$10$oztgcps66XbM4Q3vUV8v8eJZtEFb4Ebdqxp5KTPP/BxT9arw2aowK', 2, '2025-09-02 19:38:17', NULL),
(7, 'Gilberto Abarca V.', NULL, NULL, 'unelagil@gmail.com', '$2y$10$HfXutR9qP8UJveSZKM0EueHRU6Uc3LTQ8Ra7v7NVPYiUh3twuz31S', 2, '2025-09-04 18:08:39', NULL),
(9, 'Luis Herbozo Regrat', NULL, '654875675', 'herbozoluis@gmail.com', '$2y$10$gEPBhXLFHLFEPgxkSQ9Iw.a2OOCo94lNSNKYlOT9rjYpP42iazP5C', 4, '2025-11-13 17:45:01', NULL),
(10, 'Ivonne Cerdas', NULL, '854764875', 'ivonne@gmail.com', '$2y$10$f0rLxkMCrPMYVAWkz2BjVuNov9.A1qvM1eectC3juWPt91XihMpuW', 4, '2025-11-13 19:26:26', NULL),
(11, 'profe_prueba', NULL, '34367546345', 'dti@unela.ac.cr', '$2y$10$cPrbPR0thsBHJvE5fy1i9eFanxkEMLhDb4FxXzkiIeL.ehnzxXvQq', 4, '2026-01-22 19:31:37', NULL),
(12, 'Noelia Robinson Delgado', NULL, '7846587658', 'noelia@gmail.com', '$2y$10$r5xnVtUw9UEHRRhzjw0r4.g27bUS/E5oz7TVjm1vqgaq.FuffV9K6', 4, '2026-01-24 21:04:32', NULL),
(13, 'Kimberly Chavarria', NULL, '87987979879', 'kimchavarria295@gmail.com', '$2y$10$qsjwajx4q87.qfsQhIWCe.q4RIZ62Uqlz5SHojjhn6O426LUBnYIO', 4, '2026-01-26 01:04:44', NULL),
(14, 'Daniel', 'Galván Suazo', '74676556757', 'daniel@gmail.com', '$2y$10$v2fAPKlljJJqdWzWpj833uYdCcMhQWDqNlmLFU6QIdMh1dLzlnYcG', 3, '2026-01-28 21:53:58', NULL),
(15, 'Marcos', 'Vigil', '3847658356', 'marcos@gmail.com', '$2y$10$e0ApoiI4rFF.sKvBAmnyBOoD1HlQWuYfGXHTV9M7aKdQhq5HtYlq.', 3, '2026-01-30 02:02:51', NULL),
(16, 'Maria', 'Alcazar', '856485684', 'maria@gmail.com', '$2y$10$wfcHUKwDgn/yGSHAc4TssOSy2Wp9gByBDl6xhkw0VfHlv7sD1BAWO', 3, '2026-02-04 20:14:32', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `id_usuario_subida` (`id_usuario_subida`);

--
-- Indices de la tabla `adjuntos_plantillas`
--
ALTER TABLE `adjuntos_plantillas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plantilla` (`id_plantilla`),
  ADD KEY `id_usuario_subida` (`id_usuario_subida`);

--
-- Indices de la tabla `arreglos_pago`
--
ALTER TABLE `arreglos_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id_asistencia`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `id_estudiante` (`id_estudiante`);

--
-- Indices de la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_boleta` (`numero_boleta`),
  ADD KEY `fk_boleta_estudiante` (`id_estudiante`),
  ADD KEY `fk_boleta_creador` (`id_creador`);

--
-- Indices de la tabla `categorias_enlaces`
--
ALTER TABLE `categorias_enlaces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `claves`
--
ALTER TABLE `claves`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tarea` (`id_tarea`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD PRIMARY KEY (`id_credencial`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `cuotas_arreglo`
--
ALTER TABLE `cuotas_arreglo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arreglo_id` (`arreglo_id`),
  ADD KEY `pago_id` (`pago_id`);

--
-- Indices de la tabla `cursos_activos`
--
ALTER TABLE `cursos_activos`
  ADD PRIMARY KEY (`id_curso_activo`),
  ADD KEY `id_plan` (`id_plan`),
  ADD KEY `id_profesor` (`id_profesor`);

--
-- Indices de la tabla `enlaces`
--
ALTER TABLE `enlaces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario_creador` (`id_usuario_creador`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `expedientes_digitales`
--
ALTER TABLE `expedientes_digitales`
  ADD PRIMARY KEY (`id_expediente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id_matricula`),
  ADD KEY `id_estudiante` (`id_estudiante`),
  ADD KEY `id_curso_activo` (`id_curso_activo`);

--
-- Indices de la tabla `notas_rubros`
--
ALTER TABLE `notas_rubros`
  ADD PRIMARY KEY (`id_nota_rubro`),
  ADD UNIQUE KEY `id_matricula` (`id_matricula`,`id_rubro`),
  ADD KEY `id_rubro` (`id_rubro`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `boleta_id` (`boleta_id`),
  ADD KEY `registrado_por` (`registrado_por`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `plan_estudios`
--
ALTER TABLE `plan_estudios`
  ADD PRIMARY KEY (`id_plan`),
  ADD KEY `id_programa` (`id_programa`);

--
-- Indices de la tabla `plataformas`
--
ALTER TABLE `plataformas`
  ADD PRIMARY KEY (`id_plataforma`);

--
-- Indices de la tabla `precios_cursos_conesup`
--
ALTER TABLE `precios_cursos_conesup`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nivel` (`nivel`);

--
-- Indices de la tabla `programas`
--
ALTER TABLE `programas`
  ADD PRIMARY KEY (`id_programa`);

--
-- Indices de la tabla `recursos_corporativos`
--
ALTER TABLE `recursos_corporativos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reuniones_zoom`
--
ALTER TABLE `reuniones_zoom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_creador` (`id_creador`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id_rol`,`id_permiso`),
  ADD KEY `id_permiso` (`id_permiso`);

--
-- Indices de la tabla `saludos_enviados`
--
ALTER TABLE `saludos_enviados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plantilla_id` (`plantilla_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `saludo_categorias`
--
ALTER TABLE `saludo_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `saludo_plantillas`
--
ALTER TABLE `saludo_plantillas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `silabos`
--
ALTER TABLE `silabos`
  ADD PRIMARY KEY (`id_silabo`),
  ADD UNIQUE KEY `unique_silabo` (`id_plan`,`id_profesor`),
  ADD KEY `fk_silabo_profesor` (`id_profesor`);

--
-- Indices de la tabla `silabo_contenidos`
--
ALTER TABLE `silabo_contenidos`
  ADD PRIMARY KEY (`id_contenido`),
  ADD KEY `id_silabo` (`id_silabo`);

--
-- Indices de la tabla `silabo_cronograma`
--
ALTER TABLE `silabo_cronograma`
  ADD PRIMARY KEY (`id_cronograma`),
  ADD KEY `id_silabo` (`id_silabo`);

--
-- Indices de la tabla `silabo_evaluacion`
--
ALTER TABLE `silabo_evaluacion`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD KEY `id_silabo` (`id_silabo`);

--
-- Indices de la tabla `soporte`
--
ALTER TABLE `soporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_creador` (`id_creador`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `soporte_articulos_tags`
--
ALTER TABLE `soporte_articulos_tags`
  ADD PRIMARY KEY (`articulo_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indices de la tabla `soporte_categorias`
--
ALTER TABLE `soporte_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `soporte_tags`
--
ALTER TABLE `soporte_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_creador` (`id_creador`),
  ADD KEY `id_asignado` (`id_asignado`),
  ADD KEY `fk_usuario_completado` (`id_usuario_completado`);

--
-- Indices de la tabla `tarea_asignaciones`
--
ALTER TABLE `tarea_asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_tarea` (`id_tarea`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tarea_etiquetas`
--
ALTER TABLE `tarea_etiquetas`
  ADD PRIMARY KEY (`id_tarea`,`id_etiqueta`),
  ADD KEY `id_etiqueta` (`id_etiqueta`);

--
-- Indices de la tabla `tarea_plantillas`
--
ALTER TABLE `tarea_plantillas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_creador` (`id_creador`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `adjuntos_plantillas`
--
ALTER TABLE `adjuntos_plantillas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `arreglos_pago`
--
ALTER TABLE `arreglos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id_asistencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `boletas`
--
ALTER TABLE `boletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `categorias_enlaces`
--
ALTER TABLE `categorias_enlaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `claves`
--
ALTER TABLE `claves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `credenciales`
--
ALTER TABLE `credenciales`
  MODIFY `id_credencial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `cuotas_arreglo`
--
ALTER TABLE `cuotas_arreglo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cursos_activos`
--
ALTER TABLE `cursos_activos`
  MODIFY `id_curso_activo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `enlaces`
--
ALTER TABLE `enlaces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `expedientes_digitales`
--
ALTER TABLE `expedientes_digitales`
  MODIFY `id_expediente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id_matricula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `notas_rubros`
--
ALTER TABLE `notas_rubros`
  MODIFY `id_nota_rubro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `plan_estudios`
--
ALTER TABLE `plan_estudios`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `plataformas`
--
ALTER TABLE `plataformas`
  MODIFY `id_plataforma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `precios_cursos_conesup`
--
ALTER TABLE `precios_cursos_conesup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `programas`
--
ALTER TABLE `programas`
  MODIFY `id_programa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `recursos_corporativos`
--
ALTER TABLE `recursos_corporativos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reuniones_zoom`
--
ALTER TABLE `reuniones_zoom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `saludos_enviados`
--
ALTER TABLE `saludos_enviados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `saludo_categorias`
--
ALTER TABLE `saludo_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `saludo_plantillas`
--
ALTER TABLE `saludo_plantillas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `silabos`
--
ALTER TABLE `silabos`
  MODIFY `id_silabo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `silabo_contenidos`
--
ALTER TABLE `silabo_contenidos`
  MODIFY `id_contenido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `silabo_cronograma`
--
ALTER TABLE `silabo_cronograma`
  MODIFY `id_cronograma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `silabo_evaluacion`
--
ALTER TABLE `silabo_evaluacion`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `soporte`
--
ALTER TABLE `soporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `soporte_categorias`
--
ALTER TABLE `soporte_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `soporte_tags`
--
ALTER TABLE `soporte_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `tarea_asignaciones`
--
ALTER TABLE `tarea_asignaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT de la tabla `tarea_plantillas`
--
ALTER TABLE `tarea_plantillas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  ADD CONSTRAINT `adjuntos_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adjuntos_ibfk_2` FOREIGN KEY (`id_usuario_subida`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `adjuntos_plantillas`
--
ALTER TABLE `adjuntos_plantillas`
  ADD CONSTRAINT `adjuntos_plantillas_ibfk_1` FOREIGN KEY (`id_plantilla`) REFERENCES `tarea_plantillas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adjuntos_plantillas_ibfk_2` FOREIGN KEY (`id_usuario_subida`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `arreglos_pago`
--
ALTER TABLE `arreglos_pago`
  ADD CONSTRAINT `arreglos_pago_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`),
  ADD CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `boletas`
--
ALTER TABLE `boletas`
  ADD CONSTRAINT `fk_boleta_creador` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `fk_boleta_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `credenciales`
--
ALTER TABLE `credenciales`
  ADD CONSTRAINT `credenciales_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cuotas_arreglo`
--
ALTER TABLE `cuotas_arreglo`
  ADD CONSTRAINT `cuotas_arreglo_ibfk_1` FOREIGN KEY (`arreglo_id`) REFERENCES `arreglos_pago` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cuotas_arreglo_ibfk_2` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cursos_activos`
--
ALTER TABLE `cursos_activos`
  ADD CONSTRAINT `cursos_activos_ibfk_1` FOREIGN KEY (`id_plan`) REFERENCES `plan_estudios` (`id_plan`),
  ADD CONSTRAINT `cursos_activos_ibfk_2` FOREIGN KEY (`id_profesor`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `enlaces`
--
ALTER TABLE `enlaces`
  ADD CONSTRAINT `enlaces_ibfk_1` FOREIGN KEY (`id_usuario_creador`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enlaces_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categorias_enlaces` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `expedientes_digitales`
--
ALTER TABLE `expedientes_digitales`
  ADD CONSTRAINT `expedientes_digitales_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`id_estudiante`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`id_curso_activo`) REFERENCES `cursos_activos` (`id_curso_activo`);

--
-- Filtros para la tabla `notas_rubros`
--
ALTER TABLE `notas_rubros`
  ADD CONSTRAINT `notas_rubros_ibfk_1` FOREIGN KEY (`id_matricula`) REFERENCES `matriculas` (`id_matricula`) ON DELETE CASCADE,
  ADD CONSTRAINT `notas_rubros_ibfk_2` FOREIGN KEY (`id_rubro`) REFERENCES `silabo_evaluacion` (`id_evaluacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`boleta_id`) REFERENCES `boletas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `plan_estudios`
--
ALTER TABLE `plan_estudios`
  ADD CONSTRAINT `plan_estudios_ibfk_1` FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id_programa`);

--
-- Filtros para la tabla `reuniones_zoom`
--
ALTER TABLE `reuniones_zoom`
  ADD CONSTRAINT `reuniones_zoom_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `saludos_enviados`
--
ALTER TABLE `saludos_enviados`
  ADD CONSTRAINT `saludos_enviados_ibfk_1` FOREIGN KEY (`plantilla_id`) REFERENCES `saludo_plantillas` (`id`),
  ADD CONSTRAINT `saludos_enviados_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `saludos_enviados_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `saludo_plantillas`
--
ALTER TABLE `saludo_plantillas`
  ADD CONSTRAINT `saludo_plantillas_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `saludo_categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `silabos`
--
ALTER TABLE `silabos`
  ADD CONSTRAINT `fk_silabo_plan` FOREIGN KEY (`id_plan`) REFERENCES `plan_estudios` (`id_plan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_silabo_profesor` FOREIGN KEY (`id_profesor`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `silabo_contenidos`
--
ALTER TABLE `silabo_contenidos`
  ADD CONSTRAINT `silabo_contenidos_ibfk_1` FOREIGN KEY (`id_silabo`) REFERENCES `silabos` (`id_silabo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `silabo_cronograma`
--
ALTER TABLE `silabo_cronograma`
  ADD CONSTRAINT `silabo_cronograma_ibfk_1` FOREIGN KEY (`id_silabo`) REFERENCES `silabos` (`id_silabo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `silabo_evaluacion`
--
ALTER TABLE `silabo_evaluacion`
  ADD CONSTRAINT `silabo_evaluacion_ibfk_1` FOREIGN KEY (`id_silabo`) REFERENCES `silabos` (`id_silabo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soporte`
--
ALTER TABLE `soporte`
  ADD CONSTRAINT `soporte_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `soporte_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `soporte_categorias` (`id`);

--
-- Filtros para la tabla `soporte_articulos_tags`
--
ALTER TABLE `soporte_articulos_tags`
  ADD CONSTRAINT `soporte_articulos_tags_ibfk_1` FOREIGN KEY (`articulo_id`) REFERENCES `soporte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `soporte_articulos_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `soporte_tags` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `fk_usuario_completado` FOREIGN KEY (`id_usuario_completado`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`id_asignado`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tarea_asignaciones`
--
ALTER TABLE `tarea_asignaciones`
  ADD CONSTRAINT `tarea_asignaciones_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarea_asignaciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarea_etiquetas`
--
ALTER TABLE `tarea_etiquetas`
  ADD CONSTRAINT `tarea_etiquetas_ibfk_1` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarea_etiquetas_ibfk_2` FOREIGN KEY (`id_etiqueta`) REFERENCES `etiquetas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tarea_plantillas`
--
ALTER TABLE `tarea_plantillas`
  ADD CONSTRAINT `tarea_plantillas_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
