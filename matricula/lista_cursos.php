<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

$page_title = "Lista de Cursos";
// Proteger la página
// if(!has_permission($mysqli, 'ver_planes_estudio')){
//     require_once "../includes/config.php";
//     header("location: " . BASE_URL . "dashboard.php?error=No tienes permiso");
//     exit;
// }

$selected_programa_id = $_GET['programa_id'] ?? null;

// Obtener todos los programas para el filtro
$programas = [];
$sql_programas = "SELECT id_programa, nombre_programa FROM programas ORDER BY nombre_programa ASC";
$result_programas = $mysqli->query($sql_programas);
if ($result_programas) {
    while ($row = $result_programas->fetch_assoc()) {
        $programas[] = $row;
    }
}

$sql = "SELECT pe.id_plan, p.nombre_programa, pe.cuatrimestre, pe.codigo, pe.materia, pe.creditos 
        FROM plan_estudios pe 
        JOIN programas p ON pe.id_programa = p.id_programa";

if ($selected_programa_id && is_numeric($selected_programa_id)) {
    $sql .= " WHERE pe.id_programa = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $selected_programa_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($sql);
}

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Lista de Cursos</h3>
        <a href="anadir_plan_estudios.php" class="btn btn-primary">Añadir Nuevo Curso</a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">Filtrar Cursos</div>
        <div class="card-body">
            <form action="lista_cursos.php" method="GET" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <label for="programa_id" class="form-label visually-hidden">Programa</label>
                    <select name="programa_id" id="programa_id" class="form-select">
                        <option value="">Todos los Programas</option>
                        <?php foreach ($programas as $programa): ?>
                            <option value="<?php echo htmlspecialchars($programa['id_programa']); ?>"
                                <?php echo ($selected_programa_id == $programa['id_programa']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($programa['nombre_programa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Programa</th>
                    <th>Cuatrimestre</th>
                    <th>Código</th>
                    <th>Materia</th>
                    <th>Créditos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id_plan']; ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_programa']); ?></td>
                            <td><?php echo htmlspecialchars($row['cuatrimestre']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($row['materia']); ?></td>
                            <td><?php echo htmlspecialchars($row['creditos']); ?></td>
                            <td>
                                <a href="ver_plan_estudios.php?id=<?php echo $row['id_plan']; ?>" class="btn btn-info btn-sm">Ver</a>
                                <a href="editar_plan_estudios.php?id=<?php echo $row['id_plan']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="#" onclick="confirmDeletePlan(<?php echo $row['id_plan']; ?>)" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No hay cursos registrados para el filtro seleccionado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function confirmDeletePlan(id) {
    fetch('verificar_dependencias_curso.php?id=' + id)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error de red o del servidor.');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                Swal.fire({
                    title: 'Error de Verificación',
                    text: data.error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } else if (data.has_dependencies) {
                Swal.fire({
                    title: 'Acción Bloqueada',
                    text: 'Este curso no se puede eliminar mientras esté siendo usado en otra parte (ej: en un curso activo).',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
            } else {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto! El curso se eliminará permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'eliminar_plan_estudios.php?id=' + id;
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error en la llamada AJAX:', error);
            Swal.fire({
                title: 'Error de Comunicación',
                text: 'No se pudo comunicar con el servidor para verificar las dependencias. Revisa la consola para más detalles.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}
</script>
<?php 
if(isset($stmt)) $stmt->close();
$result->free();
include '../includes/footer.php'; 
?>