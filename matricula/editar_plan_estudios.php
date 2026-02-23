<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de editar planes de estudio
// if(!has_permission('editar_planes_estudio')){
//     header("location: ../dashboard.php?error=No tienes permiso para editar planes de estudio");
//     exit;
// }

$plan_estudios = null;
$programas = [];

// Obtener programas para el dropdown
$sql_programas = "SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa";
$result_programas = $mysqli->query($sql_programas);
while($row = $result_programas->fetch_assoc()) {
    $programas[] = $row;
}

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_plan = trim($_GET['id']);
    $sql = "SELECT * FROM plan_estudios WHERE id_plan = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $id_plan;
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $plan_estudios = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Plan de estudios no encontrado.";
                header("location: lista_cursos.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Error al ejecutar la consulta.";
            header("location: lista_cursos.php");
            exit;
        }
        $stmt->close();
    }
} else {
    $_SESSION['error'] = "ID de plan de estudios no especificado.";
    header("location: lista_cursos.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Plan de Estudios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-light">
                    <div class="card-header">
                        <h3 class="text-center">Editar Plan de Estudios</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php 
                        if(isset($_SESSION['error'])):
                        ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                echo $_SESSION['error']; 
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php 
                        endif; 
                        if(isset($_SESSION['success'])):
                        ?>
                            <div class="alert alert-success" role="alert">
                                <?php 
                                echo $_SESSION['success']; 
                                unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($plan_estudios): ?>
                        <form action="editar_plan_estudios_process.php" method="post">
                            <input type="hidden" name="id_plan" value="<?php echo $plan_estudios['id_plan']; ?>">
                            <div class="mb-3">
                                <label for="id_programa" class="form-label">Programa</label>
                                <select name="id_programa" id="id_programa" class="form-select" required>
                                    <option value="">Seleccione un programa...</option>
                                    <?php
                                    foreach ($programas as $programa_option) {
                                        $selected = ($programa_option['id_programa'] == $plan_estudios['id_programa']) ? 'selected' : '';
                                        echo '<option value="' . $programa_option['id_programa'] . '" ' . $selected . '>' . htmlspecialchars($programa_option['nombre_programa']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="cuatrimestre" class="form-label">Cuatrimestre</label>
                                <input type="text" name="cuatrimestre" id="cuatrimestre" class="form-control" value="<?php echo htmlspecialchars($plan_estudios['cuatrimestre']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" name="codigo" id="codigo" class="form-control" value="<?php echo htmlspecialchars($plan_estudios['codigo']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="materia" class="form-label">Materia</label>
                                <input type="text" name="materia" id="materia" class="form-control" value="<?php echo htmlspecialchars($plan_estudios['materia']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="creditos" class="form-label">Créditos</label>
                                <input type="number" name="creditos" id="creditos" class="form-control" value="<?php echo htmlspecialchars($plan_estudios['creditos']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="requisitos" class="form-label">Requisitos</label>
                                <textarea name="requisitos" id="requisitos" class="form-control" rows="3"><?php echo htmlspecialchars($plan_estudios['requisitos']); ?></textarea>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <a href="ver_plan_estudios.php?id=<?php echo $plan_estudios['id_plan']; ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                        <?php else: ?>
                            <p class="text-center">No se pudo cargar la información del plan de estudios para editar.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../includes/footer.php'; ?>
</body>
</html>