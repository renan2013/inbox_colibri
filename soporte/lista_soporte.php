<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Por ahora, permitimos a todos los usuarios gestionar soportes.
// En el futuro, se puede cambiar esto a un permiso específico.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Obtener todos los artículos de soporte para mostrarlos en la tabla
$sql_soportes = "SELECT s.id, s.titulo, sc.nombre as categoria FROM soporte s LEFT JOIN soporte_categorias sc ON s.categoria_id = sc.id ORDER BY s.fecha_creacion DESC";
$result_soportes = $mysqli->query($sql_soportes);

// Obtener todas las categorías de soporte (aunque no se usen para crear, pueden ser útiles para filtros futuros)
$sql_categorias = "SELECT id, nombre FROM soporte_categorias ORDER BY nombre ASC";
$result_categorias = $mysqli->query($sql_categorias);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Artículos de Soporte - Colibrí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Lista de Artículos de Soporte</h2>
    <p>Aquí puedes ver todos los artículos de la base de conocimiento.</p>

    <div class="row">
        <div class="col-md-12"> <!-- Changed to col-md-12 -->
            <div class="card">
                <div class="card-header">Artículos Existentes</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_soportes->num_rows > 0): ?>
                                    <?php while($soporte = $result_soportes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($soporte['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars($soporte['categoria']); ?></td>
                                            <td>
                                                <a href="https://renangalvan.net/inbox_colibri/soporte/ver_soporte.php?id=<?php echo $soporte['id']; ?>" class="btn btn-sm btn-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                                                <a href="https://renangalvan.net/inbox_colibri/soporte/editar_soporte.php?id=<?php echo $soporte['id']; ?>" class="btn btn-sm btn-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                                <a href="https://renangalvan.net/inbox_colibri/soporte/eliminar_soporte.php?id=<?php echo $soporte['id']; ?>" class="btn btn-sm btn-secondary" title="Eliminar"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hay artículos creados.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>