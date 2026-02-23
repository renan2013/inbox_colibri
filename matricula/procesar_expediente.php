<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/permissions.php';

// Verificar permisos (ej: solo admins pueden hacer esto)
// if(!has_permission($mysqli, 'crear_expedientes')){
//     header("location: ../dashboard.php?error=No tienes permiso para esta acción");
//     exit;
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recibir y validar datos básicos
    if (!isset($_POST['id_usuario']) || !is_numeric($_POST['id_usuario'])) {
        header("Location: crear_expediente_digital.php?error=" . urlencode("ID de usuario no válido."));
        exit;
    }
    $id_usuario = $_POST['id_usuario'];

    // 2. Comprobar si ya existe un expediente para este usuario
    $check_sql = "SELECT id_expediente FROM expedientes_digitales WHERE id_usuario = ?";
    $stmt = $mysqli->prepare($check_sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_expediente = $result->fetch_assoc();
    $stmt->close();

    // 3. Construir la consulta (INSERT o UPDATE)
    // Lista de todos los campos del formulario que corresponden a la tabla
    $fields = [
        'grado_a_matricular', 'especialidad_deseada', 'genero', 'fecha_nacimiento', 
        'lugar_nacimiento', 'nacionalidad', 'cedula_residencia', 'estado_civil', 
        'domicilio_direccion', 'domicilio_provincia', 'domicilio_canton', 'domicilio_distrito',
        'contacto_tel_habitacion', 'contacto_tel_celular', 'contacto_otro_emergencias',
        'procedencia_secundaria_institucion', 'procedencia_secundaria_ano_graduacion', 
        'procedencia_secundaria_grado_obtenido', 'procedencia_universidad', 
        'procedencia_universidad_ano_graduacion', 'procedencia_universidad_grado_obtenido', 
        'procedencia_universidad_especialidad', 'laboral_institucion', 'laboral_fecha_ingreso', 
        'laboral_puesto', 'laboral_telefono', 'laboral_extension', 'laboral_fax', 
        'laboral_correo_electronico'
    ];

    if ($existing_expediente) {
        // UPDATE
        $sql = "UPDATE expedientes_digitales SET ";
        $sql_parts = [];
        foreach ($fields as $field) {
            $sql_parts[] = "$field = ?";
        }
        $sql .= implode(', ', $sql_parts);
        $sql .= " WHERE id_usuario = ?";
    } else {
        // INSERT
        $sql = "INSERT INTO expedientes_digitales (id_usuario, " . implode(', ', $fields) . ") VALUES (?," . str_repeat('?,', count($fields) - 1) . "?)";
    }

    $stmt = $mysqli->prepare($sql);

    // 4. Bindeo de parámetros
    $types = 's' . str_repeat('s', count($fields)); // Asumimos que todos son strings, la BD convierte
    $params = [&$types];
    
    $values = [];
    if ($existing_expediente) {
        // Para UPDATE
        foreach ($fields as $field) {
            $values[$field] = $_POST[$field] ?? null;
            $params[] = &$values[$field];
        }
        $params[] = &$id_usuario;
        $types .= 'i';
    } else {
        // Para INSERT
        $params[] = &$id_usuario;
        foreach ($fields as $field) {
            $values[$field] = $_POST[$field] ?? null;
            $params[] = &$values[$field];
        }
    }
    
    call_user_func_array([$stmt, 'bind_param'], $params);

    // 5. Ejecutar y redirigir
    if ($stmt->execute()) {
        // El manejo de archivos se añadirá aquí después
        $message = $existing_expediente ? "Expediente actualizado exitosamente." : "Expediente creado exitosamente.";
        header("Location: ../dashboard.php?success=" . urlencode($message));
    } else {
        header("Location: crear_expediente_digital.php?error=" . urlencode("Error al guardar en la base de datos: " . $stmt->error));
    }

    $stmt->close();
    $mysqli->close();
    exit;

} else {
    // Redirigir si no es POST
    header("Location: crear_expediente_digital.php");
    exit;
}
?>