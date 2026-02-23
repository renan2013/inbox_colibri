<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'gestionar_plantillas')){
    header("location: dashboard.php?error=No tienes permiso para gestionar plantillas");
    exit;
}

// Obtener todas las plantillas para mostrarlas en la tabla
$sql_plantillas = "SELECT id, titulo, prioridad_default FROM tarea_plantillas ORDER BY fecha_creacion DESC";
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
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Gestionar Plantillas de Tareas</h2>
    <p>Selecciona una plantilla existente o crea una nueva.</p>

    <?php if(isset($_SESSION['success'])):
        ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])):
        ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <a href="gestionar_plantillas.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Crear Nueva Plantilla</a>
    </div>

    <div class="card">
        <div class="card-header">
            Plantillas Existentes
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Prioridad por Defecto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_plantillas->num_rows > 0):
                            ?>
                            <?php while($plantilla = $result_plantillas->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($plantilla['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($plantilla['prioridad_default']); ?></td>
                                    <td>
                                        <a href="editar_plantilla.php?id=<?php echo $plantilla['id']; ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Editar</a>
                                        <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $plantilla['id']; ?>, '<?php echo addslashes($plantilla['titulo']); ?>')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No hay plantillas creadas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmarEliminacion(id, titulo) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Vas a eliminar la plantilla '${titulo}'. ¡Esta acción es irreversible!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminar_plantilla.php?id=' + id;
            }
        });
    }
</script>
</body>
</html>