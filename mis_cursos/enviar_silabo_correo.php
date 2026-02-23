<?php
ob_start();
ini_set('display_errors', 1); // Keep enabled for debugging this specific step
error_reporting(E_ALL);

session_start();
require_once "../includes/config.php";
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";
require_once "../includes/email_sender.php";
require('../includes/fpdf/fpdf.php');

// Helper functions
if (!function_exists('to_pdf_encoding')) {
    function to_pdf_encoding($txt) {
        if ($txt === null) return '';
        $txt = html_entity_decode($txt, ENT_QUOTES, 'UTF-8');
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
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

// Security check
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SERVER["REQUEST_METHOD"] !== "POST"){
    header("location: ../login.php");
    exit;
}

$id_plan = $_POST['id_plan'] ?? null;
$id_curso_volver = $_POST['id_curso'] ?? null;
$id_profesor = $_SESSION['id'];

if (!$id_plan) {
    die("Error: No se ha especificado un plan de estudios.");
}

// 1. Get Data
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

if(!$silabo) die("Sílabo no encontrado para generar el correo.");

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

// 2. PDF Class
class PDF extends FPDF {
    public $materia;
    public $codigo;
    var $B=0, $I=0, $U=0, $HREF='';
    protected $fontList = array(), $issetfont = false, $issetcolor = false;

    function Header() {
        if ($this->PageNo() == 1) {
            $logo_png = __DIR__ . '/../imgs/logo_unela_color.png';
            if(file_exists($logo_png)) {
                $x_logo = ($this->w / 2) - 37.5; // Ajustar para centrar el logo más grande
                $this->Image($logo_png, $x_logo, 10, 75); // Ancho 50% más grande
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
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10, to_pdf_encoding('Design and developed by renangalvan.net - San José, Costa Rica'), 0, 0, 'L');
        $this->Cell(0,10, to_pdf_encoding('Página ').$this->PageNo().'/{nb}', 0, 0, 'R');
    }
    function SectionTitle($label) {
        $this->SetFont('Arial','B',12);
        $this->SetFillColor(240,240,240);
        $this->Cell(0,8, to_pdf_encoding($label),0,1,'L',true);
        // $this->Ln(2); // Eliminado para reducir espacio
    }
    function SectionBody($txt) {
        $this->SetFont('Arial','',10);
        $this->WriteHTML(to_pdf_encoding($txt));
        $this->Ln(10);
    }
    function WriteHTML($html) {
        $html=strip_tags($html,"<b><i><u><a><p><br><strong><em>");
        $html=str_replace("\n",' ',$html);
        $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e) {
            if($i%2==0) { 
                if($this->HREF) $this->PutLink($this->HREF,$e);
                else $this->Write(5,stripslashes(txtentities($e)));
            } else { 
                if($e[0]=='/') $this->CloseTag(strtoupper(substr($e,1)));
                else { 
                    $a2=explode(' ',$e);
                    $tag=strtoupper(array_shift($a2));
                    $attr=array();
                    foreach($a2 as $v) if(preg_match('/([^=]*)="?([^"]*)"?/',$v,$a3)) $attr[strtoupper($a3[1])]=$a3[2];
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }
    function OpenTag($tag,$attr) {
        if($tag=='B'||$tag=='I'||$tag=='U') $this->SetStyle($tag,true);
        if($tag=='A') $this->HREF=$attr['HREF'];
            case 'P':
                $this->Ln(3);
                break;
    }
    function CloseTag($tag) {
        if($tag=='B'||$tag=='I'||$tag=='U') $this->SetStyle($tag,false);
        if($tag=='A') $this->HREF='';
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

// 3. Generate PDF content
$pdf = new PDF();
$pdf->materia = $silabo['materia'];
$pdf->codigo = $silabo['codigo'];
$pdf->AliasNbPages();
$pdf->AddPage();
// ... (Add all sections)
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
// Cronograma Table
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
// Evaluation Table
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

// 4. Save PDF to a temporary file
$directorio_uploads = '../uploads/silabos_temp/';
if (!file_exists($directorio_uploads)) {
    mkdir($directorio_uploads, 0777, true);
}
$nombre_base = cleanFileName($silabo['materia']);
$nombre_archivo_servidor = 'silabo_' . $nombre_base . '_plan_' . $id_plan . '_prof_' . $id_profesor . '_' . time() . '.pdf';
$ruta_completa = $directorio_uploads . $nombre_archivo_servidor;
$pdf->Output('F', $ruta_completa);

// 5. Send Email
$message = '';
$error = '';
$to = 'dti@unela.ac.cr';
$subject = "Sílabo de Curso: " . $silabo['materia'];
$body = "
    <p>Saludos,</p>
    <p>Se adjunta el sílabo generado desde el sistema Inbox con los siguientes detalles:</p>
    <ul>
        <li><strong>Programa:</strong> " . htmlspecialchars($silabo['nombre_programa']) . "</li>
        <li><strong>Curso:</strong> " . htmlspecialchars($silabo['materia']) . "</li>
        <li><strong>Profesor:</strong> " . htmlspecialchars($silabo['profesor']) . "</li>
    </ul>
    <p>El documento PDF está adjunto en este correo.</p>
    <p><strong>Por favor, no responda a este correo.</strong></p>";

if (sendEmailWithAttachment($to, $subject, $body, $ruta_completa)) {
    $message = "El correo con el sílabo ha sido enviado exitosamente a DTI.";
} else {
    $error = "Hubo un problema al enviar el correo. Por favor, contacte a soporte.";
}

// 6. Delete temporary file
if (file_exists($ruta_completa)) {
    unlink($ruta_completa);
}

ob_end_clean();

// 7. Display result page
$page_title = 'Resultado del Envío';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>
<div class="container mt-4">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<?php echo $message ? "¡Envío Exitoso!" : "Error en el Envío"; ?>',
                text: '<?php echo $message ? addslashes($message) : addslashes($error); ?>',
                icon: '<?php echo $message ? "success" : "error"; ?>',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>

    <h3>Proceso Finalizado</h3>
    <p>Puedes volver a la página anterior o a tu lista de cursos.</p>
    <hr>
    <a href="index.php" class="btn btn-primary">Volver a Mis Cursos</a>
    <?php if ($id_plan): ?>
        <a href="gestionar_silabo.php?id_plan=<?php echo $id_plan; ?>&id_curso=<?php echo $id_curso_volver; ?>" class="btn btn-secondary">Volver al Sílabo</a>
    <?php endif; ?>
</div>
<?php require_once '../includes/footer.php'; ?>
