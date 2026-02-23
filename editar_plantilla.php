<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso de gestionar plantillas
if(!has_permission($mysqli, 'gestionar_plantillas')){
    header("location: dashboard.php?error=No tienes permiso para editar plantillas");
    exit;
}

// Validar ID de plantilla
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: gestionar_plantillas.php?error=ID de plantilla no válido");
    exit;
}
$id_plantilla = trim($_GET["id"]);

// Obtener datos de la plantilla existente
$sql_plantilla_existente = "SELECT titulo, descripcion, prioridad_default FROM tarea_plantillas WHERE id = ?";
if($stmt_plantilla_existente = $mysqli->prepare($sql_plantilla_existente)){
    $stmt_plantilla_existente->bind_param("i", $id_plantilla);
    $stmt_plantilla_existente->execute();
    $result_plantilla_existente = $stmt_plantilla_existente->get_result();
    if($result_plantilla_existente->num_rows == 1){
        $plantilla_existente = $result_plantilla_existente->fetch_assoc();
        $titulo = $plantilla_existente['titulo'];
        $descripcion = $plantilla_existente['descripcion'];
        $prioridad = $plantilla_existente['prioridad_default'];
    } else {
        header("location: gestionar_plantillas.php?error=Plantilla no encontrada");
        exit;
    }
    $stmt_plantilla_existente->close();
} else {
    die("Error al preparar la consulta de plantilla existente.");
}

// Procesar el formulario de actualización de plantilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $descripcion = trim($_POST["descripcion"]);
    $prioridad = trim($_POST["prioridad_default"]);
    
    $sql_update = "UPDATE tarea_plantillas SET titulo = ?, descripcion = ?, prioridad_default = ? WHERE id = ?";

    if ($stmt_update = $mysqli->prepare($sql_update)) {
        $stmt_update->bind_param("sssi", $titulo, $descripcion, $prioridad, $id_plantilla);

        if ($stmt_update->execute()) {
            $_SESSION['success'] = "Plantilla actualizada exitosamente.";
            header("location: gestionar_plantillas.php");
            exit;
        } else {
            $_SESSION['error'] = "¡Ups! Algo salió mal al actualizar la plantilla. Por favor, inténtalo de nuevo más tarde.";
            header("location: editar_plantilla.php?id=" . $id_plantilla);
            exit;
        }
        $stmt_update->close();
    }
} else {
    // Limpiar adjuntos temporales de la sesión
    if (isset($_SESSION['temp_adjuntos'])) {
        unset($_SESSION['temp_adjuntos']);
    }
}

$page_title = 'Editar Plantilla';
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
    <h2>Editar Plantilla</h2>
    <p>Modifique los detalles de la plantilla.</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-light p-4 rounded mt-4">
        <form action="editar_plantilla.php?id=<?php echo $id_plantilla; ?>" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título de la Plantilla</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción (Pasos a seguir)</label>
                <textarea name="descripcion" id="descripcion" rows="10"><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="prioridad_default" class="form-label">Prioridad por Defecto</label>
                <select name="prioridad_default" id="prioridad_default" class="form-select" required>
                    <option value="baja" <?php echo ($prioridad == 'baja') ? 'selected' : ''; ?>>Baja</option>
                    <option value="media" <?php echo ($prioridad == 'media') ? 'selected' : ''; ?>>Media</option>
                    <option value="alta" <?php echo ($prioridad == 'alta') ? 'selected' : ''; ?>>Alta</option>
                </select>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="gestionar_plantillas.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').addEventListener('submit', function() {
        tinymce.triggerSave();
    });
});
</script>

<?php include 'includes/footer.php'; ?>