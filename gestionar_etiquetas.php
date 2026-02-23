<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// TODO: Crear el permiso 'gestionar_etiquetas' en la base de datos.
if (!has_permission($mysqli, 'gestionar_plantillas')) { // Usando un permiso existente temporalmente
    header("Location: dashboard.php");
    exit();
}

// Obtener todas las etiquetas de la base de datos
$sql = "SELECT id, nombre, fecha_creacion FROM etiquetas ORDER BY fecha_creacion DESC";
$result = $mysqli->query($sql);

$etiquetas = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $etiquetas[] = $row;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Etiquetas - BPM Unela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Gestionar Etiquetas</h2>
        <a href="crear_etiqueta.php" class="btn btn-primary mb-3">Crear Nueva Etiqueta</a>

        <?php if (!empty($etiquetas)):
            ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etiquetas as $etiqueta):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($etiqueta['id']); ?></td>
                                <td><?php echo htmlspecialchars($etiqueta['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($etiqueta['fecha_creacion']); ?></td>
                                <td>
                                    <a href="editar_etiqueta.php?id=<?php echo $etiqueta['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="javascript:void(0);" onclick="confirmarEliminacion(<?php echo $etiqueta['id']; ?>)" class="btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else:
            ?>
            <div class="alert alert-info" role="alert">
                No hay etiquetas registradas.
            </div>
        <?php endif; ?>
    </div>

    <script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡Eliminarás esta etiqueta!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'eliminar_etiqueta.php?id=' + id;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>