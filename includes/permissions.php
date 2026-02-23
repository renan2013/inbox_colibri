<?php
function has_permission($mysqli, $permission_name) {
    // Si no hay sesión o no está logueado, no tiene permisos
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        return false;
    }

    // Si los permisos no están en la sesión, los cargamos
    if (!isset($_SESSION['permissions'])) {
        $permissions = [];
        $sql = "SELECT p.nombre FROM permisos p JOIN rol_permisos rp ON p.id = rp.id_permiso WHERE rp.id_rol = ?";
        
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param("i", $_SESSION['id_rol']);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $permissions[] = $row['nombre'];
            }
            $stmt->close();
        }
        $_SESSION['permissions'] = $permissions;
    }

    // Comprobar si el permiso existe en el array de la sesión
    return in_array($permission_name, $_SESSION['permissions']);
}