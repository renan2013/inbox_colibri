<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";
require_once "includes/email_sender.php";

// Proteger la página
if(!has_permission($mysqli, 'editar_tareas_todas')){
    header("location: dashboard.php?error=No tienes permiso para editar esta tarea");
    exit;
}

// Validar ID de la tarea
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: dashboard.php?error=ID de tarea no válido");
    exit;
}
$id_tarea = trim($_GET["id"]);

// Obtener datos de la tarea existente
$sql_tarea_existente = "SELECT titulo, descripcion, prioridad, id_asignado, fecha_creacion, fecha_vencimiento FROM tareas WHERE id = ?";
if($stmt_tarea_existente = $mysqli->prepare($sql_tarea_existente)){
    $stmt_tarea_existente->bind_param("i", $id_tarea);
    $stmt_tarea_existente->execute();
    $result_tarea_existente = $stmt_tarea_existente->get_result();
    if($result_tarea_existente->num_rows == 1){
        $tarea_existente = $result_tarea_existente->fetch_assoc();
        $titulo = $tarea_existente['titulo'];
        $descripcion = $tarea_existente['descripcion'];
        $prioridad = $tarea_existente['prioridad'];
        $id_asignado_existente = $tarea_existente['id_asignado'];
        $fecha_creacion = $tarea_existente['fecha_creacion'];
        $fecha_vencimiento = $tarea_existente['fecha_vencimiento'];
} else {
    header("location: dashboard.php?error=Tarea no encontrada");
    exit;
}
$stmt_tarea_existente->close();
} else {
    die("Error al preparar la consulta de tarea existente.");
}

// Obtener usuarios asignados a esta tarea
$assigned_users_ids = [];
$sql_assigned_users = "SELECT id_usuario FROM tarea_asignaciones WHERE id_tarea = ?";
if ($stmt_assigned_users = $mysqli->prepare($sql_assigned_users)) {
    $stmt_assigned_users->bind_param("i", $id_tarea);
    $stmt_assigned_users->execute();
    $result_assigned_users = $stmt_assigned_users->get_result();
    while ($row = $result_assigned_users->fetch_assoc()) {
        $assigned_users_ids[] = $row['id_usuario'];
    }
    $stmt_assigned_users->close();
}

// Obtener lista de usuarios para el dropdown
$sql_users = "SELECT id, nombre FROM usuarios ORDER BY nombre";
$result_users = $mysqli->query($sql_users);

// Obtener lista de todas las etiquetas para el dropdown
$sql_etiquetas = "SELECT id, nombre FROM etiquetas ORDER BY nombre";
$result_etiquetas = $mysqli->query($sql_etiquetas);

// Obtener etiquetas asignadas a esta tarea
$assigned_tags_ids = [];
$sql_assigned_tags = "SELECT id_etiqueta FROM tarea_etiquetas WHERE id_tarea = ?";
if ($stmt_assigned_tags = $mysqli->prepare($sql_assigned_tags)) {
    $stmt_assigned_tags->bind_param("i", $id_tarea);
    $stmt_assigned_tags->execute();
    $result_assigned_tags = $stmt_assigned_tags->get_result();
    while ($row = $result_assigned_tags->fetch_assoc()) {
        $assigned_tags_ids[] = $row['id_etiqueta'];
    }
    $stmt_assigned_tags->close();
}

// Obtener adjuntos existentes para mostrar
$sql_adjuntos_existentes = "SELECT id, nombre_original, nombre_servidor FROM adjuntos WHERE id_tarea = ? ORDER BY fecha_subida DESC";
$adjuntos_existentes = [];
if($stmt_adjuntos_existentes = $mysqli->prepare($sql_adjuntos_existentes)){
    $stmt_adjuntos_existentes->bind_param("i", $id_tarea);
    $stmt_adjuntos_existentes->execute();
    $result_adjuntos_existentes = $stmt_adjuntos_existentes->get_result();
    while($row = $result_adjuntos_existentes->fetch_assoc()){
        $adjuntos_existentes[] = $row;
    }
    $stmt_adjuntos_existentes->close();
}


$message = "";
// Procesar el formulario de actualización de tarea
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $prioridad = trim($_POST["prioridad"]);
    $id_asignados = $_POST["id_asignado"]; // Ahora es un array
    $fecha_creacion = empty(trim($_POST["fecha_creacion"])) ? null : trim($_POST["fecha_creacion"]);
    $fecha_vencimiento = empty(trim($_POST["fecha_vencimiento"])) ? null : trim($_POST["fecha_vencimiento"]);
    
    $sql_update_parts = [];
    $bind_types = "";
    $bind_params = [];

    $sql_update_parts[] = "titulo = ?";
    $bind_types .= "s";
    $bind_params[] = &$titulo;

    $sql_update_parts[] = "descripcion = ?";
    $bind_types .= "s";
    $bind_params[] = &$descripcion;

    $sql_update_parts[] = "prioridad = ?";
    $bind_types .= "s";
    $bind_params[] = &$prioridad;

    if (!empty($fecha_creacion)) {
        $sql_update_parts[] = "fecha_creacion = ?";
        $bind_types .= "s";
        $bind_params[] = &$fecha_creacion;
    } else {
        $sql_update_parts[] = "fecha_creacion = NULL";
    }

    if (!empty($fecha_vencimiento)) {
        $sql_update_parts[] = "fecha_vencimiento = ?";
        $bind_types .= "s";
        $bind_params[] = &$fecha_vencimiento;
    } else {
        $sql_update_parts[] = "fecha_vencimiento = NULL";
    }

    $sql_update = "UPDATE tareas SET " . implode(", ", $sql_update_parts) . " WHERE id = ?";
    $bind_types .= "i";
    $bind_params[] = &$id_tarea;

    if ($stmt_update = $mysqli->prepare($sql_update)) {
        // Dynamically bind parameters
        call_user_func_array([$stmt_update, 'bind_param'], array_merge([$bind_types], $bind_params));

        if ($stmt_update->execute()) {
            // --- Lógica para gestionar asignaciones en tarea_asignaciones ---
            // 1. Eliminar asignaciones antiguas
            $sql_delete_asignaciones = "DELETE FROM tarea_asignaciones WHERE id_tarea = ?";
            if ($stmt_delete = $mysqli->prepare($sql_delete_asignaciones)) {
                $stmt_delete->bind_param("i", $id_tarea);
                $stmt_delete->execute();
                $stmt_delete->close();
            } else {
                error_log("Error al preparar la eliminación de asignaciones: " . $mysqli->error);
            }

            // 2. Insertar nuevas asignaciones
            if (!empty($id_asignados)) {
                $sql_insert_asignacion = "INSERT INTO tarea_asignaciones (id_tarea, id_usuario) VALUES (?, ?)";
                if ($stmt_insert_asignacion = $mysqli->prepare($sql_insert_asignacion)) {
                    foreach ($id_asignados as $id_usuario_asignado) {
                        $stmt_insert_asignacion->bind_param("ii", $id_tarea, $id_usuario_asignado);
                        $stmt_insert_asignacion->execute();

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
                            sendTaskAssignmentEmail(
                                $assigned_user_email,
                                $assigned_user_name,
                                $titulo, // Task title
                                $descripcion, // Task description
                                $id_tarea, // Task ID
                                $fecha_vencimiento // Due Date
                            );
                        }
                    }
                    $stmt_insert_asignacion->close();
                } else {
                    error_log("Error al preparar la inserción de asignación: " . $mysqli->error);
                }
            }
            // --- Fin Lógica para gestionar asignaciones ---

            // --- Lógica para gestionar etiquetas en tarea_etiquetas ---
            // 1. Eliminar etiquetas antiguas
            $sql_delete_etiquetas = "DELETE FROM tarea_etiquetas WHERE id_tarea = ?";
            if ($stmt_delete_etiquetas = $mysqli->prepare($sql_delete_etiquetas)) {
                $stmt_delete_etiquetas->bind_param("i", $id_tarea);
                $stmt_delete_etiquetas->execute();
                $stmt_delete_etiquetas->close();
            } else {
                error_log("Error al preparar la eliminación de etiquetas: " . $mysqli->error);
            }

            // 2. Insertar nuevas etiquetas
            if (!empty($_POST['etiquetas'])) {
                $id_etiquetas_seleccionadas = $_POST['etiquetas'];
                $sql_insert_etiqueta = "INSERT INTO tarea_etiquetas (id_tarea, id_etiqueta) VALUES (?, ?)";
                if ($stmt_insert_etiqueta = $mysqli->prepare($sql_insert_etiqueta)) {
                    foreach ($id_etiquetas_seleccionadas as $id_etiqueta) {
                        $id_etiqueta_int = intval($id_etiqueta);
                        $stmt_insert_etiqueta->bind_param("ii", $id_tarea, $id_etiqueta_int);
                        $stmt_insert_etiqueta->execute();
                    }
                    $stmt_insert_etiqueta->close();
                } else {
                    error_log("Error al preparar la inserción de nuevas etiquetas: " . $mysqli->error);
                }
            }
            // --- Fin Lógica para gestionar etiquetas ---

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

                    if (rename($temp_path, $new_path)) {
                        $sql_adjunto = "INSERT INTO adjuntos (id_tarea, nombre_original, nombre_servidor, ruta_archivo, tipo_mime, tamano, id_usuario_subida) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        if ($stmt_adjunto = $mysqli->prepare($sql_adjunto)) {
                            $stmt_adjunto->bind_param("issssii", $id_tarea, $original_name, $server_name, $new_path, $type, $size, $_SESSION['id']);
                            $stmt_adjunto->execute();
                            $stmt_adjunto->close();
                        } else {
                            error_log("Error al preparar la inserción del adjunto: " . $mysqli->error);
                        }
                    } else {
                        error_log("Error al mover el archivo de temp a permanente: " . $temp_path);
                    }
                }
                unset($_SESSION['temp_adjuntos']);
            }
            // --- Fin Lógica para procesar archivos adjuntos temporales ---

            header("location: dashboard.php?success=Tarea Actualizada con Éxito" . (!empty($message) ? "&msg=" . urlencode($message) : ""));
            exit();
        } else {
            $message = "Error al actualizar la tarea: " . $stmt_update->error;
        }
    } else {
        $message = "Error al preparar la consulta de actualización: " . $mysqli->error;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarea - BPM Unela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.tiny.cloud/1/jjwu40cfr9t3r2semmriq8uv0lvwbgdkjla0psbt7snt9itx/tinymce/7/tinymce.min.js"></script>
    <script>
      tinymce.init({
        selector: '#descripcion',
        plugins: 'code table lists image',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | image'
      });
    </script>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Editar Tarea</h2>
    <p>Modifique los detalles de la tarea.</p>

    <?php if(!empty($message)): ?>
        <script>
            Swal.fire({
                icon: 'error', // Assuming $message is for errors
                title: 'Error',
                text: '<?php echo htmlspecialchars($message); ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    <?php endif; ?>

    <div class="bg-light p-4 rounded mt-4">
        <form id="taskForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id_tarea; ?>" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" rows="10"><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="prioridad" class="form-label">Prioridad</label>
                    <select name="prioridad" id="prioridad" class="form-select" required>
                        <option value="baja" <?php echo ($prioridad == 'baja') ? 'selected' : ''; ?>>Baja</option>
                        <option value="media" <?php echo ($prioridad == 'media') ? 'selected' : ''; ?>>Media</option>
                        <option value="alta" <?php echo ($prioridad == 'alta') ? 'selected' : ''; ?>>Alta</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="id_asignado" class="form-label">Asignar a</label>
                    <select name="id_asignado[]" id="id_asignado" class="form-select" multiple>
                        <option value="">Seleccione un usuario...</option>
                        <?php
                        if ($result_users->num_rows > 0) {
                            $result_users->data_seek(0);
                            while($user = $result_users->fetch_assoc()) {
                                $selected = in_array($user['id'], $assigned_users_ids) ? 'selected' : '';
                                echo '<option value="' . $user['id'] . '" ' . $selected . '>' . htmlspecialchars($user['nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="etiquetas" class="form-label">Etiquetas</label>
                    <select name="etiquetas[]" id="etiquetas" class="form-select" multiple>
                        <?php
                        if ($result_etiquetas && $result_etiquetas->num_rows > 0) {
                            $result_etiquetas->data_seek(0);
                            while($etiqueta = $result_etiquetas->fetch_assoc()) {
                                $selected = in_array($etiqueta['id'], $assigned_tags_ids) ? 'selected' : '';
                                echo '<option value="' . $etiqueta['id'] . '" ' . $selected . '>' . htmlspecialchars($etiqueta['nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_creacion" class="form-label">Fecha de Inicio</label>
                    <input type="date" name="fecha_creacion" id="fecha_creacion" class="form-control" value="<?php echo !empty($fecha_creacion) ? date('Y-m-d', strtotime($fecha_creacion)) : ''; ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control" value="<?php echo (!empty($fecha_vencimiento) && $fecha_vencimiento !== '0000-00-00') ? htmlspecialchars($fecha_vencimiento) : ''; ?>">
                </div>
            </div>
            
            <!-- Sección de Adjuntos Existentes -->
            <?php if(!empty($adjuntos_existentes)): ?>
            <div class="mb-3">
                <label class="form-label">Archivos Adjuntos Existentes</label>
                <ul class="list-group">
                    <?php foreach($adjuntos_existentes as $adjunto): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="uploads/<?php echo htmlspecialchars($adjunto['nombre_servidor']); ?>" target="_blank" download="<?php echo htmlspecialchars($adjunto['nombre_original']); ?>">
                                <i class="bi bi-file-earmark"></i> <?php echo htmlspecialchars($adjunto['nombre_original']); ?>
                            </a>
                            <!-- Opcional: Botón para eliminar adjunto -->
                            <!-- <button type="button" class="btn btn-sm btn-danger">Eliminar</button> -->
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Sección de Adjuntos AJAX (para añadir nuevos) -->
            <div class="mb-3">
                <label for="fileInput" class="form-label">Añadir Nuevos Archivos</label>
                <input type="file" id="fileInput" class="form-control" multiple>
                <small class="form-text text-muted">Tipos permitidos: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX. Máx. 5MB por archivo.</small>
                <div id="fileList" class="mt-2">
                    <!-- Archivos subidos temporalmente se listarán aquí -->
                </div>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
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
            // si el usuario no completa el formulario.
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
</body>
</html>