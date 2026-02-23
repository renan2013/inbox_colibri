<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// --- Funciones y Lógica Principal ---

function generarImagenTarjeta($saludo_id, $mysqli, $for_download = false) {
    // Obtener todos los datos necesarios
    $sql = "SELECT 
                se.mensaje_personalizado,
                u.nombre AS nombre_destinatario,
                sp.ruta_imagen
            FROM 
                saludos_enviados se
            JOIN 
                usuarios u ON se.usuario_id = u.id
            JOIN 
                saludo_plantillas sp ON se.plantilla_id = sp.id
            WHERE 
                se.id = ?";
    
    if (!$stmt = $mysqli->prepare($sql)) {
        die("Error preparando la consulta: " . $mysqli->error);
    }
    
    $stmt->bind_param("i", $saludo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows !== 1) {
        die("Tarjeta no encontrada.");
    }
    $data = $result->fetch_assoc();
    $stmt->close();

    $template_path = '../' . $data['ruta_imagen'];
    if (!file_exists($template_path)) {
        die("No se encontró el archivo de la plantilla.");
    }

    // Determinar el tipo de imagen y crear el recurso
    $image_info = getimagesize($template_path);
    $mime_type = $image_info['mime'];
    $image = null;
    if ($mime_type == 'image/jpeg') {
        $image = imagecreatefromjpeg($template_path);
    } elseif ($mime_type == 'image/png') {
        $image = imagecreatefrompng($template_path);
    } else {
        die("Formato de imagen no soportado. Use JPG o PNG.");
    }
    
    // Colores y Fuentes
    $font_color = imagecolorallocate($image, 30, 30, 30); // Un color oscuro, no negro puro
    $font_path = '../includes/fonts/Roboto-Regular.ttf'; // Asumimos que la fuente existe aquí
    if (!file_exists($font_path)) {
        // Fallback si la fuente no existe, para evitar un error fatal.
        $font_path = null;
    }

    $destinatario = "Para: " . $data['nombre_destinatario'];
    $mensaje = $data['mensaje_personalizado'];

    // Posicionar y escribir el texto (esto puede requerir ajustes)
    $image_width = imagesx($image);
    
    // Escribir destinatario
    if ($font_path) {
        imagettftext($image, 30, 0, 50, 100, $font_color, $font_path, $destinatario);
    } else {
        imagestring($image, 5, 50, 80, $destinatario, $font_color);
    }
    
    // Escribir mensaje personalizado
    if ($font_path) {
        // wordwrap para texto largo
        $wrapped_text = wordwrap($mensaje, 40, "\n");
        imagettftext($image, 24, 0, 50, 160, $font_color, $font_path, $wrapped_text);
    } else {
        imagestring($image, 5, 50, 140, $mensaje, $font_color);
    }

    // --- Salida de la Imagen ---
    $filename = "tarjeta_" . str_replace(' ', '_', $data['nombre_destinatario']) . ".png";

    if ($for_download) {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    } else {
        header('Content-Type: image/png');
    }
    
    imagepng($image);
    imagedestroy($image);
    exit();
}

// --- Controlador de Acciones ---

// Permisos y validación de ID
if (!isset($_SESSION['loggedin']) || !has_permission($mysqli, 'ver_cumpleanos')) {
    header("Location: ../dashboard.php?error=acceso_denegado");
    exit();
}
$saludo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$saludo_id) {
    header("Location: ../dashboard.php?error=id_invalido");
    exit();
}
$action = $_GET['action'] ?? 'show_page';

// Decidir qué hacer
if ($action === 'generate_image') {
    generarImagenTarjeta($saludo_id, $mysqli, false);
} elseif ($action === 'download_image') {
    generarImagenTarjeta($saludo_id, $mysqli, true);
}


// --- Si no hay acción, mostrar la página HTML ---
$page_title = "Ver Tarjeta Generada";
require_once '../includes/header.php';
?>

<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5 text-center">
    <h2>¡Tarjeta Generada!</h2>
    <p>Así se ve la tarjeta. Puedes descargarla para enviarla.</p>
    
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <img src="ver_tarjeta.php?id=<?php echo $saludo_id; ?>&action=generate_image" class="img-fluid rounded" alt="Tarjeta generada">
        </div>
        <div class="card-footer">
            <a href="ver_tarjeta.php?id=<?php echo $saludo_id; ?>&action=download_image" class="btn btn-primary">
                <i class="bi bi-download"></i> Descargar Tarjeta
            </a>
            <a href="../dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
