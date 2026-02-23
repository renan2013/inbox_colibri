<?php
// 1. Evitar cualquier salida previa
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

// 2. Función de codificación moderna para FPDF
if (!function_exists('to_pdf_encoding')) {
    function to_pdf_encoding($txt) {
        if ($txt === null) return '';
        // mb_convert_encoding reemplaza al obsoleto utf8_decode
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }
}

// 3. Proteger acceso
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    die("Acceso denegado.");
}

// 4. Cargar FPDF
$fpdf_path = '../includes/fpdf/fpdf.php';
if (file_exists($fpdf_path)) {
    require($fpdf_path);
} else {
    die("Error: Libreria FPDF no encontrada.");
}

class PDF extends FPDF
{
    function Header()
    {
        // Cabecera limpia sin logo
    }
    function Footer()
    {
        $this->SetY(-15);
        
        // Logo en el pie de página (Centrado)
        $logo = '../imgs/logo_inbox_color.png'; 
        if(file_exists($logo)) {
            // Calcular posición X para centrar una imagen de 30mm de ancho
            // Ancho página (L=297, P=210) / 2 - 15
            $x_logo = ($this->w / 2) - 15;
            $this->Image($logo, $x_logo, $this->GetY() - 2, 30); 
        }

        $this->SetFont('Arial','I',8);
        $texto_footer = to_pdf_encoding('Design and developed by renangalvan.net - San José, Costa Rica');
        $paginacion = to_pdf_encoding('Página ').$this->PageNo().'/{nb}';
        
        $this->Cell(0,10, $texto_footer, 0, 0, 'L');
        $this->SetX(-40);
        $this->Cell(0,10, $paginacion, 0, 0, 'R');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$tipo = $_GET['tipo'] ?? '';

switch ($tipo) {
    case 'gantt':
        $pdf->AddPage('L');
        $anio = isset($_GET['anio']) && is_numeric($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
        $eti_id = isset($_GET['etiqueta']) && is_numeric($_GET['etiqueta']) ? (int)$_GET['etiqueta'] : 0;
        $nom_eti = "General";

        if($eti_id > 0) {
            $st = $mysqli->prepare("SELECT nombre FROM etiquetas WHERE id = ?");
            $st->bind_param("i", $eti_id);
            $st->execute();
            $res = $st->get_result();
            if($r = $res->fetch_assoc()) $nom_eti = $r['nombre'];
            $st->close();
        }

        // Título Inicial
        $pdf->SetY(20); // Margen inicial reducido
        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,10, to_pdf_encoding('Cronograma de Tareas'),0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,8, to_pdf_encoding("Reporte: $nom_eti - Año: $anio"),0,1,'C');
        $pdf->Ln(5);

        // Consulta SQL
        $sql = "SELECT t.titulo, t.fecha_creacion, t.fecha_vencimiento, t.estado, t.prioridad,
                       GROUP_CONCAT(DISTINCT u.nombre SEPARATOR ', ') AS asignados
                FROM tareas t 
                LEFT JOIN tarea_asignaciones ta ON t.id = ta.id_tarea
                LEFT JOIN usuarios u ON ta.id_usuario = u.id";
        
        $where = ["YEAR(t.fecha_creacion) = ?"];
        $types = "i";
        $params = [$anio];

        if($eti_id > 0){
            $sql .= " JOIN tarea_etiquetas te ON t.id = te.id_tarea";
            $where[] = "te.id_etiqueta = ?";
            $types .= "i";
            $params[] = $eti_id;
        }
        $sql .= " WHERE " . implode(" AND ", $where) . " GROUP BY t.id ORDER BY t.fecha_creacion ASC";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tareas = [];
        $min_d = null; $max_d = null;

        while($row = $result->fetch_assoc()){
            $s = new DateTime($row['fecha_creacion']);
            $e = new DateTime($row['fecha_vencimiento']);
            if($row['fecha_vencimiento'] == '0000-00-00' || $e <= $s) {
                $e = clone $s; $e->modify('+1 day');
            }
            if ($min_d === null || $s < $min_d) $min_d = clone $s;
            if ($max_d === null || $e > $max_d) $max_d = clone $e;

            $tareas[] = [
                'titulo' => substr($row['titulo'], 0, 35),
                'start' => $s, 'end' => $e, 'estado' => $row['estado'],
                'asignados' => $row['asignados'], 'prioridad' => $row['prioridad']
            ];
        }
        $stmt->close();

        if (empty($tareas)) {
            $pdf->Cell(0,10,'No hay tareas.',0,1,'C');
        } else {
            if ($min_d == $max_d) $max_d->modify('+1 month');
            
            // Configuración Gráfica
            $m_left = 65; // Margen izquierdo un poco más grande para el texto
            $g_width = 215;
            $t_days = $max_d->diff($min_d)->days + 5;
            $px_d = $g_width / $t_days;
            $y_start_grid = 45; // Empezamos antes
            $y_end_grid = 175;  // Altura máxima

            // Función anónima para dibujar la rejilla
            $dibujar_rejilla = function($pdf, $min, $max, $ml, $gw, $px, $y_start, $y_end) {
                $pdf->SetFont('Arial','B',8);
                $curr = clone $min;
                while ($curr <= $max) {
                    $d_from = $curr->diff($min)->days;
                    $x = $ml + ($d_from * $px);
                    // Línea vertical
                    $pdf->SetDrawColor(220);
                    $pdf->Line($x, $y_start, $x, $y_end);
                    // Nombre mes
                    $pdf->SetDrawColor(0);
                    if ($x < ($ml + $gw - 10)) $pdf->Text($x+1, $y_start-2, $curr->format('M'));
                    $curr->modify('+1 month');
                }
                // Línea base cabecera
                $pdf->Line($ml, $y_start, $ml + $gw, $y_start);
            };

            // Dibujar rejilla inicial
            $dibujar_rejilla($pdf, $min_d, $max_d, $m_left, $g_width, $px_d, $y_start_grid, $y_end_grid);

            // Bucle de tareas
            $y = $y_start_grid + 5;
            $tareas_tabla = [];
            
            foreach ($tareas as $idx => $t) {
                $t['nro'] = $idx + 1;
                $tareas_tabla[] = $t;

                // Salto de página si llegamos al final del área de rejilla
                if ($y > ($y_end_grid - 5)) { 
                    $pdf->AddPage('L'); 
                    // Redibujar rejilla en nueva página
                    $dibujar_rejilla($pdf, $min_d, $max_d, $m_left, $g_width, $px_d, $y_start_grid, $y_end_grid);
                    $y = $y_start_grid + 5; // Reiniciar Y
                }

                // Nombre Tarea (Alineado Izquierda)
                $pdf->SetFont('Arial','',7);
                $pdf->SetXY(5, $y);
                $label_tarea = $t['nro'] . ". " . $t['titulo'];
                $pdf->Cell($m_left-7, 5, to_pdf_encoding($label_tarea), 0, 0, 'L'); // 'L' para izquierda

                // Barra Gantt
                $d_s = $t['start']->diff($min_d)->days;
                $dur = $t['end']->diff($t['start'])->days;
                if ($dur < 1) $dur = 1;
                $xb = $m_left + ($d_s * $px_d);
                $wb = $dur * $px_d;

                switch ($t['estado']) {
                    case 'completada': $pdf->SetFillColor(25, 135, 84); break;
                    case 'en_proceso': $pdf->SetFillColor(13, 202, 240); break;
                    case 'pendiente':  $pdf->SetFillColor(255, 193, 7); break;
                    default:           $pdf->SetFillColor(150);
                }
                $pdf->Rect($xb, $y, $wb, 4, 'F');
                
                // Línea horizontal separadora
                $pdf->SetDrawColor(230);
                $pdf->Line($m_left, $y+5, $m_left+$g_width, $y+5);
                $pdf->SetDrawColor(0);
                
                $y += 7;
            }

            // Leyenda (Solo al final del gráfico)
            $pdf->SetY($y + 2); 
            $pdf->SetFont('Arial','',8);
            $pdf->SetFillColor(25, 135, 84); $pdf->Rect(20, $y+2, 4, 4, 'F'); $pdf->Text(26, $y+5, 'Completada');
            $pdf->SetFillColor(13, 202, 240); $pdf->Rect(50, $y+2, 4, 4, 'F'); $pdf->Text(56, $y+5, 'En Proceso');
            $pdf->SetFillColor(255, 193, 7); $pdf->Rect(80, $y+2, 4, 4, 'F'); $pdf->Text(86, $y+5, 'Pendiente');

            // Tabla Detallada
            $pdf->AddPage('L');
            $pdf->SetFont('Arial','B',14);
            $pdf->Cell(0,10, to_pdf_encoding('Detalle de Tareas'),0,1,'L');
            $pdf->Ln(2);
            $pdf->SetFillColor(230);
            $pdf->SetFont('Arial','B',9);
            $pdf->Cell(10,8,'Nro',1,0,'C',true);
            $pdf->Cell(95,8,'Tarea',1,0,'C',true);
            $pdf->Cell(25,8,'Inicio',1,0,'C',true);
            $pdf->Cell(25,8,'Fin',1,0,'C',true);
            $pdf->Cell(15,8,'Dias',1,0,'C',true);
            $pdf->Cell(50,8,'Asignado',1,0,'C',true);
            $pdf->Cell(30,8,'Estado',1,1,'C',true);
            $pdf->SetFont('Arial','',8);
            foreach ($tareas_tabla as $t) {
                $pdf->Cell(10,7,$t['nro'],1,0,'C');
                $pdf->Cell(95,7,to_pdf_encoding($t['titulo']),1);
                $pdf->Cell(25,7,$t['start']->format('d/m/Y'),1,0,'C');
                $pdf->Cell(25,7,$t['end']->format('d/m/Y'),1,0,'C');
                $pdf->Cell(15,7,$t['start']->diff($t['end'])->days,1,0,'C');
                $pdf->Cell(50,7,to_pdf_encoding(substr($t['asignados'] ?? '',0,30)),1);
                $pdf->Cell(30,7,ucfirst($t['estado']),1,1,'C');
            }
        }
        break;

    case 'tareas_todas':
        $pdf->AddPage();
        $pdf->SetY(35);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Reporte General de Tareas',0,1,'C');
        $pdf->Ln(5);
        $pdf->SetFillColor(200,220,255);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(10,10,'ID',1,0,'C',true);
        $pdf->Cell(100,10,'Titulo',1,0,'C',true);
        $pdf->Cell(30,10,'Estado',1,0,'C',true);
        $pdf->Cell(40,10,'Fecha',1,1,'C',true);
        $pdf->SetFont('Arial','',9);
        $res = $mysqli->query("SELECT id, titulo, estado, fecha_creacion FROM tareas ORDER BY id DESC");
        while($r = $res->fetch_assoc()){
            $pdf->Cell(10,8,$r['id'],1);
            $pdf->Cell(100,8,to_pdf_encoding(substr($r['titulo'],0,55)),1);
            $pdf->Cell(30,8,ucfirst($r['estado']),1);
            $pdf->Cell(40,8,date("d/m/Y", strtotime($r['fecha_creacion'])),1,1);
        }
        break;

    default:
        $pdf->AddPage();
        $pdf->Cell(0,10,'Reporte no definido.',0,1);
}

ob_end_clean(); 
$pdf->Output();
?>