<?php
ob_start(); // Start output buffering
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db_connect.php';
require_once 'includes/permissions.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    require_once 'includes/config.php';
    header("Location: " . BASE_URL . "login.php");
    exit();
}

$page_title = 'Gantt de Tareas';
$user_id = $_SESSION['id'];

// --- Logica para obtener todas las etiquetas ---
$etiquetas_disponibles = [];
$sql_etiquetas_fetch = "SELECT id, nombre FROM etiquetas ORDER BY nombre ASC";
if ($result_etiquetas_fetch = $mysqli->query($sql_etiquetas_fetch)) {
    while ($row_etiqueta = $result_etiquetas_fetch->fetch_assoc()) {
        $etiquetas_disponibles[] = $row_etiqueta;
    }
}
// --- Fin Logica para obtener etiquetas ---

// Obtener tareas para el Gantt
// Filtro por etiquetas (Lógica unificada con Dashboard)
$etiquetas_seleccionadas = [];
if (isset($_GET['etiqueta']) && is_numeric($_GET['etiqueta'])) {
    $etiquetas_seleccionadas[] = $_GET['etiqueta'];
} elseif (isset($_GET['etiquetas']) && is_array($_GET['etiquetas'])) {
    $etiquetas_seleccionadas = array_filter($_GET['etiquetas'], 'is_numeric');
}

// Filtro por Año (Por defecto: Año Actual)
$anio_seleccionado = isset($_GET['anio']) && is_numeric($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

$sql_tareas = "SELECT t.id, t.titulo, t.estado, t.fecha_creacion, t.fecha_vencimiento, GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS asignados_nombres 
               FROM tareas t 
               LEFT JOIN tarea_asignaciones ta ON t.id = ta.id_tarea
               LEFT JOIN usuarios u ON ta.id_usuario = u.id";

$where_clauses = [];
$bind_types = "";
$bind_params = [];

if (!empty($etiquetas_seleccionadas)) {
    $sql_tareas .= " JOIN tarea_etiquetas te ON t.id = te.id_tarea";
    $placeholders = implode(',', array_fill(0, count($etiquetas_seleccionadas), '?'));
    $where_clauses[] = "te.id_etiqueta IN ($placeholders)";
    foreach ($etiquetas_seleccionadas as $id_etiqueta) {
        $bind_types .= 'i';
        $bind_params[] = $id_etiqueta;
    }
}

// Aplicar filtro de año
$where_clauses[] = "YEAR(t.fecha_creacion) = ?";
$bind_types .= 'i';
$bind_params[] = $anio_seleccionado;

if (!empty($where_clauses)) {
    $sql_tareas .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_tareas .= " GROUP BY t.id ORDER BY t.fecha_creacion ASC";

if (!empty($bind_params)) {
    $stmt = $mysqli->prepare($sql_tareas);
    $stmt->bind_param($bind_types, ...$bind_params);
    $stmt->execute();
    $result_tareas = $stmt->get_result();
} else {
    $result_tareas = $mysqli->query($sql_tareas);
}

$tasks_data = [];
if ($result_tareas) {
    while ($row = $result_tareas->fetch_assoc()) {
        $start_date = new DateTime($row['fecha_creacion']);
        $start_date->setTime(0, 0, 0);

        $end_date = new DateTime($row['fecha_vencimiento']);
        $end_date->setTime(0, 0, 0);
        
        if ($row['fecha_vencimiento'] == '0000-00-00' || empty($row['fecha_vencimiento']) || $end_date <= $start_date) {
            $end_date = clone $start_date;
            $end_date->modify('+1 day');
        }

        $progress = 0;
        switch ($row['estado']) {
            case 'completada':
                $progress = 100;
                break;
            case 'en_proceso':
                $progress = 50;
                break;
            default:
                $progress = 0;
                break;
        }

        $tasks_data[] = [
            'id' => $row['id'],
            'text' => htmlspecialchars($row['titulo']),
            'start_date' => $start_date->format('d-m-Y'),
            'end_date' => $end_date->format('d-m-Y'),
            'duration' => $start_date->diff($end_date)->days,
            'progress' => $progress / 100,
            'resource' => htmlspecialchars($row['asignados_nombres'] ?: 'Sin Asignar'),
            'estado' => $row['estado'],
            'open' => true
        ];
    }
}

$gantt_data = ['data' => $tasks_data, 'links' => []];

require_once 'includes/header.php';
?>
<!-- DHTMLX Gantt CSS -->
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<style>
    .gantt-container { font-size: 11px; }
    .gantt_grid_scale .gantt_grid_head_cell, .gantt_task_cell { font-size: 11px; }
    .gantt_task_text { font-size: 9px; }
    h2 { font-size: 1.8rem; }
    .gantt_row.odd, .gantt_task_row.odd { background-color: #f9f9f9; }
    .gantt_row.even, .gantt_task_row.even { background-color: #fff; }
    .gantt_task_line.gantt_completed .gantt_task_content { background-color: #198754 !important; border-color: #146c43 !important; }
    .gantt_task_line.gantt_pending .gantt_task_content { background-color: #ffc107 !important; border-color: #d39e00 !important; }
    .gantt_task_line.gantt_in_progress .gantt_task_content { background-color: #0dcaf0 !important; border-color: #0aa3c2 !important; }
    .gantt_task_line.gantt_overdue .gantt_task_content { background-color: #dc3545 !important; border-color: #b02a37 !important; }
    .gantt-legend { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
    .legend-item { display: flex; align-items: center; }
    .legend-color-box { width: 15px; height: 15px; border: 1px solid #ccc; margin-right: 8px; display: inline-block; }
</style>

<?php include 'includes/navbar.php'; ?>
<div class="gantt-container">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2>Gantt de Tareas</h2>
    </div>

    <!-- Formulario de Filtros -->
    <div class="container mb-4">
        <div class="card border-0 shadow-sm mx-auto" style="max-width: 800px;">
            <div class="card-body p-2 d-flex align-items-center justify-content-center">
                <form action="gantt_tareas.php" method="GET" class="w-100 d-flex gap-2">
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
                        <a href="reportes/generar_reporte.php?tipo=gantt&anio=<?php echo $anio_seleccionado; ?>&etiqueta=<?php echo $etiqueta_actual; ?>" target="_blank" class="btn btn-danger btn-sm" title="Descargar PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <?php if(!empty($etiqueta_actual) || $anio_seleccionado != date('Y')): ?>
                            <a href="gantt_tareas.php" class="btn btn-outline-secondary btn-sm d-flex align-items-center" title="Limpiar"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="gantt-legend">
        <div class="legend-item"><span class="legend-color-box" style="background-color: #dc3545;"></span> Vencida</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #198754;"></span> Completada</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #ffc107;"></span> Pendiente</div>
        <div class="legend-item"><span class="legend-color-box" style="background-color: #0dcaf0;"></span> En Proceso</div>
    </div>
    <div id="chart_div" style="width: 100%; height: 80vh;"></div>
</div>
<div id="gantt_data_json" style="display: none;"><?php echo json_encode($gantt_data); ?></div>

<!-- Notify Modal -->
<div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notifyModalLabel">Enviar Notificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Tarea:</strong> <span id="modalTaskTitle"></span></p>
                <p><strong>Asignado a:</strong> <span id="modalTaskAssigned"></span></p>
                <form id="notifyForm">
                    <input type="hidden" id="modalTaskId" name="taskId">
                    <div class="mb-3">
                        <label for="modalMessage" class="form-label">Mensaje:</label>
                        <textarea class="form-control" id="modalMessage" name="message" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="sendNotificationBtn">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- DHTMLX Gantt JS -->
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    gantt.config.date_format = "%d-%m-%Y";
    gantt.config.scales = [
        { unit: "month", step: 1, format: "%F, %Y" },
        { unit: "day", step: 1, format: "%d %M" }
    ];
    gantt.config.autoscroll = true;
    gantt.config.autoscroll_speed = 100;
    gantt.config.columns = [
        {name:"text", label:"Task name", tree:true, width:200, resize:true },
        {name:"start_date", label:"Start time", align: "center", resize:true },
        {name:"duration", label:"Duration", align: "center", resize:true },
        {name:"add", label:"", width:44 }
    ];
    gantt.templates.grid_row_class = function(start, end, task) {
        return (task.$index % 2 === 0) ? "even" : "odd";
    };
    gantt.templates.task_row_class = function(start, end, task) {
        return (task.$index % 2 === 0) ? "even" : "odd";
    };
    gantt.templates.task_class = function(start, end, task) {
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        if (end < today && task.estado !== 'completada') {
            return "gantt_overdue";
        }
        switch (task.estado) {
            case 'completada': return "gantt_completed";
            case 'en_proceso': return "gantt_in_progress";
            case 'pendiente': return "gantt_pending";
            default: return "";
        }
    };

    gantt.init("chart_div");
    
    var tasks = JSON.parse(document.getElementById("gantt_data_json").textContent);
    gantt.parse(tasks);

    // --- Modal Logic ---
    // Verificar si Bootstrap está disponible
    if (typeof bootstrap !== 'undefined') {
        var notifyModalElement = document.getElementById('notifyModal');
        if (notifyModalElement) {
            var notifyModal = new bootstrap.Modal(notifyModalElement);
            gantt.attachEvent("onTaskClick", function(id, e) {
                if (e.target.closest('.gantt_add') || e.target.closest('.gantt_tree_icon')) {
                    return true;
                }
                var task = gantt.getTask(id);
                document.getElementById('modalTaskTitle').textContent = task.text;
                document.getElementById('modalTaskAssigned').textContent = task.resource;
                document.getElementById('modalTaskId').value = task.id;
                document.getElementById('modalMessage').value = '';
                notifyModal.show();
                return false;
            });
        }
    } else {
        console.error("Bootstrap no está cargado. El modal no funcionará.");
    }

    var sendBtn = document.getElementById('sendNotificationBtn');
    if(sendBtn) {
        sendBtn.addEventListener('click', function() {
            var form = document.getElementById('notifyForm');
            var formData = new FormData(form);
            var sendButton = this;

            if (formData.get('message').trim() === '') {
                Swal.fire('Error', 'El mensaje no puede estar vacío.', 'error');
                return;
            }

            sendButton.disabled = true;
            sendButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';

            fetch('enviar_notificacion_gantt.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if(typeof notifyModal !== 'undefined') notifyModal.hide();
                    Swal.fire('Éxito', data.message, 'success');
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Ocurrió un error de red.', 'error');
            })
            .finally(() => {
                sendButton.disabled = false;
                sendButton.innerHTML = 'Enviar';
            });
        });
    }
});
</script>

<?php
$mysqli->close();
include 'includes/footer.php';
ob_end_flush();
?>
