<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

$page_title = "Gestionar Precios de Cursos";
require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gestionar Precios de Cursos</li>
        </ol>
    </nav>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Gestionar Precios de Cursos</h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Listado de Niveles y Precios</span>
                    <a href="crear_precio.php" class="btn btn-primary">Añadir Nuevo Nivel</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nivel Académico</th>
                                    <th>Precio</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mover la consulta aquí para asegurar que se ejecuta cada vez que se carga la página
                                $sql = "SELECT id, nivel, precio FROM precios_cursos_conesup ORDER BY nivel";
                                $result = $mysqli->query($sql);

                                if ($result && $result->num_rows > 0):
                                    while($row = $result->fetch_assoc()):
                                ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nivel']); ?></td>
                                            <td>₡<?php echo number_format($row['precio'], 2); ?></td>
                                            <td class="text-end">
                                                <a href="editar_precio.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Editar
                                                </a>
                                                <a href="eliminar_precio.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este nivel?');">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </td>
                                        </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No hay precios registrados.</td>
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

<?php include '../includes/footer.php'; ?>
