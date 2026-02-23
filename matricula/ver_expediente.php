<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// --- Seguridad y Verificación ---
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../login.php");
    exit();
}

if (!has_permission($mysqli, 'gestionar_expedientes')) {
    header("Location: ../dashboard.php?error=no_permission");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionar_expedientes.php?error=ID de expediente no válido.");
    exit();
}

$id_expediente = (int)$_GET['id'];

// --- Consulta a la Base de Datos ---
$sql = "SELECT e.*, u.nombre AS nombre_usuario, u.apellidos, u.email 
        FROM expedientes_digitales e
        JOIN usuarios u ON e.id_usuario = u.id
        WHERE e.id_expediente = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_expediente);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: gestionar_expedientes.php?error=Expediente no encontrado.");
    exit();
}

$expediente = $result->fetch_assoc();
$stmt->close();

$page_title = 'Ver Expediente de ' . htmlspecialchars($expediente['nombre_usuario']);
require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-4">
    <!-- Miga de Pan -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="gestionar_expedientes.php">Expedientes Digitales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ver Expediente #<?php echo $id_expediente; ?></li>
        </ol>
    </nav>

    <!-- Título y Botones de Acción -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-file-earmark-text"></i> Expediente de <?php echo htmlspecialchars($expediente['nombre_usuario'] . ' ' . $expediente['apellidos']); ?></h1>
        <div>
            <a href="gestionar_expedientes.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a la lista
            </a>
            <a href="editar_expediente.php?id=<?php echo $id_expediente; ?>" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Editar
            </a>
        </div>
    </div>

    <!-- Pestañas de Navegación -->
    <ul class="nav nav-tabs" id="expedienteTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Información Principal</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="procedencia-tab" data-bs-toggle="tab" data-bs-target="#procedencia" type="button" role="tab">Procedencia</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="laboral-tab" data-bs-toggle="tab" data-bs-target="#laboral" type="button" role="tab">Información Laboral</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">Documentos</button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content card p-4" id="expedienteTabContent">
        
        <!-- Pestaña de Información Principal -->
        <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <h5 class="card-title mb-3">Información Principal</h5>
            <div class="row">
                <!-- Información de Carrera -->
                <div class="col-md-6 mb-4">
                    <h6>Información de Carrera</h6>
                    <p><strong>Grado a matricular:</strong> <?php echo htmlspecialchars($expediente['grado_a_matricular'] ?? 'No especificado'); ?></p>
                    <p><strong>Especialidad deseada:</strong> <?php echo htmlspecialchars($expediente['especialidad_deseada'] ?? 'No especificado'); ?></p>
                </div>

                <!-- Información Personal -->
                <div class="col-md-6 mb-4">
                    <h6>Información Personal</h6>
                    <p><strong>Género:</strong> <?php echo htmlspecialchars($expediente['genero'] ?? 'No especificado'); ?></p>
                    <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($expediente['fecha_nacimiento'] ?? 'No especificado'); ?></p>
                    <p><strong>Lugar de nacimiento:</strong> <?php echo htmlspecialchars($expediente['lugar_nacimiento'] ?? 'No especificado'); ?></p>
                    <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($expediente['nacionalidad'] ?? 'No especificado'); ?></p>
                    <p><strong>Cédula / Residencia:</strong> <?php echo htmlspecialchars($expediente['cedula_residencia'] ?? 'No especificado'); ?></p>
                    <p><strong>Estado Civil:</strong> <?php echo htmlspecialchars($expediente['estado_civil'] ?? 'No especificado'); ?></p>
                </div>

                <!-- Información Domiciliar -->
                <div class="col-md-6 mb-4">
                    <h6>Información Domiciliar</h6>
                    <p><strong>Dirección:</strong> <?php echo nl2br(htmlspecialchars($expediente['domicilio_direccion'] ?? 'No especificado')); ?></p>
                    <p><strong>Provincia:</strong> <?php echo htmlspecialchars($expediente['domicilio_provincia'] ?? 'No especificado'); ?></p>
                    <p><strong>Cantón:</strong> <?php echo htmlspecialchars($expediente['domicilio_canton'] ?? 'No especificado'); ?></p>
                    <p><strong>Distrito:</strong> <?php echo htmlspecialchars($expediente['domicilio_distrito'] ?? 'No especificado'); ?></p>
                </div>

                <!-- Información de Contacto -->
                <div class="col-md-6 mb-4">
                    <h6>Información de Contacto</h6>
                    <p><strong>Correo electrónico:</strong> <?php echo htmlspecialchars($expediente['email'] ?? 'No especificado'); ?></p>
                    <p><strong>Teléfono de habitación:</strong> <?php echo htmlspecialchars($expediente['contacto_tel_habitacion'] ?? 'No especificado'); ?></p>
                    <p><strong>Teléfono celular:</strong> <?php echo htmlspecialchars($expediente['contacto_tel_celular'] ?? 'No especificado'); ?></p>
                    <p><strong>Otro (emergencias):</strong> <?php echo htmlspecialchars($expediente['contacto_otro_emergencias'] ?? 'No especificado'); ?></p>
                </div>
            </div>
        </div>

        <!-- Pestaña de Procedencia -->
        <div class="tab-pane fade" id="procedencia" role="tabpanel">
            <h5 class="card-title mb-3">Información de Procedencia</h5>
             <div class="row">
                <div class="col-md-6">
                    <h6>Educación Secundaria</h6>
                    <p><strong>Institución:</strong> <?php echo htmlspecialchars($expediente['procedencia_secundaria_institucion'] ?? 'No especificado'); ?></p>
                    <p><strong>Año de graduación:</strong> <?php echo htmlspecialchars($expediente['procedencia_secundaria_ano_graduacion'] ?? 'No especificado'); ?></p>
                    <p><strong>Grado obtenido:</strong> <?php echo htmlspecialchars($expediente['procedencia_secundaria_grado_obtenido'] ?? 'No especificado'); ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Educación Universitaria (si aplica)</h6>
                    <p><strong>Universidad:</strong> <?php echo htmlspecialchars($expediente['procedencia_universidad'] ?? 'No especificado'); ?></p>
                    <p><strong>Año de graduación:</strong> <?php echo htmlspecialchars($expediente['procedencia_universidad_ano_graduacion'] ?? 'No especificado'); ?></p>
                    <p><strong>Especialidad:</strong> <?php echo htmlspecialchars($expediente['procedencia_universidad_especialidad'] ?? 'No especificado'); ?></p>
                    <p><strong>Grado obtenido:</strong> <?php echo htmlspecialchars($expediente['procedencia_universidad_grado_obtenido'] ?? 'No especificado'); ?></p>
                </div>
            </div>
        </div>

        <!-- Pestaña de Información Laboral -->
        <div class="tab-pane fade" id="laboral" role="tabpanel">
            <h5 class="card-title mb-3">Información Laboral</h5>
            <p><strong>Institución donde labora:</strong> <?php echo htmlspecialchars($expediente['laboral_institucion'] ?? 'No especificado'); ?></p>
            <p><strong>Fecha de ingreso:</strong> <?php echo htmlspecialchars($expediente['laboral_fecha_ingreso'] ?? 'No especificado'); ?></p>
            <p><strong>Puesto que desempeña:</strong> <?php echo htmlspecialchars($expediente['laboral_puesto'] ?? 'No especificado'); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($expediente['laboral_telefono'] ?? 'No especificado'); ?> <strong>Ext:</strong> <?php echo htmlspecialchars($expediente['laboral_extension'] ?? ''); ?></p>
            <p><strong>Fax:</strong> <?php echo htmlspecialchars($expediente['laboral_fax'] ?? 'No especificado'); ?></p>
            <p><strong>Correo electrónico laboral:</strong> <?php echo htmlspecialchars($expediente['laboral_correo_electronico'] ?? 'No especificado'); ?></p>
        </div>

        <!-- Pestaña de Documentos -->
        <div class="tab-pane fade" id="documentos" role="tabpanel">
             <h5 class="card-title mb-3">Documentos del Expediente</h5>
             <div class="alert alert-info">
                Funcionalidad para listar y enlazar a los documentos adjuntos se implementará aquí.
             </div>
             <!-- Aquí iría el código para listar los documentos asociados al expediente -->
        </div>
    </div>
</div>

<?php
$mysqli->close();
include '../includes/footer.php';
?>
