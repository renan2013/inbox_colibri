<?php
session_start();
require_once "includes/db_connect.php";
require_once "includes/permissions.php";

// Proteger la página: solo para usuarios con permiso
if(!has_permission($mysqli, 'gestionar_plantillas')){ // Usando un permiso existente temporalmente
    header("location: dashboard.php?error=No tienes permiso para editar etiquetas");
    exit;
}

// Validar ID de etiqueta
if(!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])){
    header("location: gestionar_etiquetas.php?error=ID de etiqueta no válido");
    exit;
}
$id_etiqueta = trim($_GET["id"]);

// Obtener datos de la etiqueta existente
$sql_etiqueta_existente = "SELECT id, nombre FROM etiquetas WHERE id = ?";
if($stmt_etiqueta_existente = $mysqli->prepare($sql_etiqueta_existente)){
    $stmt_etiqueta_existente->bind_param("i", $id_etiqueta);
    $stmt_etiqueta_existente->execute();
    $result_etiqueta_existente = $stmt_etiqueta_existente->get_result();
    if($result_etiqueta_existente->num_rows == 1){
        $etiqueta_existente = $result_etiqueta_existente->fetch_assoc();
        $nombre = $etiqueta_existente['nombre'];
    } else {
        header("location: gestionar_etiquetas.php?error=Etiqueta no encontrada");
        exit;
    }
    $stmt_etiqueta_existente->close();
} else {
    die("Error al preparar la consulta de etiqueta existente.");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Etiqueta - Colibrí</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>Editar Etiqueta</h2>
    <p>Modifique el nombre de la etiqueta.</p>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="editar_etiqueta_process.php" method="post">
        <input type="hidden" name="id" value="<?php echo $id_etiqueta; ?>">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Etiqueta</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" required>
        </div>
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="gestionar_etiquetas.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'includes/footer.php'; ?>
</body>
</html>