<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Dashboard Principal";
require_once 'includes/header.php'; // Incluye session, db, config, head, body
require_once 'includes/permissions.php';

// La verificación de login ahora está en el header, pero la lógica específica de la página se mantiene aquí.

// --- Logica para Estadisticas ---
$stats = ['pendiente' => 0, 'en_proceso' => 0, 'completada' => 0, 'total' => 0];
$sql_stats = "SELECT estado, COUNT(id) as count FROM tareas";
$where_clause = "";

if (!has_permission($mysqli, 'ver_dashboard_completo')) {
    $where_clause = " WHERE id_asignado = ?";
}
$sql_stats .= $where_clause . " GROUP BY estado";

if($stmt_stats = $mysqli->prepare($sql_stats)){
    if (!has_permission($mysqli, 'ver_dashboard_completo')) {
        $stmt_stats->bind_param("i", $_SESSION["id"]);
    }
    $stmt_stats->execute();
    $result_stats = $stmt_stats->get_result();
    while($row = $result_stats->fetch_assoc()){
        if(isset($stats[$row['estado']])){
            $stats[$row['estado']] = $row['count'];
        }
    }
    $stats['total'] = array_sum($stats);
    $stmt_stats->close();
}
// --- Fin Logica de Estadisticas ---


// --- Logica para Tareas Pendientes del Usuario Logueado ---
$tareas_pendientes_count = 0;
$nombre_usuario_logueado = $_SESSION['nombre'] ?? 'Usuario'; // Asume que el nombre esta en la sesion, si no, usa 'Usuario'

$sql_pendientes = "SELECT COUNT(t.id) AS tareas_pendientes
                   FROM tareas t
                   JOIN tarea_asignaciones ta ON t.id = ta.id_tarea
                   WHERE ta.id_usuario = ? AND t.estado != 'completada'";

if ($stmt_pendientes = $mysqli->prepare($sql_pendientes)) {
    $stmt_pendientes->bind_param("i", $_SESSION["id"]);
    $stmt_pendientes->execute();
    $result_pendientes = $stmt_pendientes->get_result();
    if ($row_pendientes = $result_pendientes->fetch_assoc()) {
        $tareas_pendientes_count = $row_pendientes['tareas_pendientes'];
    }
    $stmt_pendientes->close();
}
// --- Fin Logica Tareas Pendientes ---

// --- Lógica para Cumpleaños del Mes ---
$cumpleaneros = [];
if (has_permission($mysqli, 'ver_cumpleanos')) {
    $sql_cumple = "SELECT id, nombre, fecha_nacimiento FROM usuarios WHERE MONTH(fecha_nacimiento) = MONTH(CURDATE()) AND DAY(fecha_nacimiento) >= DAY(CURDATE()) ORDER BY DAY(fecha_nacimiento) ASC";
    if ($result_cumple = $mysqli->query($sql_cumple)) {
        while ($row = $result_cumple->fetch_assoc()) {
            $cumpleaneros[] = $row;
        }
    }
}
// --- Fin Lógica de Cumpleaños ---

// --- Logica para obtener todas las etiquetas ---
$etiquetas_disponibles = [];
$sql_etiquetas = "SELECT id, nombre FROM etiquetas ORDER BY nombre ASC";
if ($result_etiquetas = $mysqli->query($sql_etiquetas)) {
    while ($row = $result_etiquetas->fetch_assoc()) {
        $etiquetas_disponibles[] = $row;
    }
}
// --- Fin Logica para obtener etiquetas ---


// --- Logica de Filtros y Paginacion ---
$tasks_per_page = 30;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $tasks_per_page;

// Obtener etiquetas seleccionadas para el filtro
$etiquetas_seleccionadas = isset($_GET['etiquetas']) && is_array($_GET['etiquetas']) ? array_filter($_GET['etiquetas'], 'is_numeric') : [];

// --- Construccion de la consulta ---
$base_from = "FROM tareas t";
$join_clauses = [];
$where_clauses = [];
$bind_types = "";
$bind_params = [];
$current_user_id = $_SESSION["id"]; // Store session id in a variable

// Filtro por usuario si no tiene permisos de ver todo
if(!has_permission($mysqli, 'ver_dashboard_completo')){
    $join_clauses[] = "JOIN tarea_asignaciones ta ON t.id = ta.id_tarea";
    $where_clauses[] = "ta.id_usuario = ?";
    $bind_types .= "i";
    $bind_params[] = $current_user_id;
}

// Filtro por etiquetas
// Ajuste para select simple: convertimos el valor único a array para mantener la lógica existente
$etiquetas_seleccionadas = [];
if (isset($_GET['etiqueta']) && is_numeric($_GET['etiqueta'])) {
    $etiquetas_seleccionadas[] = $_GET['etiqueta'];
} elseif (isset($_GET['etiquetas']) && is_array($_GET['etiquetas'])) {
    // Compatibilidad con enlaces antiguos
    $etiquetas_seleccionadas = array_filter($_GET['etiquetas'], 'is_numeric');
}

if (!empty($etiquetas_seleccionadas)) {
    $join_clauses[] = "JOIN tarea_etiquetas te ON t.id = te.id_tarea";
    $placeholders = implode(',', array_fill(0, count($etiquetas_seleccionadas), '?'));
    $where_clauses[] = "te.id_etiqueta IN ($placeholders)";
    foreach ($etiquetas_seleccionadas as $id_etiqueta) {
        $bind_types .= 'i';
        $bind_params[] = $id_etiqueta;
    }
}

// Filtro por Año
// Si anio está vacío o no definido, mostramos TODO (o el año actual por defecto si prefieres restringir)
// Ajuste: Mostrar TODO si se selecciona la opción vacía, o filtrar si hay valor.
// Para mantener el comportamiento anterior de "año presente por defecto", lo dejamos así, 
// pero permitimos una opción "Todos" en el HTML si se quisiera.
$anio_seleccionado = isset($_GET['anio']) && is_numeric($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

// Solo aplicamos el filtro si el año es válido (> 0). Si queremos ver "todos", el usuario enviaría algo distinto o modificaríamos esto.
// Por ahora mantenemos la lógica estricta: Siempre filtra por un año.
$where_clauses[] = "YEAR(t.fecha_creacion) = ?";
$bind_types .= 'i';
$bind_params[] = $anio_seleccionado;

// Unificar joins y wheres
$final_joins = !empty($join_clauses) ? " " . implode(" ", array_unique($join_clauses)) : "";
$final_wheres = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- Consulta para obtener el total de tareas (para paginacion) ---
$sql_total_tasks = "SELECT COUNT(DISTINCT t.id) " . $base_from . $final_joins . $final_wheres;
$total_tasks = 0;
if($stmt_total = $mysqli->prepare($sql_total_tasks)){
    if (!empty($bind_params)) {
        $stmt_total->bind_param($bind_types, ...$bind_params);
    }
    $stmt_total->execute();
    $stmt_total->bind_result($total_tasks);
    $stmt_total->fetch();
    $stmt_total->close();
}
$total_pages = ceil($total_tasks / $tasks_per_page);
// --- Fin Logica de Paginacion ---


// --- Preparar la consulta de tareas (con paginacion) ---
$sql_tasks = "SELECT t.id, t.titulo, t.prioridad, t.estado, t.fecha_vencimiento " . $base_from . $final_joins . $final_wheres . " GROUP BY t.id ORDER BY t.fecha_creacion DESC LIMIT ? OFFSET ?";
$bind_types .= "ii"; // Anadir tipos para LIMIT y OFFSET
$bind_params[] = $tasks_per_page;
$bind_params[] = $offset;

if($stmt_tasks = $mysqli->prepare($sql_tasks)){
    if (!empty($bind_params)) {
        $stmt_tasks->bind_param($bind_types, ...$bind_params);
    }
    $stmt_tasks->execute();
    $result_tareas = $stmt_tasks->get_result();
}

?>
<body>

<?php include 'includes/navbar.php'; ?>

    <?php if(isset($_GET['success'])):
    ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Exito',
                text: '<?php echo htmlspecialchars($_GET['success']); ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): // Assuming there might be an error parameter as well
    ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($_GET['error']); ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    <?php endif; ?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Dashboard Principal</h3>
        <?php if(has_permission($mysqli, 'crear_tareas')):
        ?>
            <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Crear Tarea
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="seleccionar_plantilla.php">Desde Plantilla</a></li>
                    <li><a class="dropdown-item" href="crear_tarea.php">Tarea en Blanco</a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- Widgets de Cumpleaños y Estadísticas -->
    <div class="row mb-4">
        <?php if (has_permission($mysqli, 'ver_cumpleanos') && !empty($cumpleaneros)): ?>
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-cake2-fill"></i> Cumpleaños del Mes</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($cumpleaneros as $cumpleanero): ?>
                            <li class="list-group-item bg-primary text-white d-flex justify-content-between align-items-center">
                                <span>
                                    <strong><?php echo date("d", strtotime($cumpleanero['fecha_nacimiento'])); ?></strong> - <?php echo htmlspecialchars($cumpleanero['nombre']); ?>
                                </span>
                                <a href="saludo/preparar_tarjeta.php?usuario_id=<?php echo $cumpleanero['id']; ?>" class="btn btn-sm btn-light">Preparar Tarjeta</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tarjetas de Estadisticas Compactas -->
        <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <h6 class="card-title mb-0 small">Pendientes</h6>
                        <span class="fs-5 fw-bold"><?php echo $stats['pendiente']; ?></span>
                    </div>
                    <i class="bi bi-clock-history fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <h6 class="card-title mb-0 small">En Proceso</h6>
                        <span class="fs-5 fw-bold"><?php echo $stats['en_proceso']; ?></span>
                    </div>
                    <i class="bi bi-person-workspace fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <h6 class="card-title mb-0 small">Listas</h6>
                        <span class="fs-5 fw-bold"><?php echo $stats['completada']; ?></span>
                    </div>
                    <i class="bi bi-check2-circle fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <h6 class="card-title mb-0 small">Total</h6>
                        <span class="fs-5 fw-bold"><?php echo $stats['total']; ?></span>
                    </div>
                    <i class="bi bi-journal-check fs-4 opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Filtros Integrados (Etiquetas + Año) -->
        <div class="col-lg-4 col-md-12 mb-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-2 d-flex align-items-center">
                    <form action="dashboard.php" method="GET" class="w-100 d-flex gap-2">
                        <!-- Filtro Año -->
                        <select name="anio" class="form-select" style="max-width: 100px;" title="Filtrar por Año">
                            <?php 
                            $year_current = date('Y');
                            for($y = 2024; $y <= $year_current + 1; $y++): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($anio_seleccionado == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>

                        <!-- Filtro Etiquetas -->
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-white border-end-0 px-2"><i class="bi bi-tags-fill text-primary"></i></span>
                            <select name="etiqueta" class="form-select border-start-0" aria-label="Filtrar por etiqueta" style="height: 2.4rem; padding-top: 0.2rem; padding-bottom: 0.2rem; font-size: 0.9rem;">
                                <option value="">Todas las etiquetas</option>
                                <?php
                                $etiqueta_actual = isset($_GET['etiqueta']) ? $_GET['etiqueta'] : '';
                                foreach ($etiquetas_disponibles as $etiqueta_item) {
                                    $selected = ($etiqueta_item['id'] == $etiqueta_actual) ? 'selected' : '';
                                    echo '<option value="' . $etiqueta_item['id'] . '" ' . $selected . '>' . htmlspecialchars($etiqueta_item['nombre']) . '</option>';
                                }
                                ?>
                            </select>
                            <button class="btn btn-primary btn-sm" type="submit" title="Aplicar Filtros"><i class="bi bi-search"></i></button>
                            <?php if(!empty($etiqueta_actual) || $anio_seleccionado != date('Y')): ?>
                                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted fst-italic ms-2" style="font-size: 0.7rem;">Filtrar búsqueda</small>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Seccion de Tareas -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <?php echo (has_permission($mysqli, 'ver_dashboard_completo')) ? 'Todas las Tareas' : 'Mis Tareas'; ?>
                <span class="ms-3">| Hola, <strong><?php echo htmlspecialchars($nombre_usuario_logueado); ?></strong>, te quedan <strong><?php echo $tareas_pendientes_count; ?></strong> tareas por completar.</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titulo</th>
                            <th>Asignado a</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Vencimiento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result_tareas) && $result_tareas->num_rows > 0):
                        ?>
                            <?php $row_index = 0; // Inicializar contador de fila ?>
                            <?php while($tarea = $result_tareas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $offset + $row_index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($tarea['titulo']); ?></td>
                                    <td>
                                        <?php
                                        $assigned_users_display = [];
                                        $sql_get_assigned_names = "SELECT u.id, u.nombre FROM tarea_asignaciones ta JOIN usuarios u ON ta.id_usuario = u.id WHERE ta.id_tarea = ?";
                                        if ($stmt_get_assigned_names = $mysqli->prepare($sql_get_assigned_names)) {
                                            $stmt_get_assigned_names->bind_param("i", $tarea['id']);
                                            $stmt_get_assigned_names->execute();
                                            $result_assigned_names = $stmt_get_assigned_names->get_result();
                                            while ($assigned_user = $result_assigned_names->fetch_assoc()) {
                                                $nombre_asignado = htmlspecialchars($assigned_user['nombre']);
                                                $clases_adicionales = '';

                                                // Verificar si es el usuario logueado
                                                if (isset($_SESSION['id']) && $assigned_user['id'] == $_SESSION['id']) {
                                                    $clases_adicionales = 'bg-dark text-white p-1 rounded'; // Fondo negro, letras blancas, negrita, padding, redondeado
                                                }
                                                $assigned_users_display[] = "<span class=\"" . $clases_adicionales . "\">" . $nombre_asignado . "</span>";
                                            }
                                            $stmt_get_assigned_names->close();
                                        }
                                        echo !empty($assigned_users_display) ? implode(', ', $assigned_users_display) : 'No asignado';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $prioridad_texto = htmlspecialchars($tarea['prioridad']);
                                        $estado_tarea = $tarea['estado']; // Obtener el estado de la tarea

                                        $icon_class = '';
                                        $color_class = '';
                                        $animation_class = '';

                                        if ($estado_tarea == 'completada') {
                                            $icon_class = 'fas fa-check-circle';
                                            $color_class = 'text-success'; // O un gris si prefieres
                                            $animation_class = ''; // Sin animacion
                                        } else {
                                            switch ($prioridad_texto) {
                                                case 'baja':
                                                    $icon_class = 'fas fa-arrow-down';
                                                    $color_class = 'text-success';
                                                    $animation_class = ''; // Sin animacion
                                                    break;
                                                case 'media':
                                                    $icon_class = 'fas fa-minus';
                                                    $color_class = 'text-info'; // Azul
                                                    $animation_class = 'fa-beat-fade'; // Animacion de pulso/desvanecimiento
                                                    break;
                                                case 'alta':
                                                    $icon_class = 'fas fa-exclamation-triangle';
                                                    $color_class = 'text-danger'; // Rojo
                                                    $animation_class = 'fa-shake'; // Animacion de vibracion
                                                    break;
                                                default:
                                                    $icon_class = 'fas fa-question-circle'; // Icono por defecto
                                                    $color_class = 'text-muted';
                                                    $animation_class = '';
                                                    break;
                                            }
                                        }
                                        ?>
                                        <i class="<?php echo $icon_class; ?> <?php echo $color_class; ?> <?php echo $animation_class; ?>"></i>
                                        <?php echo $prioridad_texto; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $estado_clase = '';
                                            switch ($tarea['estado']) {
                                                case 'completada':
                                                    $estado_clase = 'bg-success';
                                                    break;
                                                case 'pendiente':
                                                    $estado_clase = 'bg-warning'; // CAMBIO: Naranja para pendiente
                                                    break;
                                                case 'en_proceso':
                                                    $estado_clase = 'bg-info';
                                                    break;
                                                case 'cancelada':
                                                    $estado_clase = 'bg-secondary';
                                                    break;
                                                default:
                                                    $estado_clase = 'bg-secondary'; // Default color
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($tarea['estado']); ?></span>
                                    </td>
                                    <td><?php echo (!empty($tarea['fecha_vencimiento']) && $tarea['fecha_vencimiento'] !== '0000-00-00') ? date("d/m/Y", strtotime($tarea['fecha_vencimiento'])) : 'N/A'; ?></td>
                                    <td>
                                        <a href="ver_tarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-secondary" title="Ver"><i class="bi bi-eye"></i></a>
                                        <?php if(has_permission($mysqli, 'editar_tareas_todas')):
                                        ?>
                                            <a href="editar_tarea.php?id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                        <?php endif; ?>
                                        <?php if(has_permission($mysqli, 'eliminar_tareas')):
                                        ?>
                                                                                        <a href="#" class="btn btn-sm btn-secondary" onclick="confirmDelete(event, 'eliminar_tarea.php?id=<?php echo $tarea['id']; ?>', '<?php echo htmlspecialchars($tarea['titulo']); ?>');" title="Eliminar"><i class="bi bi-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php $row_index++; // Incrementar contador de fila ?>
                            <?php endwhile; ?>
                        <?php else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay tareas para mostrar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Controles de Paginacion -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center mt-4">
                    <?php if ($current_page > 1):
                    ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Anterior</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++):
                    ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages):
                    ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Siguiente</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- Fin Controles de Paginacion -->
        </div>
    </div>

</div>

<script>
function confirmDelete(event, url, title) {
    event.preventDefault(); // Prevent the default link action
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Estás seguro de que quieres eliminar la tarea '${title}'? Esta accion es irreversible y eliminara tambien los adjuntos y comentarios asociados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url; // Redirect to delete if confirmed
        }
    });
}

function confirmDeleteProgram(id, nombrePrograma) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¡No podrás revertir esto! Se eliminará el programa '${nombrePrograma}' y todos sus cursos asociados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'matricula/eliminar_programa.php?id=' + id;
        }
    });
}
</script>
<?php include 'includes/footer.php'; // Incluye los JS principales, el footer y cierra </body> y </html> ?>
