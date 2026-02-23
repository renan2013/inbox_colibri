<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

$page_title = "Registrar Nuevo Programa";
// Proteger la página: solo para usuarios con permiso de crear programas
// if(!has_permission('crear_programas')){
//     require_once '../includes/config.php';
//     header("location: " . BASE_URL . "dashboard.php?error=No tienes permiso");
//     exit;
// }

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="lista_programas.php">Programas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrar Nuevo Programa</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-header">
                    <h3 class="text-center">Registrar Nuevo Programa</h3>
                </div>
                <div class="card-body p-4">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="registrar_programa_process.php" method="post">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="nombre_programa" class="form-label">Nombre del Programa</label>
                                <input type="text" name="nombre_programa" id="nombre_programa" class="form-control" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select name="categoria" id="categoria" class="form-select">
                                    <option value="">Seleccione una categoría...</option>
                                    <option value="Bachillerato">Bachillerato</option>
                                    <option value="Maestría">Maestría</option>
                                    <option value="Doctorado">Doctorado</option>
                                    <option value="Carrera Técnica">Carrera Técnica</option>
                                    <option value="Curso Libre">Curso Libre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="costo_matricula" class="form-label">Costo de Matrícula</label>
                                <input type="number" name="costo_matricula" id="costo_matricula" class="form-control" step="0.01" min="0" value="0">
                            </div>
                        </div>

                       
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="oferta" class="form-label">Oferta</label>
                                <textarea name="oferta" id="oferta" class="form-control" rows="5"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="perfil" class="form-label">Perfil</label>
                                <textarea name="perfil" id="perfil" class="form-control" rows="5"></textarea>
                            </div>
                            <div class="col-4 mb-3">
                                <label for="informacion" class="form-label">Información</label>
                                <textarea name="informacion" id="informacion" class="form-control" rows="5"></textarea>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Registrar Programa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
