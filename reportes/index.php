<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Proteger la página
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$page_title = 'Módulo de Reportes';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reportes</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-pdf"></i> Generador de Reportes</h2>
        <p class="text-muted">Seleccione una categoría para generar el informe en PDF.</p>
    </div>

    <div class="row">
        <!-- Reportes de Tareas -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-list-check"></i> Tareas</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Informes detallados sobre el estado y asignación de tareas.</p>
                    <div class="d-grid gap-2">
                        <a href="generar_reporte.php?tipo=tareas_todas" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-file-pdf"></i> Todas las Tareas
                        </a>
                        <a href="generar_reporte.php?tipo=tareas_pendientes" class="btn btn-outline-warning text-dark" target="_blank">
                            <i class="bi bi-file-pdf"></i> Tareas Pendientes
                        </a>
                        <a href="generar_reporte.php?tipo=tareas_completadas" class="btn btn-outline-success" target="_blank">
                            <i class="bi bi-file-pdf"></i> Tareas Completadas
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reportes de Usuarios -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-people"></i> Usuarios</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Listados de personal y usuarios registrados en el sistema.</p>
                    <div class="d-grid gap-2">
                        <a href="generar_reporte.php?tipo=usuarios_lista" class="btn btn-outline-info text-dark" target="_blank">
                            <i class="bi bi-file-pdf"></i> Lista de Usuarios
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reportes de Credenciales -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-shield-lock"></i> Credenciales</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Informe de accesos y credenciales generadas.</p>
                    <div class="d-grid gap-2">
                        <a href="generar_reporte.php?tipo=credenciales_lista" class="btn btn-outline-dark" target="_blank">
                            <i class="bi bi-file-pdf"></i> Reporte de Credenciales
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reportes de Matrícula (Ejemplo de integración con otros módulos) -->
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-mortarboard"></i> Matrícula</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Resumen de estudiantes y programas académicos.</p>
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-outline-success disabled" target="_blank">
                            <i class="bi bi-file-pdf"></i> Estudiantes por Programa (Próximamente)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
