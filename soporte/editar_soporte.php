<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])) {
    header("location: " . BASE_URL . "soporte/gestionar_soportes.php?error=ID de artículo no válido");
    exit;
}
$id_soporte = trim($_GET["id"]);

// Obtener datos del artículo existente
$sql_soporte_existente = "SELECT titulo, problema, solucion, categoria_id FROM soporte WHERE id = ?";
if ($stmt_soporte_existente = $mysqli->prepare($sql_soporte_existente)) {
    $stmt_soporte_existente->bind_param("i", $id_soporte);
    $stmt_soporte_existente->execute();
    $result_soporte_existente = $stmt_soporte_existente->get_result();
    if ($result_soporte_existente->num_rows == 1) {
        $soporte_existente = $result_soporte_existente->fetch_assoc();
        $titulo = $soporte_existente['titulo'];
        $problema = $soporte_existente['problema'];
        $solucion = $soporte_existente['solucion'];
        $categoria_id_existente = $soporte_existente['categoria_id'];
    } else {
        header("location: " . BASE_URL . "soporte/gestionar_soportes.php?error=Artículo no encontrado");
        exit;
    }
    $stmt_soporte_existente->close();
} else {
    die("Error al preparar la consulta del artículo existente.");
}

// Obtener lista de categorías para el dropdown
$sql_categorias = "SELECT id, nombre FROM soporte_categorias ORDER BY nombre";
$result_categorias = $mysqli->query($sql_categorias);

$message = "";
// Procesar el formulario de actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $problema = trim($_POST["problema"]);
    $solucion = trim($_POST["solucion"]);
    $categoria_id = trim($_POST["categoria_id"]);

    $sql_update = "UPDATE soporte SET titulo = ?, problema = ?, solucion = ?, categoria_id = ? WHERE id = ?";

    if ($stmt_update = $mysqli->prepare($sql_update)) {
        $stmt_update->bind_param("sssis", $titulo, $problema, $solucion, $categoria_id, $id_soporte);

        if ($stmt_update->execute()) {
            header("location: " . BASE_URL . "soporte/gestionar_soportes.php?success=Artículo actualizado con éxito");
            exit();
        } else {
            $message = "Error al actualizar el artículo.";
        }
        $stmt_update->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Artículo de Soporte - Colibrí</title>
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
    <h2>Editar Artículo de Soporte</h2>
    <p>Modifique los detalles del artículo.</p>

    <?php if(!empty($message)): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bg-light p-4 rounded mt-4">
        <form id="supportForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id_soporte; ?>" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>
            <div class="mb-3">
                <label for="problema" class="form-label">Problema</label>
                <textarea name="problema" id="problema" rows="10"><?php echo htmlspecialchars($problema); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="solucion" class="form-label">Solución</label>
                <textarea name="solucion" id="solucion" rows="10"><?php echo htmlspecialchars($solucion); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select" required>
                    <option value="">Seleccione una categoría...</option>
                    <?php
                    if ($result_categorias && $result_categorias->num_rows > 0) {
                        while($categoria = $result_categorias->fetch_assoc()) {
                            $selected = ($categoria['id'] == $categoria_id_existente) ? 'selected' : '';
                            echo '<option value="' . $categoria['id'] . '" ' . $selected . '>' . htmlspecialchars($categoria['nombre']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <a href="<?php echo BASE_URL; ?>soporte/gestionar_soportes.php" class="btn btn-secondary">Cancelar</a>
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