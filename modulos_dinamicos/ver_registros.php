<?php
ob_start();
session_start();

require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: " . BASE_URL . "login.php");
    exit;
}

if(!isset($_GET['id']) || !ctype_digit($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id_form = $_GET['id'];
$message = "";
$message_type = "";

// Obtener detalles del formulario
$sql_form = "SELECT * FROM formularios WHERE id = ?";
$stmt_form = $mysqli->prepare($sql_form);
$stmt_form->bind_param("i", $id_form);
$stmt_form->execute();
$formulario = $stmt_form->get_result()->fetch_assoc();
$stmt_form->close();

if(!$formulario){
    header("Location: index.php");
    exit;
}

// Obtener nombres de campos para la cabecera de la tabla
$sql_fields = "SELECT id, nombre_campo FROM formularios_campos WHERE id_formulario = ? ORDER BY orden, id";
$stmt_fields = $mysqli->prepare($sql_fields);
$stmt_fields->bind_param("i", $id_form);
$stmt_fields->execute();
$result_fields = $stmt_fields->get_result();
$campos_header = $result_fields->fetch_all(MYSQLI_ASSOC);
$stmt_fields->close();

// Obtener todos los registros y sus valores
$sql_registros = "SELECT fr.id, fr.fecha_registro, u.nombre as usuario_nombre 
                   FROM formularios_registros fr 
                   JOIN usuarios u ON fr.id_usuario = u.id 
                   WHERE fr.id_formulario = ? 
                   ORDER BY fr.fecha_registro DESC";
$stmt_reg = $mysqli->prepare($sql_registros);
$stmt_reg->bind_param("i", $id_form);
$stmt_reg->execute();
$result_registros = $stmt_reg->get_result();

// --- VISTA ---
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container-fluid mt-4 px-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php">Sub-Módulos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ver Registros: <?php echo htmlspecialchars($formulario['nombre']); ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-table"></i> Datos Registrados: <?php echo htmlspecialchars($formulario['nombre']); ?></h2>
        <a href="index.php" class="btn btn-secondary">Regresar al Listado</a>
    </div>

    <!-- Tabla Dinámica de Datos -->
    <div class="card shadow border-0 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="min-width: 150px;">Registrado Por</th>
                            <th style="min-width: 150px;">Fecha</th>
                            <?php foreach($campos_header as $campo): ?>
                                <th style="min-width: 150px;"><?php echo htmlspecialchars($campo['nombre_campo']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_registros && $result_registros->num_rows > 0): ?>
                            <?php while($reg = $result_registros->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($reg['usuario_nombre']); ?></strong></td>
                                    <td class="small"><?php echo date("d/m/Y H:i", strtotime($reg['fecha_registro'])); ?></td>
                                    <?php 
                                    // Para cada campo en la cabecera, buscamos su valor en este registro
                                    foreach($campos_header as $campo): 
                                        $id_registro = $reg['id'];
                                        $id_campo = $campo['id'];
                                        
                                        $sql_val = "SELECT valor FROM formularios_valores WHERE id_registro = ? AND id_campo = ?";
                                        $stmt_val = $mysqli->prepare($sql_val);
                                        $stmt_val->bind_param("ii", $id_registro, $id_campo);
                                        $stmt_val->execute();
                                        $val = $stmt_val->get_result()->fetch_assoc();
                                        $stmt_val->close();
                                    ?>
                                        <td class="small">
                                            <?php 
                                            $valor_mostrar = $val ? $val['valor'] : '—';
                                            // Si parece un link, lo hacemos clickeable
                                            if (filter_var($valor_mostrar, FILTER_VALIDATE_URL)) {
                                                echo '<a href="' . htmlspecialchars($valor_mostrar) . '" target="_blank">' . htmlspecialchars($valor_mostrar) . '</a>';
                                            } else {
                                                echo nl2br(htmlspecialchars($valor_mostrar));
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo count($campos_header) + 2; ?>" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted display-4"></i>
                                    <p class="text-muted mt-3">No hay registros enviados todavía.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
ob_end_flush();
?>