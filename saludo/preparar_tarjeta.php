<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$page_title = "Preparar Tarjeta de Saludo";
require_once '../includes/header.php'; // Ajustar la ruta para salir de la carpeta 'saludo'
require_once '../includes/permissions.php';

// Verificar login y permisos
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}
if (!has_permission($mysqli, 'ver_cumpleanos')) {
    header("Location: ../dashboard.php");
    exit();
}

// Validar el ID de usuario desde la URL
$usuario_id = filter_input(INPUT_GET, 'usuario_id', FILTER_VALIDATE_INT);
if (!$usuario_id) {
    header("Location: ../dashboard.php?error=" . urlencode("ID de usuario no válido."));
    exit();
}

// Obtener datos del usuario para quien es la tarjeta
$sql_usuario = "SELECT nombre FROM usuarios WHERE id = ?";
if ($stmt_usuario = $mysqli->prepare($sql_usuario)) {
    $stmt_usuario->bind_param("i", $usuario_id);
    $stmt_usuario->execute();
    $result_usuario = $stmt_usuario->get_result();
    if ($result_usuario->num_rows === 1) {
        $usuario = $result_usuario->fetch_assoc();
        $nombre_usuario = $usuario['nombre'];
    } else {
        header("Location: ../dashboard.php?error=" . urlencode("Usuario no encontrado."));
        exit();
    }
    $stmt_usuario->close();
}

// Obtener categorías de tarjetas
$categorias = [];
$sql_cat = "SELECT id, nombre FROM saludo_categorias ORDER BY nombre ASC";
if ($result_cat = $mysqli->query($sql_cat)) {
    while ($row = $result_cat->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener todas las plantillas
$plantillas = [];
$sql_plantillas = "SELECT id, nombre, ruta_imagen, categoria_id FROM saludo_plantillas ORDER BY nombre ASC";
if ($result_plantillas = $mysqli->query($sql_plantillas)) {
    while ($row = $result_plantillas->fetch_assoc()) {
        $plantillas[] = $row;
    }
}

?>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">
    <h2>Preparar Tarjeta de Saludo para: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong></h2>
    <hr>

    <form id="form-preparar-tarjeta" action="preparar_tarjeta_process.php" method="POST">
        <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
        <input type="hidden" name="plantilla_id" id="plantilla_id_hidden" required>

        <!-- Filtro por Categoría -->
        <div class="mb-4">
            <label for="categoria-filtro" class="form-label">Filtrar por Categoría:</label>
            <select id="categoria-filtro" class="form-select" style="width: auto;">
                <option value="all">Todas</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Selección de Plantilla -->
        <div class="mb-4">
            <h5>Selecciona una Plantilla:</h5>
            <div id="plantillas-container" class="row g-3">
                <?php if (empty($plantillas)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning">No hay plantillas disponibles. Por favor, <a href="gestionar_plantillas.php">sube una plantilla</a> primero.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($plantillas as $plantilla): ?>
                        <div class="col-md-3 col-sm-6 plantilla-card" data-categoria-id="<?php echo $plantilla['categoria_id']; ?>">
                            <div class="card h-100" onclick="seleccionarPlantilla(this, <?php echo $plantilla['id']; ?>);">
                                <img src="../<?php echo htmlspecialchars($plantilla['ruta_imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($plantilla['nombre']); ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?php echo htmlspecialchars($plantilla['nombre']); ?></h6>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mensaje Personalizado -->
        <div class="mb-3">
            <label for="mensaje_personalizado" class="form-label">Mensaje Personalizado (opcional):</label>
            <textarea name="mensaje_personalizado" id="mensaje_personalizado" class="form-control" rows="3"></textarea>
        </div>

        <hr>
        <button type="submit" class="btn btn-primary" id="btn-generar" disabled>Generar Tarjeta</button>
        <a href="../dashboard.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<style>
    .plantilla-card .card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: border-color 0.2s ease-in-out;
    }
    .plantilla-card .card:hover {
        border-color: #0d6efd;
    }
    .plantilla-card .card.selected {
        border-color: #0d6efd;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filtro = document.getElementById('categoria-filtro');
    const plantillas = document.querySelectorAll('.plantilla-card');

    filtro.addEventListener('change', function() {
        const categoriaSeleccionada = this.value;
        plantillas.forEach(function(plantilla) {
            if (categoriaSeleccionada === 'all' || plantilla.dataset.categoriaId === categoriaSeleccionada) {
                plantilla.style.display = 'block';
            } else {
                plantilla.style.display = 'none';
            }
        });
    });
});

function seleccionarPlantilla(cardElement, plantillaId) {
    // Desmarcar todas las tarjetas
    document.querySelectorAll('.plantilla-card .card').forEach(function(card) {
        card.classList.remove('selected');
    });

    // Marcar la tarjeta seleccionada
    cardElement.classList.add('selected');

    // Guardar el ID en el input hidden
    document.getElementById('plantilla_id_hidden').value = plantillaId;
    
    // Habilitar el botón de generar
    document.getElementById('btn-generar').disabled = false;
}
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html>
