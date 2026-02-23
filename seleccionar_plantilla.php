<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'gestionar_plantillas')){
    header("location: dashboard.php?error=No tienes permiso para gestionar plantillas");
    exit;
}

// Obtener todas las plantillas
$sql_plantillas = "SELECT id, titulo FROM tarea_plantillas ORDER BY titulo ASC";
$result_plantillas = $mysqli->query($sql_plantillas);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Plantilla - BPM Unela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<?php if(isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '<?php echo htmlspecialchars($_SESSION['success']); ?>',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo htmlspecialchars($_SESSION['error']); ?>',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Opciones de Plantillas</h3>
                </div>
                <div class="card-body text-center">
                    <p>¿Qué deseas hacer con las plantillas?</p>
                    <a href="gestionar_plantillas.php" class="btn btn-success me-2"><i class="bi bi-plus-circle"></i> Crear/Administrar Plantillas</a>
                    <a href="#" class="btn btn-info disabled"><i class="bi bi-file-earmark-text"></i> Usar Plantilla para Tarea</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Crear Tarea desde Plantilla</h3>
                </div>
                <div class="card-body">
                    <p>Selecciona una plantilla para pre-rellenar el formulario de la nueva tarea.</p>
                    <form action="crear_tarea.php" method="get">
                        <div class="mb-3">
                            <label for="plantilla_id" class="form-label">Plantillas Disponibles</label>
                            <select name="plantilla_id" id="plantilla_id" class="form-select" required>
                                <option value="">Elige una plantilla...</option>
                                <?php
                                if ($result_plantillas->num_rows > 0) {
                                    while($plantilla = $result_plantillas->fetch_assoc()) {
                                        echo '<option value="' . $plantilla['id'] . '">' . htmlspecialchars($plantilla['titulo']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Usar esta Plantilla</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>