<?php
// Script para despliegue automático desde GitHub

// --- CONFIGURACIÓN ---
// Opcional: Define un token secreto. Si lo haces, deberás configurarlo también en el webhook de GitHub.
$secret_token = 'bpmunela2025!#31416'; // Cambia null por tu token si quieres usarlo. Ejemplo: 'MiTokenSecretoSuperSeguro123'

// Ruta al archivo de log para registrar los intentos de despliegue.
$log_file = 'deploy_log.txt';

// --- VALIDACIÓN ---
// Si se ha configurado un token secreto, se valida la firma de la petición.
if ($secret_token !== null) {
    // La firma viene en la cabecera 'X-Hub-Signature-256'.
    if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
        http_response_code(403);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: Petición recibida sin firma.\n", FILE_APPEND);
        die('Error: Petición no autorizada.');
    }

    // Obtener el cuerpo de la petición.
    $payload = file_get_contents('php://input');

    // Calcular la firma esperada.
    $expected_signature = 'sha256=' . hash_hmac('sha256', $payload, $secret_token, false);

    // Comparar las firmas.
    if (!hash_equals($expected_signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'])) {
        http_response_code(403);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error: Firma inválida.\n", FILE_APPEND);
        die('Error: Firma inválida.');
    }
}

// --- EJECUCIÓN DEL DESPLIEGUE ---
// Ruta al repositorio en el servidor. __DIR__ asume que este script está en la raíz del proyecto.
$repo_dir = __DIR__;

// Comando a ejecutar: ir al directorio y hacer git pull.
// Se redirige la salida de error a la salida estándar (2>&1) para capturar todo.
$command = 'cd ' . $repo_dir . ' && git pull 2>&1';

// Ejecutar el comando.
$output = shell_exec($command);

// --- REGISTRO ---
// Guardar la fecha, hora y salida del comando en el archivo de log.
$log_message = date('Y-m-d H:i:s') . " - Despliegue ejecutado.\n" . "Salida:\n" . $output . "\n---\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// --- RESPUESTA ---
// Devolver una respuesta al webhook de GitHub para que sepa que todo fue bien.
http_response_code(200);
echo "Despliegue completado.\n";
echo "<pre>" . $output . "</pre>";

?>
