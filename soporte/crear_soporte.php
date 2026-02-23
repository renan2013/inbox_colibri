<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Obtener lista de categorías para el dropdown
$sql_categorias = "SELECT id, nombre FROM soporte_categorias ORDER BY nombre";
$result_categorias = $mysqli->query($sql_categorias);

$message = "";
// Procesar el formulario de creación de artículo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $problema = trim($_POST["problema"]);
    $solucion = trim($_POST["solucion"]);
    $categoria_id = trim($_POST["categoria_id"]);
    $id_creador = $_SESSION["id"];

    $sql = "INSERT INTO soporte (titulo, problema, solucion, categoria_id, id_creador) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssis", $titulo, $problema, $solucion, $categoria_id, $id_creador);

        if ($stmt->execute()) {
            header("location: " . $base_url . "/soporte/gestionar_soportes.php?success=Articulo Creado con Éxito");
            exit();
        } else {
            $message = "Error al crear el artículo.";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Artículo de Soporte - BPM Unela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/jjwu40cfr9t3r2semmriq8uv0lvwbgdkjla0psbt7snt9itx/tinymce/7/tinymce.min.js"></script>
    <script>
      tinymce.init({
        selector: '#problema, #solucion',
        plugins: 'code table lists image',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | image'
      });
    </script>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Crear Nuevo Artículo de Soporte</h2>
    <p>Complete el formulario para crear un nuevo artículo para la base de conocimiento.</p>

    <?php if(!empty($message)): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bg-light p-4 rounded mt-4">
        <form id="supportForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="problema" class="form-label">Problema</label>
                <textarea name="problema" id="problema" rows="10"></textarea>
            </div>
            <div class="mb-3">
                <label for="solucion" class="form-label">Solución</label>
                <textarea name="solucion" id="solucion" rows="10"></textarea>
            </div>
            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select" required>
                    <option value="">Seleccione una categoría...</option>
                    <?php
                    if ($result_categorias && $result_categorias->num_rows > 0) {
                        while($categoria = $result_categorias->fetch_assoc()) {
                            echo '<option value="' . $categoria['id'] . '">' . htmlspecialchars($categoria['nombre']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Artículo</button>
                <a href="<?php echo $base_url; ?>/soporte/gestionar_soportes.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const supportForm = document.getElementById('supportForm');

    supportForm.addEventListener('submit', function(e) {
        tinymce.triggerSave();
    });
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>