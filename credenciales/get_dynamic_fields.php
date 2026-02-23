<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_GET['id_form']) || !ctype_digit($_GET['id_form'])) {
    echo "ID no válido.";
    exit;
}

$id_form = $_GET['id_form'];

// Obtener datos estáticos del formulario
$sql_f = "SELECT datos_estaticos FROM formularios WHERE id = ?";
$stmt_f = $mysqli->prepare($sql_f);
$stmt_f->bind_param("i", $id_form);
$stmt_f->execute();
$res_f = $stmt_f->get_result();
$f_data = $res_f->fetch_assoc();
$stmt_f->close();

if (!empty($f_data['datos_estaticos'])) {
    echo '<div class="alert alert-info border-0 shadow-sm mb-4">';
    echo '<h6 class="alert-heading fw-bold small"><i class="bi bi-info-circle-fill"></i> Datos Estáticos para el Cliente:</h6>';
    echo '<p class="mb-0 small">' . nl2br(htmlspecialchars($f_data['datos_estaticos'])) . '</p>';
    echo '<input type="hidden" name="static_data_hidden" value="'.htmlspecialchars($f_data['datos_estaticos']).'">';
    echo '</div>';
}

$sql = "SELECT * FROM formularios_campos WHERE id_formulario = ? ORDER BY orden, id";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_form);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_campo = $row['id'];
        $nombre = htmlspecialchars($row['nombre_campo']);
        $tipo = $row['tipo_campo'];
        $requerido = $row['requerido'] ? 'required' : '';
        $opciones = $row['opciones'];
        
        echo '<div class="mb-3">';
        echo '<label class="form-label small fw-bold text-dark">'.$nombre.($row['requerido'] ? ' <span class="text-danger">*</span>' : '').'</label>';
        
        if ($tipo == 'textarea') {
            echo '<textarea name="dyn_field_'.$id_campo.'" class="form-control form-control-sm" '.$requerido.' rows="2"></textarea>';
        } elseif ($tipo == 'select') {
            $opts = explode(',', $opciones);
            echo '<select name="dyn_field_'.$id_campo.'" class="form-select form-select-sm" '.$requerido.'>';
            echo '<option value="">-- Seleccionar --</option>';
            foreach ($opts as $o) {
                $o = trim($o);
                echo '<option value="'.htmlspecialchars($o).'">'.htmlspecialchars($o).'</option>';
            }
            echo '</select>';
        } else {
            echo '<input type="'.$tipo.'" name="dyn_field_'.$id_campo.'" class="form-control form-control-sm" '.$requerido.'>';
        }
        echo '</div>';
    }
} else {
    echo '<p class="text-muted small">Este sub-módulo no tiene campos definidos todavía.</p>';
}

$stmt->close();
$mysqli->close();
?>