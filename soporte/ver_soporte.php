<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET["id"]) || empty(trim($_GET["id"])) || !ctype_digit($_GET["id"])) {
    header("location: gestionar_soportes.php?error=ID de artículo no válido");
    exit;
}
$id_soporte = trim($_GET["id"]);

$sql = "SELECT s.id, s.titulo, s.problema, s.solucion, s.fecha_creacion, sc.nombre AS categoria, u.nombre AS nombre_creador FROM soporte s LEFT JOIN soporte_categorias sc ON s.categoria_id = sc.id JOIN usuarios u ON s.id_creador = u.id WHERE s.id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id_soporte);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $soporte = $result->fetch_assoc();
        } else {
            header("location: gestionar_soportes.php?error=Artículo no encontrado");
            exit;
        }
    } else {
        die("Error al ejecutar la consulta del artículo.");
    }
    $stmt->close();
} else {
    die("Error al preparar la consulta del artículo.");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Artículo: <?php echo htmlspecialchars($soporte['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Detalle del Artículo</h2>
        <a href="gestionar_soportes.php" class="btn btn-secondary">Volver a la Base de Conocimiento</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?php echo htmlspecialchars($soporte['titulo']); ?></h3>
        </div>
        <div class="card-body">
            <h4>Problema</h4>
            <div class="p-3 bg-light rounded mb-4"><?php echo $soporte['problema']; ?></div>
            <h4>Solución</h4>
            <div class="p-3 bg-light rounded"><?php echo $soporte['solucion']; ?></div>
        </div>
        <div class="card-footer text-muted">
            Categoría: <?php echo htmlspecialchars($soporte['categoria']); ?> | Creado por: <?php echo htmlspecialchars($soporte['nombre_creador']); ?> el <?php echo date("d/m/Y H:i", strtotime($soporte['fecha_creacion'])); ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>