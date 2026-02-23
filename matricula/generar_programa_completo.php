<?php
session_start();
require_once "../includes/db_connect.php";
require_once "../includes/permissions.php";

$page_title = "Programas Académicos Completos";
// Proteger la página
// if(!has_permission('generar_reportes')){
//     require_once '../includes/config.php';
//     header("location: " . BASE_URL . "dashboard.php?error=No tienes permiso");
//     exit;
// }

$programas_completos = [];
$sql = "SELECT p.id_programa, p.nombre_programa, p.categoria, p.informacion, p.oferta, p.perfil, pe.cuatrimestre, pe.codigo, pe.materia, pe.creditos, pe.requisitos FROM programas p LEFT JOIN plan_estudios pe ON p.id_programa = pe.id_programa ORDER BY p.nombre_programa, pe.cuatrimestre, pe.materia";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $id_programa = $row['id_programa'];
        if (!isset($programas_completos[$id_programa])) {
            $programas_completos[$id_programa] = [
                'id_programa' => $row['id_programa'], 'nombre_programa' => $row['nombre_programa'], 'categoria' => $row['categoria'],
                'informacion' => $row['informacion'], 'oferta' => $row['oferta'], 'perfil' => $row['perfil'], 'plan_estudios' => []
            ];
        }

        if ($row['cuatrimestre'] !== null) {
            $programas_completos[$id_programa]['plan_estudios'][] = [
                'cuatrimestre' => $row['cuatrimestre'], 'codigo' => $row['codigo'], 'materia' => $row['materia'],
                'creditos' => $row['creditos'], 'requisitos' => $row['requisitos']
            ];
        }
    }
    $result->free();
} else {
    $_SESSION['error'] = "Error al obtener los programas completos: " . $mysqli->error;
}

require_once '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <h3 class="text-center mb-4">Programas Académicos Completos</h3>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($programas_completos)): ?>
        <div class="accordion" id="programasAccordion">
        <?php foreach ($programas_completos as $index => $programa): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false">
                        <?php echo htmlspecialchars($programa['nombre_programa']); ?> (<?php echo htmlspecialchars($programa['categoria']); ?>)
                    </button>
                </h2>
                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" data-bs-parent="#programasAccordion">
                    <div class="accordion-body">
                        <p><strong>Información:</strong> <?php echo nl2br(htmlspecialchars($programa['informacion'])); ?></p>
                        <p><strong>Oferta:</strong> <?php echo nl2br(htmlspecialchars($programa['oferta'])); ?></p>
                        <p><strong>Perfil:</strong> <?php echo nl2br(htmlspecialchars($programa['perfil'])); ?></p>

                        <?php if (!empty($programa['plan_estudios'])): ?>
                            <h5>Plan de Estudios:</h5>
                            <?php 
                            $cuatrimestres_agrupados = [];
                            foreach ($programa['plan_estudios'] as $materia) {
                                $cuatrimestres_agrupados[$materia['cuatrimestre']][] = $materia;
                            }
                            ksort($cuatrimestres_agrupados);
                            ?>
                            <?php foreach ($cuatrimestres_agrupados as $cuatrimestre => $materias): ?>
                                <div class="mb-3">
                                    <h6><?php echo htmlspecialchars($cuatrimestre); ?></h6>
                                    <ul class="list-group">
                                        <?php foreach ($materias as $materia): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($materia['materia']); ?></strong> (<?php echo htmlspecialchars($materia['codigo']); ?>) - <?php echo htmlspecialchars($materia['creditos']); ?> Créditos
                                                <?php if (!empty($materia['requisitos'])): ?>
                                                    <br><small>Requisitos: <?php echo htmlspecialchars($materia['requisitos']); ?></small>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay plan de estudios registrado para este programa.</p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-end mt-3">
                            <a href="ver_programa.php?id=<?php echo $programa['id_programa']; ?>" class="btn btn-primary btn-sm">Ver Programa Completo</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No hay programas académicos registrados.</p>
    <?php endif; ?>
</div>

<?php 
$mysqli->close();
include '../includes/footer.php'; 
?>
