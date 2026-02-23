<?php
// 1. Evitar cualquier salida previa y errores visuales
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once "../includes/db_connect.php";

// 2. Funcin de codificacin moderna para FPDF
if (!function_exists('to_pdf_encoding')) {
    function to_pdf_encoding($txt) {
        if ($txt === null) return '';
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        $txt = str_replace(['<br>', '<br />', '<p>', '</p>'], ["\n", "\n", "\n\n", ""], $txt);
        $txt = strip_tags($txt);
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }
}

if(!isset($_SESSION["loggedin"]) || !isset($_GET['id_plan'])){
    die("Acceso denegado.");
}

$id_plan = $_GET['id_plan'];
$id_profesor = $_SESSION['id'];
$id_curso = $_GET['id_curso'] ?? null; // Capturar id_curso

// Obtener Datos
$sql = "SELECT s.*, pe.materia, pe.codigo, p.nombre_programa, u.nombre as profesor 
        FROM silabos s
        JOIN plan_estudios pe ON s.id_plan = pe.id_plan
        JOIN programas p ON pe.id_programa = p.id_programa
        JOIN usuarios u ON s.id_profesor = u.id
        WHERE s.id_plan = ? AND s.id_profesor = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $id_plan, $id_profesor);
$stmt->execute();
$res = $stmt->get_result();
$silabo = $res->fetch_assoc();

if(!$silabo) die("Slabo no encontrado.");

// Obtener Evaluacin
$rubros = [];
$sql_r = "SELECT * FROM silabo_evaluacion WHERE id_silabo = ?";
$stmt_r = $mysqli->prepare($sql_r);
$stmt_r->bind_param("i", $silabo['id_silabo']);
$stmt_r->execute();
$res_r = $stmt_r->get_result();
while($r = $res_r->fetch_assoc()) $rubros[] = $r;

// Obtener Cronograma
$cronograma = [];
$sql_c = "SELECT * FROM silabo_cronograma WHERE id_silabo = ? ORDER BY id_cronograma ASC";
$stmt_c = $mysqli->prepare($sql_c);
$stmt_c->bind_param("i", $silabo['id_silabo']);
$stmt_c->execute();
$res_c = $stmt_c->get_result();
while($r = $res_c->fetch_assoc()) $cronograma[] = $r;

require('../includes/fpdf/fpdf.php');

class PDF extends FPDF
{
    public $materia;
    public $codigo;

    function Header()
    {
        if ($this->PageNo() == 1) {
            // Logo Centrado
            $logo_jpg = __DIR__ . '/../imgs/logo_unela.jpg'; 
            $logo_png = __DIR__ . '/../imgs/logo_unela_color.png';
            $logo_usar = file_exists($logo_jpg) ? $logo_jpg : (file_exists($logo_png) ? $logo_png : null);

            if($logo_usar) {
                $x_logo = ($this->w / 2) - 25; 
                try { $this->Image($logo_usar, $x_logo, 10, 50); } catch(Exception $e) {}
            }

            // Ttulos Centrados
            $this->SetY(65); // Debajo del logo
            
            $this->SetFont('Arial','',12);
            $this->Cell(0,6, to_pdf_encoding('PROGRAMA DE CURSO'),0,1,'C');
            
            $this->SetFont('Arial','B',16);
            $this->MultiCell(0,10, to_pdf_encoding($this->materia), 0, 'C');
            
            $this->SetFont('Arial','',10);
            $this->Cell(0,6, to_pdf_encoding("CDIGO: " . $this->codigo),0,1,'C');
            
            $this->Ln(5); 
            $this->Line(10, $this->GetY(), 200, $this->GetY()); 
            $this->Ln(5);
        }
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $texto_footer = to_pdf_encoding('Design and developed by renangalvan.net - San Jos, Costa Rica');
        $paginacion = to_pdf_encoding('Pgina ').$this->PageNo().'/{nb}';
        
        $this->Cell(0,10, $texto_footer, 0, 0, 'L');
        
        $logo_inbox = __DIR__ . '/../imgs/logo_inbox_color.png'; 
        if(file_exists($logo_inbox)) {
            $x_logo = ($this->w / 2) - 10;
            try { $this->Image($logo_inbox, $x_logo, $this->GetY() + 1, 20); } catch(Exception $e) {}
        }

        $this->SetX(-40);
        $this->Cell(0,10, $paginacion, 0, 0, 'R');
    }
    
    function SectionTitle($label)
    {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(240,240,240);
        $this->Cell(0,8, to_pdf_encoding($label),0,1,'L',true);
        $this->Ln(2);
    }

    function SectionBody($txt)
    {
        $this->SetFont('Arial','',10);
        $this->MultiCell(0,5, to_pdf_encoding($txt));
        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->materia = $silabo['materia'];
$pdf->codigo = $silabo['codigo'];

$pdf->AliasNbPages();
$pdf->AddPage();

// Info Profesor (Debajo de cabecera)
$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Profesor:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($silabo['profesor']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Modalidad:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($silabo['modalidad']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Horario:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($silabo['horario']),0,1);
$pdf->Ln(8);

// Secciones
$pdf->SectionTitle('1. Descripcin del Curso');
$pdf->SectionBody($silabo['descripcion']);

$pdf->SectionTitle('2. Objetivo General');
$pdf->SectionBody($silabo['objetivo_general']);

$pdf->SectionTitle('3. Objetivos Especficos');
$pdf->SectionBody($silabo['objetivos_especificos']);

$pdf->SectionTitle('4. Metodologa');
$pdf->SectionBody($silabo['metodologia']);

$pdf->SectionTitle('5. Contenidos');
$pdf->SectionBody($silabo['contenidos']);

// Cronograma Tabla
$pdf->SectionTitle('6. Cronograma Tentativo');
if (!empty($cronograma)) {
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(230);
    // Ancho total 190
    $w_sem = 20; $w_fec = 30; $w_act = 140;

    $pdf->Cell($w_sem,8,'Semana',1,0,'C',true);
    $pdf->Cell($w_fec,8,'Fechas',1,0,'C',true);
    $pdf->Cell($w_act,8,'Actividad',1,1,'L',true); 

    $pdf->SetFont('Arial','',9);
    
    foreach($cronograma as $c) {
        $cell_width = $w_act;
        $cell_height = 6;
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        
        $pdf->Cell($w_sem, $cell_height, to_pdf_encoding($c['semana']), 0, 0, 'C');
        
        $f_mostrar = $c['fecha'];
        if(strtotime($f_mostrar)) $f_mostrar = date("d/m/Y", strtotime($f_mostrar));
        
        $pdf->Cell($w_fec, $cell_height, to_pdf_encoding($f_mostrar), 0, 0, 'C');
        
        $pdf->SetXY($x + $w_sem + $w_fec, $y); 
        $pdf->MultiCell($cell_width, $cell_height, to_pdf_encoding($c['actividad']), 0, 'L');
        
        $h_diff = $pdf->GetY() - $y;
        $pdf->Rect($x, $y, $w_sem, $h_diff); 
        $pdf->Rect($x + $w_sem, $y, $w_fec, $h_diff);
        $pdf->Rect($x + $w_sem + $w_fec, $y, $cell_width, $h_diff);
        
        $pdf->SetXY($x, $y + $h_diff);
        
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            if ($pdf->PageNo() == 1) $pdf->SetY(75); // Si por alguna razon vuelve a pag 1 (improbable)
            else $pdf->SetY(20); // Paginas siguientes empiezan arriba
        }
    }
    $pdf->Ln(5);
} else {
    $pdf->SectionBody('No definido.');
}

// Evaluacin Tabla
$pdf->SectionTitle('7. Sistema de Evaluacin');
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230);
$w_rubro = 160; $w_porc = 30;

$pdf->Cell($w_rubro,8,'Rubro',1,0,'L',true);
$pdf->Cell($w_porc,8,'Porcentaje',1,1,'C',true);

$pdf->SetFont('Arial','',10);
$total = 0;
foreach($rubros as $r){
    $pdf->Cell($w_rubro,8, to_pdf_encoding($r['rubro']),1);
    $pdf->Cell($w_porc,8, $r['porcentaje'] . '%',1,1,'C');
    $total += $r['porcentaje'];
}
$pdf->SetFont('Arial','B',10);
$pdf->Cell($w_rubro,8,'TOTAL',1,0,'R');
$pdf->Cell($w_porc,8,$total.'%',1,1,'C');
$pdf->Ln(5);

$pdf->SectionTitle('8. Bibliografa');
$pdf->SectionBody($silabo['bibliografia']);

ob_end_clean();

// Función para limpiar el nombre del archivo
function cleanFileName($filename) {
    // Reemplaza caracteres no alfanuméricos (excepto espacio, guiones, puntos) por nada
    $filename = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $filename);
    // Reemplaza espacios y guiones múltiples por un solo guion bajo
    $filename = preg_replace('/[\s\-]+/', '_', $filename);
    // Limpia guiones bajos al inicio/final
    $filename = trim($filename, '_');
    return strtolower($filename);
}

// --- Lógica para guardar el archivo ---
$directorio_uploads = '../uploads/silabos/';
if (!file_exists($directorio_uploads)) {
    mkdir($directorio_uploads, 0777, true);
}

// Crear nombre de archivo basado en el nombre del curso
$nombre_base = cleanFileName($silabo['materia']);
$nombre_archivo_servidor = $nombre_base . '_plan_' . $id_plan . '_prof_' . $id_profesor . '.pdf';
$ruta_completa = $directorio_uploads . $nombre_archivo_servidor;

// Nombre para la descarga
$nombre_archivo_descarga = $nombre_base . '.pdf';

// Guardar el PDF en el servidor (sobrescribirá el anterior si existe)
$pdf->Output('F', $ruta_completa);

// Guardar ruta y contexto en la sesión y redirigir
$_SESSION['silabo_contexto'] = [
    'ruta_archivo' => $ruta_completa,
    'nombre_curso' => $silabo['materia'] . " (" . $silabo['codigo'] . ")",
    'nombre_programa' => $silabo['nombre_programa'],
    'periodo' => $silabo['periodo'] ?? 'No especificado', // El sílabo no parece tener período, se puede añadir a la consulta si es necesario
    'nombre_profesor' => $silabo['profesor'],
    'id_curso' => $id_curso // Añadir id_curso al contexto
];
header('Location: confirmar_envio_silabo.php');
exit;
?>