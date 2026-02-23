<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Por ahora, permitimos a todos los usuarios gestionar soportes.
// En el futuro, se puede cambiar esto a un permiso específico.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$message = "";
// Procesar el formulario para crear un nuevo artículo de soporte
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_soporte'])) {
    $titulo = trim($_POST["titulo"]);
    $problema = trim($_POST["problema"]);
    $solucion = trim($_POST["solucion"]);
    $categoria_id = trim($_POST["categoria_id"]);
    $id_creador = $_SESSION["id"];

    $sql = "INSERT INTO soporte (titulo, problema, solucion, categoria_id, id_creador) VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("sssis", $titulo, $problema, $solucion, $categoria_id, $id_creador);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Artículo de soporte creado con éxito.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error al crear el artículo.</div>";
        }
        $stmt->close();
    }
}

// Obtener todos los artículos de soporte para mostrarlos en la tabla
$sql_soportes = "SELECT s.id, s.titulo, sc.nombre as categoria FROM soporte s LEFT JOIN soporte_categorias sc ON s.categoria_id = sc.id ORDER BY s.fecha_creacion DESC";
$result_soportes = $mysqli->query($sql_soportes);

// Obtener todas las categorías de soporte
$sql_categorias = "SELECT id, nombre FROM soporte_categorias ORDER BY nombre ASC";
$result_categorias = $mysqli->query($sql_categorias);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Base de Conocimiento - Colibrí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <h2>Gestionar Base de Conocimiento</h2>
    <p>Crea y administra artículos para la base de conocimiento.</p>

    <?php echo $message; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3 bg-light">
                <div class="card-header">Crear Nuevo Artículo</div>
                <div class="card-body p-4">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título del Artículo</label>
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
                                <option value="">Seleccione una categoría</option>
                                <?php while($categoria = $result_categorias->fetch_assoc()): ?>
                                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="crear_soporte" class="btn btn-primary">Guardar Artículo</button>
                    </form>
                </div>
            </div>
        </div>

       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>