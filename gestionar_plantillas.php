<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página
if(!has_permission($mysqli, 'gestionar_plantillas')){
    header("location: dashboard.php?error=No tienes permiso para gestionar plantillas");
    exit;
}

$page_title = 'Gestionar Plantillas de Tareas';
$message = "";
// Procesar el formulario para crear una nueva plantilla
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_plantilla'])) {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $prioridad = trim($_POST["prioridad_default"]);
    $id_creador = $_SESSION["id"];

    $sql = "INSERT INTO tarea_plantillas (titulo, descripcion, prioridad_default, id_creador) VALUES (?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssi", $titulo, $descripcion, $prioridad, $id_creador);
        if ($stmt->execute()) {
            $id_plantilla_creada = $mysqli->insert_id;

            if (isset($_SESSION['temp_adjuntos']) && !empty($_SESSION['temp_adjuntos'])) {
                $upload_dir = __DIR__ . '/uploads/';
                
                foreach ($_SESSION['temp_adjuntos'] as $adjunto_temp) {
                    $original_name = $adjunto_temp['original_name'];
                    $server_name = $adjunto_temp['server_name'];
                    $temp_path = $adjunto_temp['temp_path'];
                    $type = $adjunto_temp['type'];
                    $size = $adjunto_temp['size'];
                    $new_path = $upload_dir . $server_name;

                    if (rename($temp_path, $new_path)) {
                        $sql_adjunto = "INSERT INTO adjuntos_plantillas (id_plantilla, nombre_original, nombre_servidor, ruta_archivo, tipo_mime, tamano, id_usuario_subida) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        if ($stmt_adjunto = $mysqli->prepare($sql_adjunto)) {
                            $stmt_adjunto->bind_param("issssii", $id_plantilla_creada, $original_name, $server_name, $new_path, $type, $size, $_SESSION['id']);
                            $stmt_adjunto->execute();
                            $stmt_adjunto->close();
                        }
                    }
                }
                unset($_SESSION['temp_adjuntos']);
            }
            $message = "<div class='alert alert-success'>Plantilla creada con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al crear la plantilla.</div>";
        }
        $stmt->close();
    }
}

// Obtener todas las plantillas para mostrarlas en la tabla
$sql_plantillas = "SELECT id, titulo, prioridad_default FROM tarea_plantillas ORDER BY fecha_creacion DESC";
$result_plantillas = $mysqli->query($sql_plantillas);

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
    <h2>Gestionar Plantillas de Tareas</h2>
    <p>Crea y administra las plantillas para tareas recurrentes.</p>

    <?php echo $message; ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-3 bg-light">
                <div class="card-header">Crear Nueva Plantilla</div>
                <div class="card-body p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título de la Plantilla</label>
                            <input type="text" name="titulo" id="titulo" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción (Pasos a seguir)</label>
                            <textarea name="descripcion" id="descripcion" rows="10"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="prioridad_default" class="form-label">Prioridad por Defecto</label>
                            <select name="prioridad_default" id="prioridad_default" class="form-select" required>
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                        <button type="submit" name="crear_plantilla" class="btn btn-primary">Guardar Plantilla</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Adjuntar Archivos</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Seleccionar Archivos</label>
                        <input type="file" id="fileInput" class="form-control" multiple>
                        <small class="form-text text-muted">JPG, PNG, PDF, DOCX, etc. Máx. 5MB.</small>
                        <div id="fileList" class="mt-2"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Plantillas Existentes</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Prioridad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_plantillas && $result_plantillas->num_rows > 0): ?>
                                    <?php while($plantilla = $result_plantillas->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($plantilla['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars($plantilla['prioridad_default']); ?></td>
                                            <td>
                                                <a href="editar_plantilla.php?id=<?php echo $plantilla['id']; ?>" class="btn btn-sm btn-secondary" title="Editar"><i class="bi bi-pencil"></i></a>
                                                <a href="#" class="btn btn-sm btn-secondary" onclick="confirmDeletePlantilla(event, 'eliminar_plantilla.php?id=<?php echo $plantilla['id']; ?>', '<?php echo htmlspecialchars($plantilla['titulo']); ?>');" title="Eliminar"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hay plantillas creadas.</td>
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

<script>
function confirmDeletePlantilla(event, url, title) {
    event.preventDefault();
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Vas a eliminar la plantilla '${title}'. Esta acción es irreversible.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    function showMessage(msg, type = 'info') {
        let alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
        alertDiv.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        fileList.before(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    fileInput.addEventListener('change', function() {
        if (this.files.length === 0) return;

        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', 'template');

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
                listItem.remove();
                if (data.success) {
                    const uploadedItem = document.createElement('div');
                    uploadedItem.className = 'd-flex justify-content-between align-items-center p-2 border rounded mb-1 bg-light';
                    uploadedItem.innerHTML = `<span><i class="bi bi-check-circle-fill text-success"></i> ${data.file.original_name}</span>
                                              <button type="button" class="btn btn-sm btn-danger remove-file" data-server-name="${data.file.server_name}"><i class="bi bi-x-lg"></i></button>`;
                    fileList.appendChild(uploadedItem);
                    showMessage(`"${data.file.original_name}" subido temporalmente.`, 'success');
                } else {
                    showMessage(`Error al subir "${file.name}": ${data.message}`, 'danger');
                }
            })
            .catch(error => {
                listItem.remove();
                showMessage(`Error de red al subir "${file.name}".`, 'danger');
            });
        }
        this.value = '';
    });

    fileList.addEventListener('click', function(event) {
        const button = event.target.closest('.remove-file');
        if (button) {
            const serverName = button.dataset.serverName;
            button.closest('div').remove();
            showMessage(`"${serverName}" eliminado de la lista.`, 'warning');
        }
    });

    fetch('clear_temp_adjuntos.php')
        .then(response => response.json())
        .then(data => {
            if(data.success) console.log('Adjuntos temporales de sesión limpiados.');
        });

    document.querySelector('form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
