<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

$page_title = "Añadir Plan de Estudios";
// Proteger la página
// if(!has_permission('anadir_plan_estudios')){
//     require_once '../includes/config.php';
//     header("location: " . BASE_URL . "dashboard.php?error=No tienes permiso");
//     exit;
// }

// Obtener programas para el dropdown
$sql_programas = "SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa";
$result_programas = $mysqli->query($sql_programas);

// Obtener niveles para el dropdown
$sql_niveles = "SELECT id, nivel, precio FROM precios_cursos_conesup ORDER BY nivel";
$result_niveles = $mysqli->query($sql_niveles);

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrar Curso</li>
        </ol>
    </nav>
</div>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-light">
                <div class="card-header">
                    <h3 class="text-center">Añadir Curso a Programa</h3>
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
                    <form action="anadir_plan_estudios_process.php" method="post">
                        <div class="mb-3">
                            <label for="id_programa" class="form-label">Programa</label>
                            <select name="id_programa" id="id_programa" class="form-select" required>
                                <option value="">Seleccione un programa...</option>
                                <?php
                                if ($result_programas && $result_programas->num_rows > 0) {
                                    while($programa = $result_programas->fetch_assoc()) {
                                        echo '<option value="' . $programa['id_programa'] . '">' . htmlspecialchars($programa['nombre_programa']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="id_precio_curso" class="form-label">Nivel Académico</label>
                            <select name="id_precio_curso" id="id_precio_curso" class="form-select" required>
                                <option value="" data-precio="0.00">Seleccione un nivel...</option>
                                <?php
                                if ($result_niveles && $result_niveles->num_rows > 0) {
                                    while($nivel = $result_niveles->fetch_assoc()) {
                                        echo '<option value="' . $nivel['id'] . '" data-precio="' . htmlspecialchars($nivel['precio']) . '">' . htmlspecialchars($nivel['nivel']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="precio_display" class="form-label">Precio</label>
                            <input type="text" id="precio_display" class="form-control" placeholder="0.00" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="cuatrimestre" class="form-label">Periodo</label>
                            <select name="cuatrimestre" id="cuatrimestre" class="form-select" required>
                                <option value="">Seleccione el Periodo</option>
                                <option value="I Cuatrimestre">I Cuatrimestre</option>
                                <option value="II Cuatrimestre">II Cuatrimestre</option>
                                <option value="III Cuatrimestre">III Cuatrimestre</option>
                                <option value="IV Cuatrimestre">IV Cuatrimestre</option>
                                <option value="V Cuatrimestre">V Cuatrimestre</option>
                                <option value="VI Cuatrimestre">VI Cuatrimestre</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" name="codigo" id="codigo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="materia" class="form-label">Materia</label>
                            <input type="text" name="materia" id="materia" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="creditos" class="form-label">Créditos</label>
                            <input type="number" name="creditos" id="creditos" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="requisitos" class="form-label">Requisitos</label>
                            <textarea name="requisitos" id="requisitos" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Añadir Curso al Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nivelSelect = document.getElementById('id_precio_curso');
    const precioDisplay = document.getElementById('precio_display');

    nivelSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const precio = selectedOption.getAttribute('data-precio');
        
        // Formatear el precio a dos decimales y mostrarlo
        precioDisplay.value = parseFloat(precio).toFixed(2);
    });
});
</script>

<?php include '../includes/footer.php'; ?>
