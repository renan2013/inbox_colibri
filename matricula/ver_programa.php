<?php
session_start();
require_once "../includes/db_connect.php";
// require_once "../includes/permissions.php"; // Permisos deshabilitados temporalmente

$programa = null;
$plan_estudios_data = [];
$cuatrimestres_agrupados = [];

if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
    $id_programa = trim($_GET['id']);

    // Un solo query para obtener toda la información
    $sql = "SELECT p.*, pe.cuatrimestre, pe.codigo, pe.materia, pe.creditos, pe.requisitos 
            FROM programas p 
            LEFT JOIN plan_estudios pe ON p.id_programa = pe.id_programa 
            WHERE p.id_programa = ? 
            ORDER BY pe.cuatrimestre, pe.materia";

    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $id_programa);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()){
                    if(!$programa){
                        $programa = [
                            'nombre_programa' => $row['nombre_programa'],
                            'categoria' => $row['categoria'],
                            'informacion' => $row['informacion'],
                            'oferta' => $row['oferta'],
                            'perfil' => $row['perfil']
                        ];
                    }
                    if($row['cuatrimestre']){
                        $cuatrimestres_agrupados[$row['cuatrimestre']][] = $row;
                    }
                }
            } else {
                $_SESSION['error'] = "Programa no encontrado.";
                header("location: lista_programas.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Error al ejecutar la consulta.";
            header("location: lista_programas.php");
            exit;
        }
        $stmt->close();
    }
    $mysqli->close();
} else {
    $_SESSION['error'] = "ID de programa no especificado.";
    header("location: lista_programas.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($programa['nombre_programa']); ?> - Universidad Castro Carazo</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background-color: #fff;
      color: #333;
      line-height: 1.6;
    }
    .container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 10px;
    }
    .hero-section {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://via.placeholder.com/1200x400?text=Estudiantes') no-repeat center center;
      background-size: cover;
      padding: 50px 10px;
      color: white;
      text-align: center;
      border-radius: 15px;
    }
    .hero-section h1 {
      font-size: 2.0rem;
      margin-bottom: 15px;
    }
    .info-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin: 20px 0;
    }
    .info-box {
      background: #f9f9f9;
      padding: 5px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      font-size: 13px;
    }
    .info-box h2 {
      color: #136ad5;
      margin-bottom: 5px;
      
    }
    .header-plan {
      background: #136ad5;
      padding: 10px 20px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: -20px;
      
      border-radius: 15px;
    }
    .header-plan h2 {
      font-size: 1.8rem;
      margin: 0;
    }
    .cuatrimestres-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 25px;
      margin: 20px 0;
      font-size: 11px;
    }
    @media (min-width: 992px) { /* Para pantallas grandes, mostrar 2 columnas */
      .cuatrimestres-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    .cuatrimestre-box {
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      font-size: 11px;
    }
    .cuatrimestre-title {
      background: linear-gradient(90deg, #136ad5, #136ad5);
      color: white;
      
      font-weight: bold;
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.8rem;
    }
    th, td {
      padding: 5px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    th {
      background-color: #f9f9f9;
    }
    @media (max-width: 768px) {
      .hero-section h1 { font-size: 2rem; }
      .header-plan { flex-direction: column; text-align: center; gap: 15px; }
      .info-section { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="header-plan">
      <div>
        <h2>Universidad Evangélica de las Américas</h2>
      </div>
      <img src="https://unela.ac.cr/classbox/public/uploads/client_data/logo_6884737aeb721.png" alt="Universidad Castro Carazo" style="height: 50px;">
    </div>
    <br/>
    <h2 style="text-align: center;">
    <?php echo htmlspecialchars(strtoupper($programa['categoria'])); ?> EN <?php echo htmlspecialchars(strtoupper($programa['nombre_programa'])); ?>  
    </h2>

    <div class="info-section">
      <div class="info-box ">
        <h4>INFORMACIÓN GENERAL</h4>
        <p><?php echo nl2br(htmlspecialchars($programa['informacion'])); ?></p>
      </div>

      <div class="info-box ">
        <h4>PERFIL PROFESIONAL</h4>
        <p><?php echo nl2br(htmlspecialchars($programa['perfil'])); ?></p>
      </div>

      <div class="info-box ">
        <h4>OFERTA LABORAL</h4>
        <p><?php echo nl2br(htmlspecialchars($programa['oferta'])); ?></p>
      </div>
    </div>

    <h3 style="text-align: center;">Programa de Estudios</h3>

    <div class="cuatrimestres-grid">
      <?php if (!empty($cuatrimestres_agrupados)): ?>
        <?php foreach ($cuatrimestres_agrupados as $cuatrimestre => $materias): ?>
          <div class="cuatrimestre-box">
            <div class="cuatrimestre-title"><?php echo htmlspecialchars(strtoupper($cuatrimestre)); ?></div>
            <table>
              <thead><tr><th>Código</th><th>Materia</th><th>Créditos</th><th>Requisitos</th></tr></thead>
              <tbody>
                <?php foreach ($materias as $materia): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($materia['codigo']); ?></td>
                    <td><?php echo htmlspecialchars($materia['materia']); ?></td>
                    <td><?php echo htmlspecialchars($materia['creditos']); ?></td>
                    <td><?php echo htmlspecialchars($materia['requisitos']); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay plan de estudios registrado para este programa.</p>
      <?php endif; ?>
    </div>
    <hr/>
    <p style="text-align: center; font-size:11px"> La Universidad Evangélica de las Américas esta aprobada por el Consejo Nacional de Enseñanza Superior Universitaria Privada · CONESUP <br/> 
    San José, Costa Rica, 2025 </p>

  </div>

</body>
</html>