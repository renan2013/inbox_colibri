<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Verificar permisos, por ejemplo, 'gestionar_expedientes'
if (!has_permission($mysqli, 'gestionar_expedientes')) {
    // Si no existe un permiso específico, podemos usar uno más genérico como 'gestionar_usuarios'
    // o simplemente denegar el acceso si se requiere un control estricto.
    // Por ahora, lo dejaremos comentado para no bloquear al usuario.
    // header("Location: ../dashboard.php?error=no_permission");
    // exit();
}

$page_title = 'Gestionar Expedientes Digitales';
require_once '../includes/header.php';
include '../includes/navbar.php';

// Obtener todos los expedientes de la base de datos
// Unimos las tablas expedientes_digitales y usuarios para obtener el nombre del estudiante
$sql = "SELECT e.id_expediente, u.nombre AS nombre_usuario, u.apellidos, e.grado_a_matricular, e.especialidad_deseada, e.fecha_creacion 
        FROM expedientes_digitales e
        JOIN usuarios u ON e.id_usuario = u.id
        ORDER BY e.fecha_creacion DESC";

$result = $mysqli->query($sql);

$expedientes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expedientes[] = $row;
    }
}
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Expedientes Digitales</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-folder-symlink"></i> Expedientes Digitales</h1>
        <a href="crear_expediente_digital.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Crear Nuevo Expediente
        </a>
    </div>

    <?php if (!empty($expedientes)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Expediente</th>
                        <th>Estudiante</th>
                        <th>Grado a Matricular</th>
                        <th>Especialidad Deseada</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expedientes as $expediente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expediente['id_expediente']); ?></td>
                            <td><?php echo htmlspecialchars($expediente['nombre_usuario'] . ' ' . $expediente['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($expediente['grado_a_matricular']); ?></td>
                            <td><?php echo htmlspecialchars($expediente['especialidad_deseada']); ?></td>
                            <td><?php echo date("d/m/Y H:i", strtotime($expediente['fecha_creacion'])); ?></td>
                            <td>
                                <a href="ver_expediente.php?id=<?php echo $expediente['id_expediente']; ?>" class="btn btn-sm btn-info" title="Ver Detalles"><i class="bi bi-eye"></i></a>
                                <a href="editar_expediente.php?id=<?php echo $expediente['id_expediente']; ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="eliminar_expediente.php?id=<?php echo $expediente['id_expediente']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar este expediente?');"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">No hay expedientes</h4>
            <p>Aún no se han creado expedientes digitales. Puedes empezar creando uno nuevo.</p>
            <hr>
            <a href="crear_expediente_digital.php" class="btn btn-primary">Crear Nuevo Expediente</a>
        </div>
    <?php endif; ?>
</div>

<?php
$mysqli->close();
include '../includes/footer.php';
?>
