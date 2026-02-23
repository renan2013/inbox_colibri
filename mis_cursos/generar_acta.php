<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once "../includes/db_connect.php";

// Función de codificación
if (!function_exists('to_pdf_encoding')) {
    function to_pdf_encoding($txt) {
        if ($txt === null) return '';
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }
}

if(!isset($_SESSION["loggedin"]) || !isset($_GET['id'])){
    die("Acceso denegado.");
}

$id_curso = $_GET['id'];

// 1. Obtener datos del curso
$sql_curso = "SELECT ca.periodo, pe.id_plan, pe.materia, pe.codigo, p.nombre_programa, u.nombre as profesor 
              FROM cursos_activos ca
              JOIN plan_estudios pe ON ca.id_plan = pe.id_plan
              JOIN programas p ON pe.id_programa = p.id_programa
              LEFT JOIN usuarios u ON ca.id_profesor = u.id
              WHERE ca.id_curso_activo = ?";
$stmt = $mysqli->prepare($sql_curso);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$res_curso = $stmt->get_result();
$curso = $res_curso->fetch_assoc();

if(!$curso) die("Curso no encontrado.");

// 2. Obtener Rubros de Evaluación (Sílabo)
$rubros = [];
// Buscamos el sílabo vinculado al plan de estudios, independientemente del profesor
$sql_rubros = "SELECT re.id_evaluacion, re.rubro, re.porcentaje 
               FROM silabo_evaluacion re 
               JOIN silabos s ON re.id_silabo = s.id_silabo 
               WHERE s.id_plan = ?
               ORDER BY re.id_evaluacion ASC";
$stmt_r = $mysqli->prepare($sql_rubros);
$stmt_r->bind_param("i", $curso['id_plan']);
$stmt_r->execute();
$res_rubros = $stmt_r->get_result();
while($r = $res_rubros->fetch_assoc()) $rubros[] = $r;
$stmt_r->close();

// 3. Obtener alumnos y notas finales
$sql_alum = "SELECT m.id_matricula, u.nombre, u.cedula, m.calificacion 
             FROM matriculas m
             JOIN usuarios u ON m.id_estudiante = u.id
             WHERE m.id_curso_activo = ?
             ORDER BY u.nombre ASC";
$stmt_alum = $mysqli->prepare($sql_alum);
$stmt_alum->bind_param("i", $id_curso);
$stmt_alum->execute();
$res_alum = $stmt_alum->get_result();

// 4. Cargar notas parciales en memoria para acceso rápido
$notas_parciales = [];
if (!empty($rubros)) {
    $sql_np = "SELECT nr.id_matricula, nr.id_rubro, nr.calificacion_obtenida 
               FROM notas_rubros nr 
               JOIN matriculas m ON nr.id_matricula = m.id_matricula 
               WHERE m.id_curso_activo = ?";
    $stmt_np = $mysqli->prepare($sql_np);
    $stmt_np->bind_param("i", $id_curso);
    $stmt_np->execute();
    $res_np = $stmt_np->get_result();
    while($np = $res_np->fetch_assoc()){
        $notas_parciales[$np['id_matricula']][$np['id_rubro']] = $np['calificacion_obtenida'];
    }
    $stmt_np->close();
}

// Cargar FPDF
require('../includes/fpdf/fpdf.php');

class PDF extends FPDF
{
    function Header()
    {
        // Logo UNELA (Arriba)
        $logo_unela = __DIR__ . '/../imgs/logo_unela_color.png'; 
        if(file_exists($logo_unela)) {
            $x_logo = ($this->w / 2) - 33.75; // Ajustar para centrar el logo más grande
            $this->Image($logo_unela, $x_logo, 10, 67.5); // Ancho 50% más grande
        }
        $this->SetY(15); // Adjust Y to make space for the new text
        $this->SetFont('Arial','',10);
        $this->Cell(0,6, to_pdf_encoding('Universidad Evangélica de las Américas - Costa Rica'),0,1,'C');
        $this->Ln(5);
        
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10, to_pdf_encoding('Acta Detallada de Calificaciones'),0,1,'C');
        $this->Ln(5);
    }
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $texto_footer = to_pdf_encoding('Design and developed by renangalvan.net - San José, Costa Rica');
        $paginacion = to_pdf_encoding('Página ').$this->PageNo().'/{nb}';
        
        // Texto izquierda
        $this->Cell(0,10, $texto_footer, 0, 0, 'L');
        
        // Logo INBOX (Centrado en la misma línea)
        $logo_inbox = '../imgs/logo_inbox_color.png'; 
        if(file_exists($logo_inbox)) {
            // Posición Y ajustada para que quede en medio de la línea de texto
            // Posición X centrada
            $x_logo = ($this->w / 2) - 10;
            $this->Image($logo_inbox, $x_logo, $this->GetY() + 1, 20); 
        }

        // Paginación derecha
        $this->SetX(-40);
        $this->Cell(0,10, $paginacion, 0, 0, 'R');
    }
}

// Crear PDF Horizontal para que quepan los rubros
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L'); 

// Info Curso
$pdf->SetY(35);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6,'Curso:',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($curso['materia'] . " (" . $curso['codigo'] . ")"),0,0);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6,'Periodo:',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6, to_pdf_encoding($curso['periodo']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6,'Programa:',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($curso['nombre_programa']),0,0);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6,'Profesor:',0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6, to_pdf_encoding($curso['profesor']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Fecha Acta:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6, date('d/m/Y H:i'),0,1);
$pdf->Ln(5);

// --- Construcción Dinámica de la Tabla ---

// Definir anchos
$w_nro = 10;
$w_nombre = 50; // Reducido
$w_cedula = 25; // Nueva columna
$w_final = 15;
$w_condicion = 25;
$w_rubro = 25; // Ancho base por rubro

// Calcular si caben
$ancho_total_disponible = 270; // A4 Landscape (297) - Margenes
$espacio_rubros = $ancho_total_disponible - $w_nro - $w_nombre - $w_cedula - $w_final - $w_condicion;
$num_rubros = count($rubros);

if ($num_rubros > 0 && $espacio_rubros > 0) {
    $w_rubro = $espacio_rubros / $num_rubros;
} else {
    $w_rubro = 0;
}

// Cabecera
$pdf->SetFont('Arial','B',8);
$pdf->SetFillColor(230);
$pdf->Cell($w_nro, 10, 'No.', 1, 0, 'C', true);
$pdf->Cell($w_nombre, 10, 'Estudiante', 1, 0, 'C', true);
$pdf->Cell($w_cedula, 10, to_pdf_encoding('Cédula'), 1, 0, 'C', true);

if ($w_rubro > 0) {
    foreach ($rubros as $rubro) {
        $pdf->Cell($w_rubro, 10, to_pdf_encoding(substr($rubro['rubro'],0,10).' ('.$rubro['porcentaje'].'%)'), 1, 0, 'C', true);
    }
}

$pdf->Cell($w_final, 10, 'TOTAL', 1, 0, 'C', true);
$pdf->Cell($w_condicion, 10, to_pdf_encoding('Condición'), 1, 1, 'C', true);

// Cuerpo
$pdf->SetFont('Arial','',8);
$i = 1;
while($alum = $res_alum->fetch_assoc()){
    $id_mat = $alum['id_matricula'];
    
    $pdf->Cell($w_nro, 8, $i++, 1, 0, 'C');
    $pdf->Cell($w_nombre, 8, to_pdf_encoding(substr($alum['nombre'], 0, 25)), 1); 
    $pdf->Cell($w_cedula, 8, to_pdf_encoding($alum['cedula']), 1, 0, 'C');

    // Notas Parciales
    if ($w_rubro > 0) {
        foreach ($rubros as $rubro) {
            $val = $notas_parciales[$id_mat][$rubro['id_evaluacion']] ?? '-';
            $pdf->Cell($w_rubro, 8, $val, 1, 0, 'C');
        }
    }

    // Nota Final
    $nota_final = $alum['calificacion'] !== null ? number_format($alum['calificacion'], 2) : '-';
    
    // Negrita si aprueba
    if(is_numeric($nota_final) && $nota_final >= 70) $pdf->SetFont('Arial','B',8);
    $pdf->Cell($w_final, 8, $nota_final, 1, 0, 'C');
    $pdf->SetFont('Arial','',8); // Reset

    // Condición
    $condicion = '';
    if (is_numeric($nota_final)) {
        $condicion = $nota_final >= 70 ? 'Aprobado' : 'Reprobado';
    }
    $pdf->Cell($w_condicion, 8, to_pdf_encoding($condicion), 1, 1, 'C');
}

// Firmas
$pdf->Ln(30);
$pdf->Line(20, $pdf->GetY(), 90, $pdf->GetY());
$pdf->Line(180, $pdf->GetY(), 250, $pdf->GetY());
$pdf->SetFont('Arial','',9);
$pdf->Text(35, $pdf->GetY()+5, 'Firma del Profesor');
$pdf->Text(195, $pdf->GetY()+5, 'Sello de Registro');

ob_end_clean();

// Función para limpiar el nombre del archivo
function cleanFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $filename);
    $filename = preg_replace('/[\s\-]+/', '_', $filename);
    $filename = trim($filename, '_');
    return strtolower($filename);
}

// --- Lógica para guardar el archivo ---
$directorio_uploads = '../uploads/actas/';
if (!file_exists($directorio_uploads)) {
    mkdir($directorio_uploads, 0777, true);
}

// Crear nombre de archivo basado en el nombre del curso
$nombre_base = cleanFileName($curso['materia']);
$nombre_archivo_servidor = $nombre_base . '_curso_' . $id_curso . '.pdf';
$ruta_completa = $directorio_uploads . $nombre_archivo_servidor;

// Guardar el PDF en el servidor (sobrescribirá el anterior si existe)
$pdf->Output('F', $ruta_completa);

// Guardar ruta y contexto en la sesión y redirigir
$_SESSION['acta_contexto'] = [
    'id_curso' => $id_curso,
    'ruta_archivo' => $ruta_completa,
    'nombre_curso' => $curso['materia'] . " (" . $curso['codigo'] . ")",
    'nombre_programa' => $curso['nombre_programa'],
    'periodo' => $curso['periodo'],
    'nombre_profesor' => $curso['profesor']
];
header('Location: confirmar_envio_acta.php');
exit;
?>