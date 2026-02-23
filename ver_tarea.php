<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Verificar login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Validar ID de la tarea
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: dashboard.php?error=ID de tarea no válido");
    exit;
}
$id_tarea = trim($_GET["id"]);

// Preparar la consulta para obtener los detalles de la tarea
$sql = "SELECT t.id, t.titulo, t.descripcion, t.prioridad, t.estado, t.fecha_creacion, t.fecha_vencimiento, t.id_asignado, u_creador.nombre AS nombre_creador, u_asignado.nombre AS nombre_asignado FROM tareas t JOIN usuarios u_creador ON t.id_creador = u_creador.id LEFT JOIN usuarios u_asignado ON t.id_asignado = u_asignado.id WHERE t.id = ?";
if($stmt = $mysqli->prepare($sql)){
    $stmt->bind_param("i", $id_tarea);
    if($stmt->execute()){
        $result = $stmt->get_result();
        if($result->num_rows == 1){
            $tarea = $result->fetch_assoc();
        } else{
            header("location: dashboard.php?error=Tarea no encontrada");
            exit;
        }
    } else{
        die("Error al ejecutar la consulta de tarea.");
    }
    $stmt->close();
} else {
    die("Error al preparar la consulta de tarea.");
}

// Determinar si el usuario puede editar el estado
$can_edit_status = has_permission($mysqli, 'editar_tareas_todas') || (has_permission($mysqli, 'editar_tareas_propias') && isset($tarea['id_asignado']) && $tarea['id_asignado'] == $_SESSION['id']);

// --- Lógica de POST para actualizar y comentar ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lógica para actualizar el estado de la tarea
    if (isset($_POST['update_status']) && isset($_POST['estado'])) {
        $nuevo_estado = trim($_POST['estado']);
        if ($can_edit_status && in_array($nuevo_estado, ['pendiente', 'en_proceso', 'completada', 'cancelada'])) {
            $sql_update = "UPDATE tareas SET estado = ? WHERE id = ?";
            if ($stmt_update = $mysqli->prepare($sql_update)) {
                $stmt_update->bind_param("si", $nuevo_estado, $id_tarea);
                if ($stmt_update->execute()) {
                    header("location: ver_tarea.php?id=" . $id_tarea . "&success=Estado de la tarea actualizado correctamente.");
                    exit();
                } else {
                    echo "Error al actualizar el estado: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                echo "Error al preparar la consulta de actualización de estado: " . $mysqli->error;
            }
        } else {
            header("location: ver_tarea.php?id=" . $id_tarea . "&error=Permisos insuficientes o estado no válido.");
            exit();
        }
    }

    // Lógica para añadir un comentario
    if (isset($_POST['add_comment']) && isset($_POST['comentario'])) {
        $comentario_texto = trim($_POST['comentario']);
        $id_usuario = $_SESSION['id']; // Asume que el ID del usuario está en la sesión

        if (!empty($comentario_texto)) {
            $sql_comment = "INSERT INTO comentarios (id_tarea, id_usuario, comentario) VALUES (?, ?, ?)";
            if ($stmt_comment = $mysqli->prepare($sql_comment)) {
                $stmt_comment->bind_param("iis", $id_tarea, $id_usuario, $comentario_texto);
                if ($stmt_comment->execute()) {
                    header("location: ver_tarea.php?id=" . $id_tarea . "&success=Comentario añadido correctamente.");
                    exit();
                } else {
                    echo "Error al añadir comentario: " . $stmt_comment->error;
                }
                $stmt_comment->close();
            } else {
                echo "Error al preparar la consulta de comentario: " . $mysqli->error;
            }
        } else {
            header("location: ver_tarea.php?id=" . $id_tarea . "&error=El comentario no puede estar vacío.");
            exit();
        }
    }
}

// Lógica para obtener asignados (nuevo sistema)
$lista_nombres_asignados = [];
$sql_asignados_nuevo = "SELECT u.nombre FROM usuarios u JOIN tarea_asignaciones ta ON u.id = ta.id_usuario WHERE ta.id_tarea = ?";
if ($stmt_asignados = $mysqli->prepare($sql_asignados_nuevo)) {
    $stmt_asignados->bind_param("i", $id_tarea);
    if ($stmt_asignados->execute()) {
        $resultado_asignados = $stmt_asignados->get_result();
        while ($fila_asignado = $resultado_asignados->fetch_assoc()) {
            $lista_nombres_asignados[] = $fila_asignado['nombre'];
        }
    }
    $stmt_asignados->close();
}

// Lógica para obtener etiquetas
$etiquetas_tarea = [];
$sql_etiquetas = "SELECT e.nombre FROM etiquetas e JOIN tarea_etiquetas te ON e.id = te.id_etiqueta WHERE te.id_tarea = ?";
if ($stmt_etiquetas = $mysqli->prepare($sql_etiquetas)) {
    $stmt_etiquetas->bind_param("i", $id_tarea);
    if ($stmt_etiquetas->execute()) {
        $resultado_etiquetas = $stmt_etiquetas->get_result();
        while ($fila_etiqueta = $resultado_etiquetas->fetch_assoc()) {
            $etiquetas_tarea[] = $fila_etiqueta['nombre'];
        }
    }
    $stmt_etiquetas->close();
}

// Lógica para obtener adjuntos
$adjuntos = [];
$sql_adjuntos = "SELECT nombre_original, nombre_servidor, tipo_mime FROM adjuntos WHERE id_tarea = ?";
if ($stmt_adjuntos = $mysqli->prepare($sql_adjuntos)) {
    $stmt_adjuntos->bind_param("i", $id_tarea);
    if ($stmt_adjuntos->execute()) {
        $result_adjuntos = $stmt_adjuntos->get_result();
        while ($row = $result_adjuntos->fetch_assoc()) {
            $adjuntos[] = $row;
        }
    } else {
        echo "Error al obtener adjuntos: " . $stmt_adjuntos->error;
    }
    $stmt_adjuntos->close();
} else {
    echo "Error al preparar la consulta de adjuntos: " . $mysqli->error;
}

// Lógica para obtener comentarios
$comentarios = [];
$sql_comentarios = "SELECT c.comentario, c.fecha_creacion, u.nombre AS nombre_usuario FROM comentarios c JOIN usuarios u ON c.id_usuario = u.id WHERE c.id_tarea = ? ORDER BY c.fecha_creacion ASC";
if ($stmt_comentarios = $mysqli->prepare($sql_comentarios)) {
    $stmt_comentarios->bind_param("i", $id_tarea);
    if ($stmt_comentarios->execute()) {
        $result_comentarios = $stmt_comentarios->get_result();
        while ($row = $result_comentarios->fetch_assoc()) {
            $comentarios[] = $row;
        }
    } else {
        echo "Error al obtener comentarios: " . $stmt_comentarios->error;
    }
    $stmt_comentarios->close();
} else {
    echo "Error al preparar la consulta de comentarios: " . $mysqli->error;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Tarea: <?php echo htmlspecialchars($tarea['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detalle de la Tarea</h2>
        <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '<?php echo htmlspecialchars($_GET['success']); ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
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

    <div class="row">
        <!-- Columna de Detalles y Comentarios -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($tarea['titulo']); ?></h3>
                </div>
                <div class="card-body">
                    <div class="card-text"><?php echo $tarea['descripcion']; ?></div>
                </div>
            </div>
            
            <!-- Sección de Adjuntos -->
            <div class="card mb-4">
                <div class="card-header"><h4>Archivos Adjuntos</h4></div>
                <div class="card-body">
                    <?php if(!empty($adjuntos)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($adjuntos as $adjunto): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php
                                    $file_url = 'uploads/' . htmlspecialchars($adjunto['nombre_servidor']);
                                    $file_name = htmlspecialchars($adjunto['nombre_original'] ?? '');
                                    $file_type = htmlspecialchars($adjunto['tipo_mime'] ?? '');
                                    $icon_class = 'bi-file-earmark'; // Default icon

                                    // Determine icon based on file type
                                    if (str_starts_with($file_type, 'image/')) {
                                        $icon_class = 'bi-image';
                                    } elseif ($file_type === 'application/pdf') {
                                        $icon_class = 'bi-file-earmark-pdf';
                                    } elseif (str_starts_with($file_type, 'text/')) {
                                        $icon_class = 'bi-file-earmark-text';
                                    } elseif (str_contains($file_type, 'wordprocessingml')) { // .docx
                                        $icon_class = 'bi-file-earmark-word';
                                    } elseif (str_contains($file_type, 'spreadsheetml')) { // .xlsx
                                        $icon_class = 'bi-file-earmark-excel';
                                    } elseif (str_contains($file_type, 'presentationml')) { // .pptx
                                        $icon_class = 'bi-file-earmark-ppt';
                                    }

                                    if (str_starts_with($file_type, 'image/')) {
                                        // Image: Show thumbnail, click opens modal
                                        echo '<a href="#" class="attachment-preview-link" data-bs-toggle="modal" data-bs-target="#attachmentViewerModal" data-file-url="' . $file_url . '" data-file-name="' . $file_name . '" data-file-type="' . $file_type . '">';
                                        echo '<img src="' . $file_url . '" alt="' . $file_name . '" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px; border-radius: 5px;">';
                                        echo $file_name;
                                        echo '</a>';
                                    } elseif ($file_type === 'application/pdf') {
                                        // PDF: Show icon, click opens in new tab
                                        echo '<a href="' . $file_url . '" target="_blank" title="Ver PDF">';
                                        echo '<i class="bi ' . $icon_class . '"></i> ' . $file_name;
                                        echo '</a>';
                                    } else {
                                        // Other files: Show icon, click downloads
                                        echo '<a href="' . $file_url . '" download="' . $file_name . '" title="Descargar">';
                                        echo '<i class="bi ' . $icon_class . '"></i> ' . $file_name;
                                        echo '</a>';
                                    }
                                    ?>
                                    <a href="<?php echo $file_url; ?>" download="<?php echo $file_name; ?>" class="btn btn-sm btn-outline-secondary" title="Descargar"><i class="bi bi-download"></i></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay archivos adjuntos para esta tarea.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sección de comentarios -->
            <div class="card">
                <div class="card-header"><h4>Comentarios</h4></div>
                <div class="card-body">
                    <!-- Formulario para añadir comentario -->
                    <?php if (has_permission($mysqli, 'comentar_tareas')): ?>
                    <form action="ver_tarea.php?id=<?php echo $id_tarea; ?>" method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Añadir un comentario</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="add_comment" class="btn btn-primary">Añadir Comentario</button>
                    </form>
                    <?php endif; ?>
                    <hr>
                    <!-- Lista de comentarios -->
                    <?php if(!empty($comentarios)): ?>
                        <?php foreach($comentarios as $comentario): ?>
                            <div class="mb-3">
                                <strong><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></strong> <small class="text-muted">dijo el <?php echo date("d/m/Y H:i", strtotime($comentario['fecha_creacion'])); ?>:</small>
                                <p class="p-2 bg-light rounded"><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay comentarios en esta tarea todavía.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Columna de Información y Acciones -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Información</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Estado:</strong> <span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($tarea['estado']); ?></span></li>
                        <li class="list-group-item"><strong>Prioridad:</strong> 
                            <?php
                            $prioridad_texto = htmlspecialchars($tarea['prioridad']);
                            $estado_tarea = $tarea['estado']; // Obtener el estado de la tarea

                            $icon_class = '';
                            $color_class = '';
                            $animation_class = '';

                            if ($estado_tarea == 'completada') {
                                $icon_class = 'fas fa-check-circle';
                                $color_class = 'text-success'; // O un gris si prefieres
                                $animation_class = ''; // Sin animación
                            } else {
                                switch ($prioridad_texto) {
                                    case 'baja':
                                        $icon_class = 'fas fa-arrow-down';
                                        $color_class = 'text-success';
                                        $animation_class = ''; // Sin animación
                                        break;
                                    case 'media':
                                        $icon_class = 'fas fa-minus';
                                        $color_class = 'text-info'; // Azul
                                        $animation_class = 'fa-beat-fade'; // Animación de pulso/desvanecimiento
                                        break;
                                    case 'alta':
                                        $icon_class = 'fas fa-exclamation-triangle';
                                        $color_class = 'text-danger'; // Rojo
                                        $animation_class = 'fa-shake'; // Animación de vibración
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
                        </li>
                        <li class="list-group-item"><strong>Etiquetas:</strong>
                            <?php if(!empty($etiquetas_tarea)): ?>
                                <?php foreach($etiquetas_tarea as $nombre_etiqueta): ?>
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($nombre_etiqueta); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">No hay etiquetas</span>
                            <?php endif; ?>
                        </li>
                        <?php
                        $display_asignados = !empty($lista_nombres_asignados) ? htmlspecialchars(implode(', ', $lista_nombres_asignados)) : (!empty($tarea['nombre_asignado']) ? htmlspecialchars($tarea['nombre_asignado']) : 'No asignado');
                        echo '<li class="list-group-item"><strong>Asignado a:</strong> ' . $display_asignados . '</li>';
                        ?>
                        <li class="list-group-item"><strong>Creado por:</strong> <?php echo htmlspecialchars($tarea['nombre_creador']); ?></li>
                        <li class="list-group-item"><strong>Fecha Creación:</strong> <?php echo date("d/m/Y H:i", strtotime($tarea['fecha_creacion'])); ?></li>
                        <li class="list-group-item"><strong>Fecha Vencimiento:</strong> <?php echo (!empty($tarea['fecha_vencimiento']) && $tarea['fecha_vencimiento'] !== '0000-00-00') ? date("d/m/Y", strtotime($tarea['fecha_vencimiento'])) : 'N/A'; ?></li>
                    </ul>
                </div>
            </div>
            
            <?php
// Calcular días restantes si la fecha de vencimiento no es nula o '0000-00-00'
$dias_restantes_html = '';
if (!empty($tarea['fecha_vencimiento']) && $tarea['fecha_vencimiento'] !== '0000-00-00') {
    $fecha_vencimiento_dt = new DateTime($tarea['fecha_vencimiento']);
    $fecha_actual_dt = new DateTime();
    $interval = $fecha_actual_dt->diff($fecha_vencimiento_dt);
    $dias_restantes = $interval->days;
    $prefijo = $interval->invert ? 'Hace ' : 'Quedan '; // 'invert' es true si la fecha de vencimiento es pasada

    $clase_alerta = 'alert-info';
    if ($interval->invert) {
        $clase_alerta = 'alert-danger'; // Vencida
    } elseif ($dias_restantes <= 2) { // CAMBIO: Ahora es <= 2 para rojo
        $clase_alerta = 'alert-danger'; // Pocos días (2 o menos)
    } elseif ($dias_restantes == 3) { // CAMBIO: Específicamente 3 días para amarillo
        $clase_alerta = 'alert-warning';
    } else {
        $clase_alerta = 'alert-success'; // Suficientes días (más de 3)
    }

    $dias_restantes_html = "<div class=\"alert {$clase_alerta} mt-3\">";
    $dias_restantes_html .= "<strong>{$prefijo} {$dias_restantes} día" . ($dias_restantes == 1 ? '' : 's') . "</strong> para completar este proyecto.";
    $dias_restantes_html .= "</div>";
}
echo $dias_restantes_html;
?>

            <!-- Formulario de actualización de estado -->
            <?php if ($can_edit_status): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Actualizar Estado</h5>
                    <form action="ver_tarea.php?id=<?php echo $id_tarea; ?>" method="post">
                        <div class="mb-3">
                            <label for="estado" class="form-label">Nuevo Estado</label>
                            <select class="form-select" name="estado" id="estado">
                                <option value="pendiente" <?php echo ($tarea['estado'] == 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="en_proceso" <?php echo ($tarea['estado'] == 'en_proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="completada" <?php echo ($tarea['estado'] == 'completada') ? 'selected' : ''; ?>>Completada</option>
                                <option value="cancelada" <?php echo ($tarea['estado'] == 'cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-success">Actualizar</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Attachment Viewer Modal -->
<div class="modal fade" id="attachmentViewerModal" tabindex="-1" aria-labelledby="attachmentViewerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="attachmentViewerModalLabel">Previsualizar Adjunto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Content will be loaded here by JavaScript -->
        <div id="modalContent" class="text-center">Cargando...</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <a id="downloadAttachmentBtn" href="#" class="btn btn-primary" download>Descargar</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const attachmentViewerModal = document.getElementById('attachmentViewerModal');
    const modalTitle = document.getElementById('attachmentViewerModalLabel');
    const modalContent = document.getElementById('modalContent');
    const downloadAttachmentBtn = document.getElementById('downloadAttachmentBtn');

    attachmentViewerModal.addEventListener('show.bs.modal', function (event) {
        // Button that triggered the modal
        const button = event.relatedTarget;
        // Extract info from data-bs-* attributes
        const fileURL = button.getAttribute('data-file-url');
        const fileName = button.getAttribute('data-file-name');
        const fileType = button.getAttribute('data-file-type');

        // Update the modal's content.
        modalTitle.textContent = fileName;
        downloadAttachmentBtn.href = fileURL;
        downloadAttachmentBtn.download = fileName;

        // Clear previous content
        modalContent.innerHTML = 'Cargando...';

        if (fileType.startsWith('image/')) {
            modalContent.innerHTML = `<img src="${fileURL}" class="img-fluid" alt="${fileName}">`;
        } else {
            // This case should ideally not be reached if PHP logic is correct,
            // as only images should trigger the modal.
            modalContent.innerHTML = `<p class="text-muted">No se puede previsualizar este tipo de archivo.</p>`;
        }
    });

    // Simple htmlspecialchars equivalent for JavaScript
    function htmlspecialchars(str) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
<?php include 'includes/footer.php'; ?>
</body>
</html>