<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de ver planes de estudio
// if(!has_permission('ver_planes_estudio')){
//     header("location: ../dashboard.php?error=No tienes permiso para ver planes de estudio");
//     exit;
// }

$plan_estudios = null;
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_plan = trim($_GET['id']);
    $sql = "SELECT pe.*, p.nombre_programa FROM plan_estudios pe JOIN programas p ON pe.id_programa = p.id_programa WHERE pe.id_plan = ?";
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

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Plan de Estudios</title>
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
                        <h3 class="text-center">Detalles del Plan de Estudios</h3>
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
                            <div class="mb-3">
                                <label class="form-label"><strong>Programa:</strong></label>
                                <p class="form-control-static"><?php echo htmlspecialchars($plan_estudios['nombre_programa']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Cuatrimestre:</strong></label>
                                <p class="form-control-static"><?php echo htmlspecialchars($plan_estudios['cuatrimestre']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Código:</strong></label>
                                <p class="form-control-static"><?php echo htmlspecialchars($plan_estudios['codigo']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Materia:</strong></label>
                                <p class="form-control-static"><?php echo htmlspecialchars($plan_estudios['materia']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Créditos:</strong></label>
                                <p class="form-control-static"><?php echo htmlspecialchars($plan_estudios['creditos']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Requisitos:</strong></label>
                                <p class="form-control-static"><?php echo nl2br(htmlspecialchars($plan_estudios['requisitos'])); ?></p>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="editar_plan_estudios.php?id=<?php echo $plan_estudios['id_plan']; ?>" class="btn btn-warning">Editar</a>
                                <a href="eliminar_plan_estudios.php?id=<?php echo $plan_estudios['id_plan']; ?>" class="btn btn-danger">Eliminar</a>
                                <a href="lista_cursos.php" class="btn btn-secondary">Volver a la Lista</a>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No se pudo cargar la información del plan de estudios.</p>
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