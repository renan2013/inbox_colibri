<?php
session_start();
require_once "includes/config.php";
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$page_title = 'Recursos para Profesores';
require_once 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-collection-fill text-primary"></i> Recursos para Profesores</h2>
    </div>

    <div class="row">
        <!-- Ejemplo de Recurso 1 (Hardcoded) -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
            <a href="https://www.unela.ac.cr/campus/" target="_blank" class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm text-center card-hover">
                    <img src="https://via.placeholder.com/150x100.png?text=UNELA" class="card-img-top" alt="Campus Virtual">
                    <div class="card-body">
                        <h6 class="card-title">Campus Virtual</h6>
                        <p class="card-text small">Acceso a la plataforma principal de la universidad.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ejemplo de Recurso 2 (Hardcoded) -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
            <a href="https://www.google.com/drive/" target="_blank" class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm text-center card-hover">
                    <img src="https://via.placeholder.com/150x100.png?text=Drive" class="card-img-top" alt="Google Drive">
                    <div class="card-body">
                        <h6 class="card-title">Google Drive</h6>
                        <p class="card-text small">Almacenamiento en la nube para tus archivos.</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ejemplo de Recurso 3 (Hardcoded) -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
            <a href="https://www.canva.com/" target="_blank" class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm text-center card-hover">
                    <img src="https://via.placeholder.com/150x100.png?text=Canva" class="card-img-top" alt="Canva">
                    <div class="card-body">
                        <h6 class="card-title">Canva</h6>
                        <p class="card-text small">Herramienta de diseño para crear presentaciones.</p>
                    </div>
                </div>
            </a>
        </div>
        
        <!-- Ejemplo de Recurso 4 (Hardcoded) -->
        <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
            <a href="https://www.youtube.com/" target="_blank" class="text-decoration-none text-dark">
                <div class="card h-100 shadow-sm text-center card-hover">
                    <img src="https://via.placeholder.com/150x100.png?text=YouTube" class="card-img-top" alt="YouTube">
                    <div class="card-body">
                        <h6 class="card-title">YouTube</h6>
                        <p class="card-text small">Plataforma de videos para contenido educativo.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
    transition: all 0.2s ease-in-out;
}
</style>

<?php include 'includes/footer.php'; ?>
