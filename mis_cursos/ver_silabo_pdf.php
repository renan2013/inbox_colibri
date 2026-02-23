<?php
// 1. Activado para depuración
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../includes/db_connect.php";

if (!function_exists('to_pdf_encoding')) {
    function to_pdf_encoding($txt) {
        if ($txt === null) return '';
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }
}

if(!isset($_SESSION["loggedin"]) || !isset($_GET['id_plan'])){
    die("Acceso denegado.");
}

$id_plan = $_GET['id_plan'];
$id_profesor = $_SESSION['id'];

// Obtener Datos
$sql = "SELECT s.*, pe.materia, pe.codigo, pe.creditos, p.nombre_programa, u.nombre as profesor 
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

// Obtener Evaluación y Cronograma
$rubros = [];
$sql_r = "SELECT * FROM silabo_evaluacion WHERE id_silabo = ?";
$stmt_r = $mysqli->prepare($sql_r);
$stmt_r->bind_param("i", $silabo['id_silabo']);
$stmt_r->execute();
$res_r = $stmt_r->get_result();
while($r = $res_r->fetch_assoc()) $rubros[] = $r;

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
    protected $fontList = array();
    protected $issetfont = false;
    protected $issetcolor = false;

    function Header()
    {
        if ($this->PageNo() == 1) {
            $logo_jpg = __DIR__ . '/../imgs/logo_unela.jpg'; 
            $logo_png = __DIR__ . '/../imgs/logo_unela_color.png';
            $logo_usar = file_exists($logo_jpg) ? $logo_jpg : (file_exists($logo_png) ? $logo_png : null);

                                    if($logo_usar) {
                                        $x_logo = ($this->w / 2) - 37.5; // Ajustar para centrar el logo más grande
                                        try { $this->Image($logo_usar, $x_logo, 10, 75); } catch(Exception $e) {} // Ancho 50% más grande
                                    }
                        $this->SetY(45); // Set Y position after logo

                        $this->SetFont('Arial','',10);

                        $this->Cell(0,6, to_pdf_encoding('Universidad Evangélica de las Américas - Costa Rica'),0,1,'C');
$this->Ln(5);

                        

                        $this->SetFont('Arial','',12);

                        $this->Cell(0,6, to_pdf_encoding('PROGRAMA DE CURSO'),0,1,'C');

                        

                        $this->SetFont('Arial','B',16);

                        $this->MultiCell(0,10, to_pdf_encoding($this->materia), 0, 'C');

                        

                        $this->SetFont('Arial','',10);

                        $this->Cell(0,6, to_pdf_encoding("CÓDIGO: " . $this->codigo),0,1,'C');

                        

                        $this->Ln(5); 

                        $this->Line(10, $this->GetY(), 200, $this->GetY()); 

                        $this->Ln(5);
        }
    }

    function Footer()
    {
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
    }
    
    function SectionTitle($label)
    {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(240,240,240);
        $this->Cell(0,8, to_pdf_encoding($label),0,1,'L',true);
        // $this->Ln(2); // Eliminado para reducir espacio
    }

    function SectionBody($txt)
    {
        $this->SetFont('Arial','',10);
        $this->WriteHTML(to_pdf_encoding($txt));
        $this->Ln(10);
    }

    function WriteHTML($html)
    {
        $html=strip_tags($html,"<b><i><u><a><img><p><br><strong><em><font><tr><blockquote>");
        $html=str_replace("\n",' ',$html);
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                if($this->HREF)
                    $this->PutLink($this->HREF,$e);
                else
                    $this->Write(5,stripslashes(txtentities($e)));
            }
            else
            {
                if($e[0]=='/')
                    $this->CloseTag(strtoupper(substr($e,1)));
                else
                {
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v)
                    {
                        if(preg_match('/([^=]*)="?([^"]*)"?/',$v,$a3))
                            $attr[strtoupper($a3[1])]=$a3[2];
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function OpenTag($tag,$attr)
    {
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
                if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT'])))
                {
                    if(!isset($attr['WIDTH'])) $attr['WIDTH'] = 0;
                    if(!isset($attr['HEIGHT'])) $attr['HEIGHT'] = 0;
                    $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), px2mm($attr['WIDTH']), px2mm($attr['HEIGHT']));
                }
                break;
            case 'TR':
            case 'BLOCKQUOTE':
            case 'BR':
                $this->Ln(5);
                break;
            case 'P':
                $this->Ln(5);
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
        if($tag=='STRONG') $tag='B';
        if($tag=='EM') $tag='I';
        if($tag=='B' || $tag=='I' || $tag=='U') $this->SetStyle($tag,false);
        if($tag=='A') $this->HREF='';
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

    function SetStyle($tag,$enable) {
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B','I','U') as $s) if($this->$s>0) $style.=$s;
        $this->SetFont('',$style);
    }

    function PutLink($URL,$txt) {
        $this->SetTextColor(0,0,255);
        $this->SetStyle('U',true);
        $this->Write(5,$txt,$URL);
        $this->SetStyle('U',false);
        $this->SetTextColor(0);
    }

    // Tabla con MultiCell
    var $widths;
    var $aligns;

    function SetWidths($w) {
        $this->widths=$w;
    }

    function SetAligns($a) {
        $this->aligns=$a;
    }

    function Row($data) {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++) {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x=$this->GetX();
            $y=$this->GetY();
            $this->Rect($x,$y,$w,$h);
            $this->MultiCell($w,5,$data[$i],0,$a);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt) {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb) {
            $c=$s[$i];
            if($c=="\n") {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
}


function px2mm($px){ return $px*25.4/72; }
function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}
function hex2dec($couleur = "#000000"){
    $R = substr($couleur, 1, 2); $rouge = hexdec($R);
    $V = substr($couleur, 3, 2); $vert = hexdec($V);
    $B = substr($couleur, 5, 2); $bleu = hexdec($B);
    return array('R'=>$rouge,'G'=>$vert,'B'=>$bleu);
}
function cleanFileName($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $filename);
    $filename = preg_replace('/[\s\-]+/', '_', $filename);
    return strtolower(trim($filename, '_'));
}

$pdf = new PDF();
$pdf->materia = $silabo['materia'];
$pdf->codigo = $silabo['codigo'];

$pdf->AliasNbPages();
$pdf->AddPage();

// Info Profesor
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

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Carrera:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($silabo['nombre_programa']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Créditos:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, to_pdf_encoding($silabo['creditos']),0,1);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(25,6, to_pdf_encoding('Fecha Gen:'),0,0);
$pdf->SetFont('Arial','',10);
$pdf->Cell(100,6, date('d/m/Y H:i'),0,1);

$pdf->Ln(8);

// Secciones
$pdf->SectionTitle('1. Descripción del Curso');
$pdf->SectionBody($silabo['descripcion']);

$pdf->SectionTitle('2. Objetivo General');
$pdf->SectionBody($silabo['objetivo_general']);

$pdf->SectionTitle('3. Objetivos Específicos');
$pdf->SectionBody($silabo['objetivos_especificos']);

$pdf->SectionTitle('4. Metodología');
$pdf->SectionBody($silabo['metodologia']);

$pdf->SectionTitle('5. Contenidos');
$pdf->SectionBody($silabo['contenidos']);

// Cronograma Tabla
$pdf->SectionTitle('6. Cronograma Tentativo');
if (!empty($cronograma)) {
    $pdf->SetFont('Arial','B',9);
    $pdf->SetFillColor(230);
    $w_sem=20; $w_fec=30; $w_act=140;
    
    // Header
    $pdf->SetWidths(array($w_sem, $w_fec, $w_act));
    $pdf->SetAligns(array('C', 'C', 'L'));
    $pdf->Row(array('Semana', 'Fechas', 'Actividad'));
    
    $pdf->SetFont('Arial','',9);
    foreach($cronograma as $c) {
        $f_mostrar = $c['fecha'] ? date("d/m/Y", strtotime($c['fecha'])) : '';
        $pdf->Row(array(
            to_pdf_encoding($c['semana']),
            to_pdf_encoding($f_mostrar),
            to_pdf_encoding($c['actividad'])
        ));
    }
    $pdf->Ln(5);
} else {
    $pdf->SectionBody('No definido.');
}

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

$pdf->SectionTitle('8. Bibliografía');
$pdf->SectionBody($silabo['bibliografia']);

ob_end_clean();

$nombre_archivo_salida = 'silabo_' . cleanFileName($silabo['materia']) . '.pdf';
$pdf->Output('I', $nombre_archivo_salida);
exit;
?>
