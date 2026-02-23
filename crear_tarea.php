<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";
require_once "includes/email_sender.php";

// Proteger la página
if(!has_permission($mysqli, 'crear_tareas')){
    // Opcional: Redirigir a una página de "acceso denegado"
    header("location: dashboard.php?error=No tienes permiso para crear tareas");
    exit;
}

$page_title = 'Crear Tarea';

// --- Lógica para pre-rellenar desde plantilla ---
$plantilla_titulo = "";
$plantilla_descripcion = "";
$plantilla_prioridad = "media"; // Prioridad por defecto si no hay plantilla

if (isset($_GET['plantilla_id']) && ctype_digit($_GET['plantilla_id'])) {
    $id_plantilla = $_GET['plantilla_id'];
    $sql_plantilla = "SELECT titulo, descripcion, prioridad_default FROM tarea_plantillas WHERE id = ?";
    if ($stmt_plantilla = $mysqli->prepare($sql_plantilla)) {
        $stmt_plantilla->bind_param("i", $id_plantilla);
        if ($stmt_plantilla->execute()) {
            $result_plantilla = $stmt_plantilla->get_result();
            if ($result_plantilla->num_rows == 1) {
                $plantilla = $result_plantilla->fetch_assoc();
                $plantilla_titulo = $plantilla['titulo'];
                $plantilla_descripcion = $plantilla['descripcion'];
                $plantilla_prioridad = $plantilla['prioridad_default'];
            }
        }
        $stmt_plantilla->close();
    }
} // --- Fin de la lógica de plantilla ---

// Obtener lista de usuarios para el dropdown
$sql_users = "SELECT id, nombre FROM usuarios ORDER BY nombre";
$result_users = $mysqli->query($sql_users);

// Obtener lista de etiquetas para el dropdown
$sql_etiquetas = "SELECT id, nombre FROM etiquetas ORDER BY nombre";
$result_etiquetas = $mysqli->query($sql_etiquetas);

// Obtener lista de plantillas para el dropdown
$sql_plantillas = "SELECT id, titulo FROM tarea_plantillas ORDER BY titulo";
$result_plantillas = $mysqli->query($sql_plantillas);

$message = "";
// Procesar el formulario de creación de tarea
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $prioridad = trim($_POST["prioridad"]);
    $id_asignados = $_POST["id_asignado"]; // Ahora es un array
    $fecha_creacion = empty(trim($_POST["fecha_creacion"])) ? null : trim($_POST["fecha_creacion"]);
    $fecha_vencimiento = empty(trim($_POST["fecha_vencimiento"])) ? null : trim($_POST["fecha_vencimiento"]);
    $id_creador = $_SESSION["id"];

    // Base de la consulta
    $sql_parts = ["titulo", "descripcion", "prioridad", "id_creador"];
    $sql_values = ["?", "?", "?", "?"];
    $bind_types = "sssi";
    $bind_params = [&$titulo, &$descripcion, &$prioridad, &$id_creador];

    // Añadir fecha_creacion solo si se proporcionó una
    if (!empty($fecha_creacion)) {
        $sql_parts[] = "fecha_creacion";
        $sql_values[] = "?";
        $bind_types .= "s";
        $bind_params[] = &$fecha_creacion;
    }

    // Añadir fecha_vencimiento solo si se proporcionó una
    if (!empty($fecha_vencimiento)) {
        $sql_parts[] = "fecha_vencimiento";
        $sql_values[] = "?";
        $bind_types .= "s";
        $bind_params[] = &$fecha_vencimiento;
    }

    $sql = "INSERT INTO tareas (" . implode(", ", $sql_parts) . ") VALUES (" . implode(", ", $sql_values) . ")";

    if ($stmt = $mysqli->prepare($sql)) {
        // Usar call_user_func_array para bindear los parámetros dinámicamente
        call_user_func_array([$stmt, 'bind_param'], array_merge([$bind_types], $bind_params));

        if ($stmt->execute()) {
            $id_tarea_creada = $mysqli->insert_id; // Obtener el ID de la tarea recién creada

            // --- Lógica para insertar asignaciones en tarea_asignaciones ---
            if (!empty($id_asignados)) {
                $sql_insert_asignacion = "INSERT INTO tarea_asignaciones (id_tarea, id_usuario) VALUES (?, ?)";
                if ($stmt_asignacion = $mysqli->prepare($sql_insert_asignacion)) {
                    foreach ($id_asignados as $id_usuario_asignado) {
                        $stmt_asignacion->bind_param("ii", $id_tarea_creada, $id_usuario_asignado);
                        $stmt_asignacion->execute();

                        // --- Lógica para enviar notificación por correo electrónico a cada asignado ---
                        $assigned_user_email = '';
                        $assigned_user_name = '';
                        $sql_get_user_info = "SELECT nombre, email FROM usuarios WHERE id = ?";
                        if ($stmt_user_info = $mysqli->prepare($sql_get_user_info)) {
                            $stmt_user_info->bind_param("i", $id_usuario_asignado);
                            if ($stmt_user_info->execute()) {
                                $result_user_info = $stmt_user_info->get_result();
                                if ($result_user_info->num_rows == 1) {
                                    $user_info = $result_user_info->fetch_assoc();
                                    $assigned_user_email = $user_info['email'];
                                    $assigned_user_name = $user_info['nombre'];
                                }
                            }
                            $stmt_user_info->close();
                        }

                        if (!empty($assigned_user_email)) {
                            error_log("DEBUG: Fecha de vencimiento en crear_tarea.php: " . $fecha_vencimiento);
                            sendTaskAssignmentEmail(
                                $assigned_user_email,
                                $assigned_user_name,
                                $titulo, // Task title
                                $descripcion, // Task description
                                $id_tarea_creada, // Task ID
                                $fecha_vencimiento // Due Date
                            );
                        }
                    }
                    $stmt_asignacion->close();
                } else {
                    error_log("Error al preparar la inserción de asignación: " . $mysqli->error);
                }
            }
            // --- Fin Lógica para insertar asignaciones ---

            // --- Lógica para insertar etiquetas en tarea_etiquetas ---
            if (!empty($_POST['etiquetas'])) {
                $id_etiquetas_seleccionadas = $_POST['etiquetas'];
                $sql_insert_etiqueta = "INSERT INTO tarea_etiquetas (id_tarea, id_etiqueta) VALUES (?, ?)";
                if ($stmt_etiqueta = $mysqli->prepare($sql_insert_etiqueta)) {
                    foreach ($id_etiquetas_seleccionadas as $id_etiqueta) {
                        $id_etiqueta_int = intval($id_etiqueta);
                        $stmt_etiqueta->bind_param("ii", $id_tarea_creada, $id_etiqueta_int);
                        $stmt_etiqueta->execute();
                    }
                    $stmt_etiqueta->close();
                } else {
                    error_log("Error al preparar la inserción de etiqueta: " . $mysqli->error);
                }
            }
            // --- Fin Lógica para insertar etiquetas ---

            // --- Lógica para procesar archivos adjuntos temporales ---
            if (isset($_SESSION['temp_adjuntos']) && !empty($_SESSION['temp_adjuntos'])) {
                $upload_dir = __DIR__ . '/uploads/'; // Directorio permanente
                
                foreach ($_SESSION['temp_adjuntos'] as $adjunto_temp) {
                    $original_name = $adjunto_temp['original_name'];
                    $server_name = $adjunto_temp['server_name'];
                    $temp_path = $adjunto_temp['temp_path'];
                    $type = $adjunto_temp['type'];
                    $size = $adjunto_temp['size'];

                    $new_path = $upload_dir . $server_name;

                    // Mover el archivo de temp a su ubicación permanente
                    if (rename($temp_path, $new_path)) {
                        $sql_adjunto = "INSERT INTO adjuntos (id_tarea, nombre_original, nombre_servidor, ruta_archivo, tipo_mime, tamano, id_usuario_subida) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        if ($stmt_adjunto = $mysqli->prepare($sql_adjunto)) {
                            $stmt_adjunto->bind_param("issssii", $id_tarea_creada, $original_name, $server_name, $new_path, $type, $size, $_SESSION['id']);
                            $stmt_adjunto->execute();
                            $stmt_adjunto->close();
                        } else {
                            error_log("Error al preparar la inserción del adjunto: " . $mysqli->error);
                        }
                    } else {
                         error_log("Error al mover el archivo de temp a permanente: de " . $temp_path . " a " . $new_path);
                    }
                }
            }
            // Limpiar la sesión de adjuntos temporales
            unset($_SESSION['temp_adjuntos']);
        

            header("location: dashboard.php?success=Tarea Creada con Éxito" . (!empty($message) ? "&msg=" . urlencode($message) : ""));
            exit();
        } else {
            $message = "Error al crear la tarea.";
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>
<script src="https://cdn.tiny.cloud/1/jjwu40cfr9t3r2semmriq8uv0lvwbgdkjla0psbt7snt9itx/tinymce/7/tinymce.min.js"></script>
<script>
  tinymce.init({
    selector: '#descripcion',
    plugins: 'code table lists image',
    toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | image'
  });
</script>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Crear Nueva Tarea</h2>
    <p>Complete el formulario para crear una nueva tarea y asignarla a un miembro del equipo.</p>

    <?php if(!empty($message)): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bg-light p-4 rounded mt-4">
        <div class="mb-4">
            <label for="plantilla_selector" class="form-label fw-bold">Cargar desde Plantilla (Opcional)</label>
            <select id="plantilla_selector" class="form-select">
                <option value="" selected>Seleccione una plantilla...</option>
                <option value="0">--- Tarea en Blanco ---</option>
                <?php
                if ($result_plantillas && $result_plantillas->num_rows > 0) {
                    $current_plantilla_id = $_GET['plantilla_id'] ?? null;
                    while($plantilla_item = $result_plantillas->fetch_assoc()) {
                        $selected = ($current_plantilla_id == $plantilla_item['id']) ? 'selected' : '';
                        echo '<option value="' . $plantilla_item['id'] . '" ' . $selected . '>' . htmlspecialchars($plantilla_item['titulo']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>

        <form id="taskForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($plantilla_titulo); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="10"><?php echo htmlspecialchars($plantilla_descripcion); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="prioridad" class="form-label">Prioridad</label>
                    <select name="prioridad" id="prioridad" class="form-select" required>
                        <option value="baja" <?php echo ($plantilla_prioridad == 'baja') ? 'selected' : ''; ?>>Baja</option>
                        <option value="media" <?php echo ($plantilla_prioridad == 'media') ? 'selected' : ''; ?>>Media</option>
                        <option value="alta" <?php echo ($plantilla_prioridad == 'alta') ? 'selected' : ''; ?>>Alta</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="id_asignado" class="form-label">Asignar a</label>
                    <select name="id_asignado[]" id="id_asignado" class="form-select" multiple>
                        <option value="">Seleccione un usuario...</option>
                        <?php
                        if ($result_users && $result_users->num_rows > 0) {
                            // Reset pointer just in case
                            $result_users->data_seek(0);
                            while($user = $result_users->fetch_assoc()) {
                                echo '<option value="' . $user['id'] . '">' . htmlspecialchars($user['nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="etiquetas" class="form-label">Etiquetas (opcional)</label>
                    <select name="etiquetas[]" id="etiquetas" class="form-select" multiple>
                        <?php
                        if ($result_etiquetas && $result_etiquetas->num_rows > 0) {
                            while($etiqueta = $result_etiquetas->fetch_assoc()) {
                                echo '<option value="' . $etiqueta['id'] . '">' . htmlspecialchars($etiqueta['nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_creacion" class="form-label">Fecha de Inicio (opcional)</label>
                    <input type="date" name="fecha_creacion" id="fecha_creacion" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="" placeholder="YYYY-MM-DD">
                </div>
            </div>
            
            <!-- Sección de Adjuntos AJAX -->
            <div class="mb-3">
                <label for="fileInput" class="form-label">Adjuntar Archivos</label>
                <input type="file" id="fileInput" class="form-control" multiple>
                <small class="form-text text-muted">Tipos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX. Máx. 5MB por archivo.</small>
                <div id="fileList" class="mt-2">
                    <!-- Archivos subidos temporalmente se listarán aquí -->
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Tarea</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('plantilla_selector').addEventListener('change', function() {
    var plantillaId = this.value;
    if (plantillaId) {
        if (plantillaId === '0') {
            // Tarea en Blanco
            window.location.href = 'crear_tarea.php';
        } else {
            // Cargar plantilla
            window.location.href = 'crear_tarea.php?plantilla_id=' + plantillaId;
        }
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    const taskForm = document.getElementById('taskForm');

    // Función para mostrar mensajes
    function showMessage(msg, type = 'info') {
        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alertDiv.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        fileList.before(alertDiv); // Insert before fileList
        setTimeout(() => alertDiv.remove(), 5000); // Remove after 5 seconds
    }

    // Manejar la subida de archivos
    fileInput.addEventListener('change', function() {
        if (this.files.length === 0) {
            return;
        }

        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            const formData = new FormData();
            formData.append('file', file);

            // Mostrar indicador de carga
            const listItem = document.createElement('div');
            listItem.className = 'd-flex justify-content-between align-items-center p-2 border rounded mb-1';
            listItem.innerHTML = `<span><i class="bi bi-hourglass-split"></i> Subiendo: ${file.name}</span>`;
            fileList.appendChild(listItem);

            fetch('upload_temp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                listItem.remove(); // Eliminar indicador de carga
                if (data.success) {
                    const uploadedItem = document.createElement('div');
                    uploadedItem.className = 'd-flex justify-content-between align-items-center p-2 border rounded mb-1 bg-light';
                    uploadedItem.innerHTML = `
                        <span><i class="bi bi-check-circle-fill text-success"></i> ${data.file.original_name}</span>
                        <button type="button" class="btn btn-sm btn-danger remove-file" data-server-name="${data.file.server_name}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    `;
                    fileList.appendChild(uploadedItem);
                    showMessage(`"${data.file.original_name}" subido temporalmente.`, 'success');
                } else {
                    showMessage(`Error al subir "${file.name}": ${data.message}`, 'danger');
                }
            })
            .catch(error => {
                listItem.remove();
                showMessage(`Error de red al subir "${file.name}".`, 'danger');
                console.error('Error:', error);
            });
        }
        this.value = ''; // Limpiar el input para permitir subir el mismo archivo de nuevo
    });

    // Manejar la eliminación de archivos de la lista (y de la sesión/temp)
    fileList.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-file') || event.target.closest('.remove-file')) {
            const button = event.target.closest('.remove-file');
            const serverName = button.dataset.serverName;
            
            // Eliminar visualmente
            button.closest('div').remove();

            // Opcional: Enviar una petición AJAX para eliminar del servidor/sesión
            // Esto es más complejo y no se implementará en este ejemplo para mantenerlo manejable.
            // En un entorno de producción, querrías una forma de limpiar los archivos temporales
            // si el usuario no completa el formulario. (Requiere un nuevo endpoint PHP)
            showMessage(`"${serverName}" eliminado de la lista. (No eliminado del servidor temporalmente)`, 'warning');
        }
    });

    // Limpiar adjuntos temporales de la sesión al cargar la página (si el usuario no completó la tarea anterior)
    // Esto es una medida de limpieza. En un sistema real, se necesitaría un cron job para limpiar archivos antiguos.
    fetch('clear_temp_adjuntos.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                console.log('Adjuntos temporales de sesión limpiados al cargar la página.');
            }
        })
        .catch(error => console.error('Error al limpiar adjuntos temporales:', error));

    taskForm.addEventListener('submit', function(e) {
        // We need to trigger tinymce to save the content to the textarea before the form submits
        tinymce.triggerSave();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
