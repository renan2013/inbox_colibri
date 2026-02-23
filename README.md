# Proyecto: Colibrí

Sistema de Gestión de Procesos de Negocio (BPM) para la UNELA. Este proyecto maneja tareas, plantillas, usuarios, y módulos específicos como matrícula y soporte.

## Tecnologías Utilizadas

*   **Backend:** PHP
*   **Base de Datos:** MySQL
*   **Frontend:** HTML, CSS, JavaScript
*   **Editor de Texto Enriquecido:** TinyMCE

## Configuración del Entorno

1.  Asegúrate de tener un servidor web local (como XAMPP o WAMP) con PHP y MySQL.
2.  Importa la base de datos principal usando el archivo `sql/bpm.sql`.
3.  Revisa y ajusta las credenciales de la base de datos en `includes/db_connect.php` y `includes/config.php`.
4.  Coloca los archivos del proyecto en el directorio `htdocs` (o equivalente) de tu servidor web.

## Estructura del Proyecto

*   `/`: Archivos principales y de gestión de tareas.
*   `/includes`: Conexión a BD, configuración, y librerías (PHPMailer).
*   `/matricula`: Módulo para la gestión de expedientes y programas académicos.
*   `/saludo`: Módulo para la creación de tarjetas de saludo.
*   `/soporte`: Módulo para la gestión de tickets de soporte.
*   `/sql`: Archivos de migración y base de datos.
*   `/uploads`: Directorio para archivos subidos por los usuarios.

## Notas Adicionales

- El sistema utiliza PHPMailer para el envío de correos, configurado en `includes/email_sender.php`.
- La gestión de permisos y roles se define en `includes/permissions.php`.
