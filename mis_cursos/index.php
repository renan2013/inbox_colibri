<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// Proteger la página
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$isAdmin = has_permission($mysqli, 'gestionar_usuarios');
$page_title = $isAdmin ? 'Todos los Cursos Programados' : 'Mis Cursos Asignados';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

// Obtener cursos
$id_profesor = $_SESSION['id'];
$sql = "SELECT ca.id_curso_activo, ca.periodo, pe.materia, pe.codigo, p.nombre_programa, u.nombre as nombre_profesor
        FROM cursos_activos ca
        JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
        JOIN programas p ON pe.id_programa = p.id_programa
        LEFT JOIN usuarios u ON ca.id_profesor = u.id";

if ($isAdmin) {
    $sql .= " ORDER BY ca.periodo DESC, pe.materia ASC";
    $stmt = $mysqli->prepare($sql);
} else {
    $sql .= " WHERE ca.id_profesor = ? ORDER BY ca.periodo DESC, pe.materia ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_profesor);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $isAdmin ? 'Todos los Cursos' : 'Mis Cursos'; ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-video3"></i> <?php echo $isAdmin ? 'Todos los Cursos Programados' : 'Mis Cursos Asignados'; ?></h2>
    </div>

    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($curso = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($curso['codigo']); ?></span>
                                <span class="badge bg-info text-white"><?php echo htmlspecialchars($curso['periodo']); ?></span>
                            </div>
                            <h5 class="card-title text-primary"><?php echo htmlspecialchars($curso['materia']); ?></h5>
                            <p class="card-text small text-muted"><i class="bi bi-mortarboard"></i> <?php echo htmlspecialchars($curso['nombre_programa']); ?></p>
                            <?php if ($isAdmin): ?>
                                <p class="card-text small text-muted"><i class="bi bi-person-check-fill"></i> Profesor: <strong><?php echo htmlspecialchars($curso['nombre_profesor'] ?? 'No asignado'); ?></strong></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-transparent border-0 pb-3">
                            <div class="d-grid">
                                <a href="ver_curso.php?id=<?php echo $curso['id_curso_activo']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> Gestionar Curso
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-journal-x display-1 text-muted"></i>
                    <p class="mt-3 lead text-muted"><?php echo $isAdmin ? 'No hay cursos activos en el sistema.' : 'No tienes cursos asignados actualmente.'; ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
