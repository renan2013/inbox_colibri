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
$sql_form = "SELECT * FROM formularios WHERE id = ? AND activo = 1";
$stmt_form = $mysqli->prepare($sql_form);
$stmt_form->bind_param("i", $id_form);
$stmt_form->execute();
$formulario = $stmt_form->get_result()->fetch_assoc();
$stmt_form->close();

if(!$formulario){
    header("Location: index.php");
    exit;
}

// Obtener campos para renderizar
$sql_fields = "SELECT * FROM formularios_campos WHERE id_formulario = ? ORDER BY orden, id";
$stmt_fields = $mysqli->prepare($sql_fields);
$stmt_fields->bind_param("i", $id_form);
$stmt_fields->execute();
$result_fields = $stmt_fields->get_result();
$campos = $result_fields->fetch_all(MYSQLI_ASSOC);

// 1. LÓGICA DE PROCESAMIENTO: Guardar Registro y Valores
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit_data') {
    $id_usuario = $_SESSION['id'];
    
    // Iniciar transacción para asegurar integridad
    $mysqli->begin_transaction();
    
    try {
        // Crear el registro de cabecera
        $sql_reg = "INSERT INTO formularios_registros (id_formulario, id_usuario) VALUES (?, ?)";
        $stmt_reg = $mysqli->prepare($sql_reg);
        $stmt_reg->bind_param("ii", $id_form, $id_usuario);
        $stmt_reg->execute();
        $id_registro = $mysqli->insert_id;
        $stmt_reg->close();
        
        // Guardar cada valor dinámico
        $sql_val = "INSERT INTO formularios_valores (id_registro, id_campo, valor) VALUES (?, ?, ?)";
        $stmt_val = $mysqli->prepare($sql_val);
        
        foreach ($campos as $campo) {
            $id_campo = $campo['id'];
            // El nombre del campo en POST es 'field_' + id
            $valor = isset($_POST["field_$id_campo"]) ? trim($_POST["field_$id_campo"]) : "";
            
            // Si es un password, podrías decidir no guardarlo en texto plano, 
            // pero para este módulo de gestión interna suele ser necesario.
            $stmt_val->bind_param("iis", $id_registro, $id_campo, $valor);
            $stmt_val->execute();
        }
        $stmt_val->close();
        
        $mysqli->commit();
        header("Location: index.php?msg=data_saved");
        exit;
        
    } catch (Exception $e) {
        $mysqli->rollback();
        $message = "Error al procesar el formulario: " . $e->getMessage();
        $message_type = "danger";
    }
}

// --- VISTA ---
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>dashboard.php">Inicio</a></li>
            <li class="breadcrumb-item"><a href="index.php">Sub-Módulos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Enviar Formulario</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-pencil-square"></i> <?php echo htmlspecialchars($formulario['nombre']); ?></h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($formulario['descripcion']); ?></p>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $id_form; ?>" method="post">
                        <input type="hidden" name="action" value="submit_data">
                        
                        <?php foreach($campos as $campo): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold"><?php echo htmlspecialchars($campo['nombre_campo']); ?><?php echo $campo['requerido'] ? ' <span class="text-danger">*</span>' : ''; ?></label>
                                
                                <?php 
                                $input_name = "field_" . $campo['id'];
                                $required_attr = $campo['requerido'] ? 'required' : '';
                                
                                switch($campo['tipo_campo']):
                                    case 'textarea': ?>
                                        <textarea name="<?php echo $input_name; ?>" class="form-control" rows="4" <?php echo $required_attr; ?>></textarea>
                                    <?php break;
                                    
                                    case 'select': 
                                        $options = explode(',', $campo['opciones']); ?>
                                        <select name="<?php echo $input_name; ?>" class="form-select" <?php echo $required_attr; ?>>
                                            <option value="">-- Seleccionar Opción --</option>
                                            <?php foreach($options as $opt): 
                                                $opt = trim($opt); ?>
                                                <option value="<?php echo htmlspecialchars($opt); ?>"><?php echo htmlspecialchars($opt); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php break;
                                    
                                    default: // text, password, number, date, email, url ?>
                                        <input type="<?php echo $campo['tipo_campo']; ?>" name="<?php echo $input_name; ?>" class="form-control" <?php echo $required_attr; ?>>
                                    <?php break;
                                endswitch; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if(!empty($formulario['datos_estaticos'])): ?>
                            <div class="alert alert-light border-start border-4 border-info shadow-sm mb-4">
                                <h6 class="fw-bold text-info"><i class="bi bi-info-circle"></i> Información Importante:</h6>
                                <div class="small"><?php echo nl2br(htmlspecialchars($formulario['datos_estaticos'])); ?></div>
                            </div>
                        <?php endif; ?>

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-success btn-lg px-5" onclick="this.disabled=true; this.innerHTML='Enviando...'; this.form.submit();">Enviar Información</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once '../includes/footer.php'; 
ob_end_flush();
?>