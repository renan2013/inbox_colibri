<?php
session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";
require_once "../includes/email_sender.php";

// Proteger la página
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Verificar que hay un acta para enviar
if (!isset($_SESSION['acta_contexto']) || !file_exists($_SESSION['acta_contexto']['ruta_archivo'])) {
    header("location: index.php?error=No hay un acta generada para enviar.");
    exit;
}

$acta_contexto = $_SESSION['acta_contexto'];
$ruta_archivo = $acta_contexto['ruta_archivo'];
$nombre_archivo = basename($ruta_archivo);
$message = '';
$error = '';

// Lógica para enviar el correo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enviar_correo'])) {
    $to = 'dti@unela.ac.cr';
    
    // Construir asunto y cuerpo detallados
    $subject = "Acta de Calificaciones: " . $acta_contexto['nombre_curso'] . " - " . $acta_contexto['periodo'];
    $body = "
        <p>Saludos,</p>
        <p>Se adjunta el acta de calificaciones generada desde el sistema Inbox con los siguientes detalles:</p>
        <ul>
            <li><strong>Programa:</strong> " . htmlspecialchars($acta_contexto['nombre_programa']) . "</li>
            <li><strong>Curso:</strong> " . htmlspecialchars($acta_contexto['nombre_curso']) . "</li>
            <li><strong>Período:</strong> " . htmlspecialchars($acta_contexto['periodo']) . "</li>
            <li><strong>Profesor:</strong> " . htmlspecialchars($acta_contexto['nombre_profesor']) . "</li>
        </ul>
        <p>El documento PDF está adjunto en este correo.</p>
        <p><strong>Por favor, no responda a este correo, ya que ha sido generado automáticamente por el sistema Inbox.</strong></p>
        <br>
        <p>Atentamente:</p>
        <p><strong>" . htmlspecialchars($acta_contexto['nombre_profesor']) . "</strong></p>
        <p><em>Este es un mensaje generado automáticamente.</em></p>
    ";

    if (sendEmailWithAttachment($to, $subject, $body, $ruta_archivo)) {
        $message = "El correo con el acta ha sido enviado exitosamente a DTI.";
        // Limpiar la sesión para no volver a enviar el mismo archivo
        unset($_SESSION['acta_contexto']);
    } else {
        $error = "Hubo un problema al enviar el correo.";
    }
}

$page_title = 'Confirmar Envío de Acta';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php">Mis Cursos</a></li>
            <li class="breadcrumb-item"><a href="ver_curso.php?id=<?php echo htmlspecialchars($acta_contexto['id_curso']); ?>"><?php echo htmlspecialchars($acta_contexto['nombre_curso']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Confirmar Envío de Acta</li>
        </ol>
    </nav>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2><i class="bi bi-file-earmark-check"></i> Confirmar Envío de Acta</h2>
                </div>
                <div class="card-body">
                    <?php if ($message || $error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<?php echo $message ? "¡Envío Exitoso!" : "Error en el Envío"; ?>',
                text: '<?php echo $message ? addslashes($message) : addslashes($error); ?>',
                icon: '<?php echo $message ? "success" : "error"; ?>',
                confirmButtonText: 'Aceptar'
            }).then((result) => {
                // Redirigir después de mostrar el mensaje
                window.location.href = 'ver_curso.php?id=<?php echo $acta_contexto['id_curso']; ?>';
            });
        });
    </script>
<?php else: ?>
    <?php if (isset($_SESSION['acta_contexto'])): ?>
        <p>Se ha generado el acta de calificaciones para el curso <strong><?php echo htmlspecialchars($acta_contexto['nombre_curso']); ?></strong>. Por favor, revísala antes de enviarla.</p>
        <div class="text-center my-4">
            <a href="<?php echo htmlspecialchars($ruta_archivo); ?>" target="_blank" class="btn btn-lg btn-secondary">
                <i class="bi bi-file-earmark-pdf-fill"></i> Ver Acta (<?php echo htmlspecialchars($nombre_archivo); ?>)
            </a>
        </div>
        <p class="text-muted text-center">El siguiente botón enviará el acta por correo electrónico a <strong>dti@unela.ac.cr</strong>.</p>
        <form action="confirmar_envio_acta.php" method="POST">
            <div class="d-grid">
                <button type="submit" name="enviar_correo" class="btn btn-primary btn-lg">
                    <i class="bi bi-send"></i> Enviar Correo a DTI
                </button>
            </div>
        </form>
    <?php endif; ?>
<?php endif; ?>

                </div>
                <div class="card-footer text-center">
                    <a href="ver_curso.php?id=<?php echo htmlspecialchars($acta_contexto['id_curso']); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al Curso
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
