<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

$page_title = "Lista de Programas";
// Proteger la página
// if(!has_permission('ver_programas')){
//     require_once '../includes/config.php';
//     header("location: " . BASE_URL . "dashboard.php?error=No tienes permiso");
//     exit;
// }

$sql = "SELECT id_programa, nombre_programa, categoria FROM programas";
$result = $mysqli->query($sql);

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Programas</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Lista de Programas</h3>
        <a href="registrar_programa.php" class="btn btn-primary">Registrar Nuevo Programa</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Programa</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_programa']; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_programa']); ?></td>
                            <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                            <td>
                                <a href="ver_programa.php?id=<?php echo $row['id_programa']; ?>" class="btn btn-info btn-sm">Ver</a>
                                <a href="editar_programa.php?id=<?php echo $row['id_programa']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="#" onclick="confirmDeleteProgram(<?php echo $row['id_programa']; ?>)" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No hay programas registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDeleteProgram(id) {
    fetch('verificar_dependencias_programa.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.has_dependencies) {
                Swal.fire({
                    title: 'Acción Requerida',
                    text: 'Este programa tiene planes de estudio asociados. Por favor, elimine primero los planes de estudio para poder eliminar el programa.',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
            } else {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto! El programa se eliminará permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'eliminar_programa.php?id=' + id;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error al verificar dependencias:', error);
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un error al verificar las dependencias. Por favor, inténtalo de nuevo.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

<?php
if (isset($_SESSION['success'])) {
    $message = json_encode($_SESSION['success']);
    unset($_SESSION['success']);
    echo "Swal.fire({ title: '¡Éxito!', text: $message, icon: 'success', confirmButtonText: 'OK' });";
} elseif (isset($_SESSION['error'])) {
    $message = json_encode($_SESSION['error']);
    unset($_SESSION['error']);
    echo "Swal.fire({ title: 'Error', text: $message, icon: 'error', confirmButtonText: 'OK' });";
}
?>
</script>

<?php 
$result->free();
$mysqli->close();
include '../includes/footer.php'; 
?>