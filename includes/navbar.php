<?php
require_once 'permissions.php';
require_once 'config.php'; // Incluir el archivo de configuración para BASE_URL

// Lógica para determinar la página activa
$current_uri = $_SERVER['REQUEST_URI'];

function is_active($uris, $current_uri) {
    foreach ((array)$uris as $uri) {
        if (strpos($current_uri, $uri) !== false) {
            return 'active';
        }
    }
    return '';
}

// Definir las páginas para cada sección del menú
$uris_gestion_curso = ['/mis_cursos/', 'recursos_profesor.php'];
$uris_soporte = ['/soporte/'];
$uris_registro = [
    'registrar_usuario.php', 'gestionar_usuarios.php', '/matric/', 
    'gestionar_grupos.php', '/precios_conesup/', 'gestionar_etiquetas.php', '/saludo/', 
    'gestionar_plantillas.php', '/reportes/', 'boleta/generar_boleta.php'
];

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard.php">
            <img src="<?php echo BASE_URL; ?>imgs/SVG/logo_blanco.svg" alt="BPM Unela Logo" style="max-height: 40px; margin-right: 10px;"><span style="border-left: 1px solid #ccc; height: 30px; margin: 0 10px; vertical-align: middle;"></span> <span class="fs-6">Gestor de Proyectos</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_active('dashboard.php', $current_uri); ?>" href="<?php echo BASE_URL; ?>dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    </li>
                    <?php if(has_permission($mysqli, 'ver_dashboard_completo')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo is_active('gantt_tareas.php', $current_uri); ?>" href="<?php echo BASE_URL; ?>gantt_tareas.php">Gantt Tareas</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($uris_gestion_curso, $current_uri); ?>" href="#" id="navbarGestionarCursoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Gestionar Curso
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarGestionarCursoDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>mis_cursos/index.php"><i class="bi bi-book"></i> Mis Cursos</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>recursos_profesor.php"><i class="bi bi-collection"></i> Recursos</a></li>
                        </ul>
                    </li>

                    <?php if(has_permission($mysqli, 'gestionar_usuarios')): // Asumiendo que soporte es admin ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($uris_soporte, $current_uri); ?>" href="#" id="navbarSoporteDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Soporte
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarSoporteDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>soporte/gestionar_soportes.php">Gestionar Soporte</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>soporte/gestionar_categorias_soporte.php">Gestionar Categorías</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>soporte/lista_soporte.php">Lista de Soporte</a></li>
                            <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1) : ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>soporte/gestionar_claves.php">Gestionar Claves</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>credenciales/index.php">Gestor de Credenciales</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if(has_permission($mysqli, 'ver_menu_registro')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo is_active($uris_registro, $current_uri); ?>" href="#" id="navbarRegistroDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Registro
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarRegistroDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>boleta/generar_boleta.php"><i class="bi bi-receipt"></i> Generar Boleta de Pago</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>registrar_usuario.php">Registrar Usuario</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>gestionar_usuarios.php">Lista de Usuarios</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/crear_expediente_digital.php">Crear Expediente Digital</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/registrar_programa.php">Registrar Programa</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/anadir_plan_estudios.php">Registrar Curso</a></li>                        
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>gestionar_grupos.php">Gestionar Grupos</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>precios_conesup/index.php">Precios de Cursos</a></li>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/lista_programas.php">Lista de Programas</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/lista_cursos.php">Lista de Cursos</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>matricula/generar_programa_completo.php">Ver Programas Completos</a></li>

                            <?php if(has_permission($mysqli, 'gestionar_plantillas') || has_permission($mysqli, 'gestionar_saludos')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <?php if(has_permission($mysqli, 'gestionar_etiquetas')): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>gestionar_etiquetas.php">Etiquetas de Tareas</a></li>
                                <?php endif; ?>
                                
                                <?php if(has_permission($mysqli, 'gestionar_saludos')): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Módulo Saludos</h6></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>saludo/gestionar_plantillas.php">Gestionar Plantillas</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>saludo/gestionar_categorias.php">Gestionar Categorías</a></li>
                                <?php endif; ?>
                                <?php if(has_permission($mysqli, 'gestionar_plantillas')): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>gestionar_plantillas.php">Plantillas de Tareas</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>reportes/index.php"><i class="bi bi-file-earmark-pdf"></i> Reportes del Sistema</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>