<?php
// 1. Evitar cualquier salida previa y errores visuales
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db_connect.php";

// 2. Función de codificación moderna para FPDF


if(!isset($_SESSION["loggedin"]) || !isset($_GET['id_plan'])){
    die("Acceso denegado.");
}

$id_plan = $_GET['id_plan'];
$id_profesor = $_SESSION['id'];

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

if(!$silabo) die("Sílabo no encontrado.");

// Obtener Evaluación
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
    var $B=0;
    var $I=0;
    var $U=0;
    var $HREF='';

    function Header()
    {
        /*
        if ($this->PageNo() == 1) {
            // Logo Centrado
            $logo_jpg = __DIR__ . '/../imgs/logo_unela.jpg'; 
            $logo_png = __DIR__ . '/../imgs/logo_unela_color.png';
            $logo_usar = file_exists($logo_jpg) ? $logo_jpg : (file_exists($logo_png) ? $logo_png : null);

            if($logo_usar) {
                $x_logo = ($this->w / 2) - 25; 
                try { $this->Image($logo_usar, $x_logo, 10, 50); } catch(Exception $e) {}
            }

            // Títulos Centrados
            $this->SetY(45); // Debajo del logo (ajustado para estar más cerca del logo)
            
            $this->SetFont('Arial','',12);
            $this->Cell(0,6, to_pdf_encoding('PROGRAMA DE CURSO'),0,1,'C');
            
            $this->SetFont('Arial','B',16);
            $this->MultiCell(0,10, to_pdf_encoding($this->materia), 0, 'C');
            
            $this->SetFont('Arial','',10);
            $this->Cell(0,6, to_pdf_encoding("CóDIGO: " . $this->codigo),0,1,'C');
            
            $this->Ln(5); 
            $this->Line(10, $this->GetY(), 200, $this->GetY()); 
            $this->Ln(5);
        }
        */
    }

    function Footer()
    {
        /*
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $texto_footer = to_pdf_encoding('Design and developed by renangalvan.net - San José, Costa Rica');
        $paginacion = to_pdf_encoding('Página ').$this->PageNo().'/{nb}';
        
        $this->Cell(0,10, $texto_footer, 0, 0, 'L');
        
        $logo_inbox = __DIR__ . '/../imgs/logo_inbox_color.png'; 
        if(file_exists($logo_inbox)) {
            $x_logo = ($this->w / 2) - 10;
            try { $this->Image($logo_inbox, $x_logo, $this->GetY() + 1, 20); } catch(Exception $e) {}
        }

        $this->SetX(-40);
        $this->Cell(0,10, $paginacion, 0, 0, 'R');
        */
    }
    
    function SectionTitle($label)
    {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(240,240,240);
        $this->Cell(0,8, to_pdf_encoding($label),0,1,'L',true);
        $this->Ln(2); // Reintroducido para añadir un pequeño espacio
    }

    function SectionBody($txt)
    {
        $this->SetFont('Arial','',10);
        $this->WriteHTML(to_pdf_encoding($txt));
        $this->Ln(5);
    }

    protected $HREF = '';
    protected $fontList = array();
    protected $issetfont = false;
    protected $issetcolor = false;

    function WriteHTML($html)
    {
        $html=strip_tags($html,"<b><i><u><a><img><p><br><strong><em><font><tr><blockquote>"); //supprime tous les tags sauf ceux reconnus
        $html=str_replace("\n",' ',$html); //remplace les fins de lignes par des espaces
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //eclate la chaine avec les balises
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Texte
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,stripslashes(txtentities($e)));
            }
            else
            {
                //Balise
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    //Extraction des attributs
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                            $attr[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function OpenTag($tag,$attr)
    {
        //Balise ouvrante
        switch($tag){
            case 'STRONG':
                $this->SetStyle('B',true);
                break;
            case 'EM':
                $this->SetStyle('I',true);
                break;
            case 'B':
            case 'I':
            case 'U':
                $this->SetStyle($tag,true);
                break;
            case 'A':
                $this->HREF=$attr['HREF'];
                break;
            case 'IMG':
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
                    if(!isset($attr['WIDTH']))
                        $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT']))
                        $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(10);
                break;
            case 'FONT':
                if (isset($attr['COLOR']) && $attr['COLOR']!='') {
                    $coul=hex2dec($attr['COLOR']);
                    $this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
                    $this->issetcolor=true;
                }
                if (isset($attr['FACE']) && in_array(strtolower($attr['FACE']), $this->fontlist)) {
                    $this->SetFont(strtolower($attr['FACE']));
                    $this->issetfont=true;
                }
                break;
        }
    }

    function CloseTag($tag)
    {
        //Balise fermante
        if($tag=='STRONG')
            $tag='B';
        if($tag=='EM')
            $tag='I';
        if($tag=='B' || $tag=='I' || $tag=='U')
            $this->SetStyle($tag,false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='FONT'){
            if ($this->issetcolor==true) {
                $this->SetTextColor(0);
            }
            if ($this->issetfont) {
                $this->SetFont('arial');
                $this->issetfont=false;
            }
        }
    }

    function SetStyle($tag,$enable)
    {
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt)
    {
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }
}

function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2);
    $rouge = hexdec($R);
    $V = substr($couleur, 3, 2);
    $vert = hexdec($V);
    $B = substr($couleur, 5, 2);
    $bleu = hexdec($B);
    $tbl_couleur = array();
    $tbl_couleur['R']=$rouge;
    $tbl_couleur['G']=$vert;
    $tbl_couleur['B']=$bleu;
    return $tbl_couleur;
}


$pdf = new PDF();
$pdf->materia = $silabo['materia'];
$pdf->codigo = $silabo['codigo'];

$pdf->AliasNbPages();
$pdf->AddPage();
error_log("PDF page created.");

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
error_log("General info section rendered.");

// Secciones
$pdf->SectionTitle('1. Descripción del Curso');
$pdf->SectionBody("Texto de prueba.");
error_log("Descripción del Curso section rendered.");

$pdf->SectionTitle('2. Objetivo General');
$pdf->SectionBody("Texto de prueba.");
error_log("Objetivo General section rendered.");

$pdf->SectionTitle('3. Objetivos Específicos');
$pdf->SectionBody("Texto de prueba.");
error_log("Objetivos Específicos section rendered.");

$pdf->SectionTitle('4. Metodología');
$pdf->SectionBody("Texto de prueba.");
error_log("Metodología section rendered.");

$pdf->SectionTitle('5. Contenidos');
$pdf->SectionBody("Texto de prueba.");
error_log("Contenidos section rendered.");

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
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $text = to_pdf_encoding($c['actividad']);
        $char_width = 75;
        $lines = explode("\n", $text);
        $nb = 0;
        foreach($lines as $line){
            $nb = max($nb, ceil($pdf->GetStringWidth($line) / $char_width));
        }
        $h = 6 * $nb;

        $pdf->Cell($w_sem, $h, to_pdf_encoding($c['semana']), 1, 0, 'C');
        
        $f_mostrar = $c['fecha'];
        if(strtotime($f_mostrar)) $f_mostrar = date("d/m/Y", strtotime($f_mostrar));
        
        $pdf->Cell($w_fec, $h, to_pdf_encoding($f_mostrar), 1, 0, 'C');
        
        $pdf->MultiCell($w_act, 6, $text, 1, 'L');
        
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
            if ($pdf->PageNo() == 1) $pdf->SetY(75);
            else $pdf->SetY(20);
        }
    }
    $pdf->Ln(5);
} else {
    $pdf->SectionBody('No definido.');
}
error_log("Cronograma section rendered.");

// Evaluación Tabla
$pdf->SectionTitle('7. Sistema de Evaluación');
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230);
$w_rubro = 160; $w_porc = 30;

$pdf->Cell($w_rubro,8,'Rubro',1,0,'L',true);
$pdf->Cell($w_porc,8,'Porcentaje',1,1,'C');

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
error_log("Evaluación section rendered.");

$pdf->SectionTitle('8. Bibliografía');
$pdf->SectionBody("Texto de prueba.");
error_log("Bibliografía section rendered.");

ob_end_clean();
error_log("PDF generation complete, starting output.");

// Función para limpiar el nombre del archivo
function cleanFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $filename);
    $filename = preg_replace('/[\s\-]+/', '_', $filename);
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
$nombre_archivo_servidor = 'silabo_' . $nombre_base . '_plan_' . $id_plan . '_prof_' . $id_profesor . '.pdf';
$ruta_completa = $directorio_uploads . $nombre_archivo_servidor;

// Guardar el PDF en el servidor (sobrescribirá el anterior si existe)
$pdf->Output('F', $ruta_completa);

// Guardar ruta y contexto en la sesión y redirigir
$_SESSION['silabo_contexto'] = [
    'ruta_archivo' => $ruta_completa,
    'nombre_curso' => $silabo['materia'] . " (" . $silabo['codigo'] . ")",
    'nombre_programa' => $silabo['nombre_programa'],
    'periodo' => $silabo['horario'], // Usando horario como sustituto si periodo no está
    'nombre_profesor' => $silabo['profesor']
];
header('Location: confirmar_envio_silabo.php');
exit;
?>