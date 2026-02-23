<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de editar programas
// if(!has_permission('editar_programas')){
//     header("location: ../dashboard.php?error=No tienes permiso para editar programas");
//     exit;
// }

$programa = null;
if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_programa = trim($_GET['id']);
    $sql = "SELECT * FROM programas WHERE id_programa = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $param_id);
        $param_id = $id_programa;
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $programa = $result->fetch_assoc();
            } else {
                $_SESSION['error'] = "Programa no encontrado.";
                header("location: lista_programas.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Error al ejecutar la consulta.";
            header("location: lista_programas.php");
            exit;
        }
        $stmt->close();
    }
} else {
    $_SESSION['error'] = "ID de programa no especificado.";
    header("location: lista_programas.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Programa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card bg-light">
                    <div class="card-header">
                        <h3 class="text-center">Editar Programa</h3>
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

                        <?php if ($programa): ?>
                        <form action="editar_programa_process.php" method="post">
                            <input type="hidden" name="id_programa" value="<?php echo $programa['id_programa']; ?>">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="nombre_programa" class="form-label">Nombre del Programa</label>
                                    <input type="text" name="nombre_programa" id="nombre_programa" class="form-control" value="<?php echo htmlspecialchars($programa['nombre_programa']); ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <select name="categoria" id="categoria" class="form-select">
                                        <option value="">Seleccione una categoría...</option>
                                        <option value="Bachillerato" <?php echo ($programa['categoria'] == 'Bachillerato') ? 'selected' : ''; ?>>Bachillerato</option>
                                        <option value="Maestría" <?php echo ($programa['categoria'] == 'Maestría') ? 'selected' : ''; ?>>Maestría</option>
                                        <option value="Doctorado" <?php echo ($programa['categoria'] == 'Doctorado') ? 'selected' : ''; ?>>Doctorado</option>
                                        <option value="Carrera Técnica" <?php echo ($programa['categoria'] == 'Carrera Técnica') ? 'selected' : ''; ?>>Carrera Técnica</option>
                                        <option value="Curso Libre" <?php echo ($programa['categoria'] == 'Curso Libre') ? 'selected' : ''; ?>>Curso Libre</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="costo_matricula" class="form-label">Costo de Matrícula</label>
                                    <input type="number" name="costo_matricula" id="costo_matricula" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($programa['costo_matricula']); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="informacion" class="form-label">Información</label>
                                    <textarea name="informacion" id="informacion" class="form-control" rows="5"><?php echo htmlspecialchars($programa['informacion']); ?></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="oferta" class="form-label">Oferta</label>
                                    <textarea name="oferta" id="oferta" class="form-control" rows="5"><?php echo htmlspecialchars($programa['oferta']); ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="perfil" class="form-label">Perfil</label>
                                    <textarea name="perfil" id="perfil" class="form-control" rows="5"><?php echo htmlspecialchars($programa['perfil']); ?></textarea>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <a href="ver_programa.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                        <?php else: ?>
                            <p class="text-center">No se pudo cargar la información del programa para editar.</p>
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