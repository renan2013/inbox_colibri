<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT nivel, precio FROM precios_cursos_conesup WHERE id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $nivel = $row['nivel'];
        $precio = $row['precio'];
    } else {
        $_SESSION['error'] = "No se encontró el nivel de precio.";
        header("location: index.php");
        exit;
    }
    $stmt->close();
}

$page_title = "Editar Nivel de Precio";
require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>precios_conesup/index.php">Gestionar Precios de Cursos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar Nivel de Precio</li>
        </ol>
    </nav>
</div>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Editar Nivel de Precio</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="editar_precio_process.php" method="POST">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <div class="mb-3">
                            <label for="nivel" class="form-label">Nombre del Nivel</label>
                            <input type="text" name="nivel" id="nivel" class="form-control" value="<?php echo htmlspecialchars($nivel); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" name="precio" id="precio" class="form-control" value="<?php echo htmlspecialchars($precio); ?>" step="0.01" min="0" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Actualizar Nivel</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
