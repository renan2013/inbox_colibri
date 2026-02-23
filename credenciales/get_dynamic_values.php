<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_GET['id_reg']) || !ctype_digit($_GET['id_reg'])) {
    echo "ID no válido.";
    exit;
}

$id_reg = $_GET['id_reg'];

// Obtener los valores guardados vinculados a los campos del formulario
$sql = "SELECT c.*, v.valor 
        FROM formularios_campos c 
        LEFT JOIN formularios_valores v ON c.id = v.id_campo AND v.id_registro = ?
        WHERE c.id_formulario = (SELECT id_formulario FROM formularios_registros WHERE id = ?)
        ORDER BY c.orden, c.id";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id_reg, $id_reg);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_campo = $row['id'];
        $nombre = htmlspecialchars($row['nombre_campo']);
        $tipo = $row['tipo_campo'];
        $requerido = $row['requerido'] ? 'required' : '';
        $opciones = $row['opciones'];
        $valor = $row['valor'] ?? "";
        
        echo '<div class="mb-3">';
        echo '<label class="form-label small fw-bold text-dark">'.$nombre.($row['requerido'] ? ' <span class="text-danger">*</span>' : '').'</label>';
        
        if ($tipo == 'textarea') {
            echo '<textarea name="dyn_field_'.$id_campo.'" class="form-control form-control-sm" '.$requerido.' rows="2">'.htmlspecialchars($valor).'</textarea>';
        } elseif ($tipo == 'select') {
            $opts = explode(',', $opciones);
            echo '<select name="dyn_field_'.$id_campo.'" class="form-select form-select-sm" '.$requerido.'>';
            echo '<option value="">-- Seleccionar --</option>';
            foreach ($opts as $o) {
                $o = trim($o);
                $selected = ($o == $valor) ? 'selected' : '';
                echo '<option value="'.htmlspecialchars($o).'" '.$selected.'>'.htmlspecialchars($o).'</option>';
            }
            echo '</select>';
        } else {
            echo '<input type="'.$tipo.'" name="dyn_field_'.$id_campo.'" value="'.htmlspecialchars($valor).'" class="form-control form-control-sm" '.$requerido.'>';
        }
        echo '</div>';
    }
}

// También recuperar los datos estáticos para mostrarlos al final
$sql_static = "SELECT f.datos_estaticos 
               FROM formularios f 
               JOIN formularios_registros r ON f.id = r.id_formulario 
               WHERE r.id = ?";
$stmt_s = $mysqli->prepare($sql_static);
$stmt_s->bind_param("i", $id_reg);
$stmt_s->execute();
$res_s = $stmt_s->get_result()->fetch_assoc();

if (!empty($res_s['datos_estaticos'])) {
    echo '<div class="alert alert-light border-start border-4 border-success shadow-sm mt-4">';
    echo '<h6 class="alert-heading fw-bold small text-success">Datos Estáticos:</h6>';
    echo '<p class="mb-0 small">' . nl2br(htmlspecialchars($res_s['datos_estaticos'])) . '</p>';
    echo '<input type="hidden" name="static_data_hidden" value="'.htmlspecialchars($res_s['datos_estaticos']).'">';
    echo '</div>';
}

$stmt->close();
$mysqli->close();
?>