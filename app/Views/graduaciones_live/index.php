<?php
/* ==========================================================================
   GRADUACIONES LIVE - Vista Principal REFACTORIZADA
   Versión con Componentes: 565 líneas → 120 líneas (79% reducción)
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'index'; 
$data = handleConsultaLentesAction();

$paciente = $data['patient'];
$consulta = $data['consultas'][0] ?? null;

if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = FormatHelper::patientName($paciente);
$edad = $paciente['fecha_nacimiento'] ? \FormatHelper::calculateAge($paciente['fecha_nacimiento']) : 0;

// Obtener graduaciones específicas
require_once __DIR__ . '/../../Models/GraduacionModel.php';
$pdo = getConnection();
$graduacionModel = new GraduacionModel($pdo);

$autoGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'autorrefractometro');
$foroGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'foroptor');
$ambulatoriaGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'ambulatorio');
$lensometroGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'lensometro');
$externaGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'externa');

$hasAuto = !empty($autoGrads['OD']) || !empty($autoGrads['OI']);
$hasForo = !empty($foroGrads['OD']) || !empty($foroGrads['OI']);
$hasAmbulatoria = !empty($ambulatoriaGrads['OD']) || !empty($ambulatoriaGrads['OI']);
$hasLensometro = !empty($lensometroGrads['OD']) || !empty($lensometroGrads['OI']);
$hasExterna = !empty($externaGrads['OD']) || !empty($externaGrads['OI']);

$autoIsFinal = !empty($autoGrads['OD']['es_graduacion_final']) || !empty($autoGrads['OI']['es_graduacion_final']);
$foroIsFinal = !empty($foroGrads['OD']['es_graduacion_final']) || !empty($foroGrads['OI']['es_graduacion_final']);
$ambulatoriaIsFinal = !empty($ambulatoriaGrads['OD']['es_graduacion_final']) || !empty($ambulatoriaGrads['OI']['es_graduacion_final']);
$lensometroIsFinal = !empty($lensometroGrads['OD']['es_graduacion_final']) || !empty($lensometroGrads['OI']['es_graduacion_final']);
$externaIsFinal = !empty($externaGrads['OD']['es_graduacion_final']) || !empty($externaGrads['OI']['es_graduacion_final']);

// Obtener catálogo de AV para AV/CV
require_once __DIR__ . '/../../Models/ConsultaModel.php';
$consultaModel = new ConsultaModel($pdo);
$catalogoAV = $consultaModel->getCatalogoAV();

$avLookup = [];
foreach ($catalogoAV as $av) {
    $avLookup[$av['id']] = $av['valor'];
}

$hasAV = !empty($consulta['av_ao_id']) || !empty($consulta['av_od_id']) || !empty($consulta['av_oi_id']);
$hasCV = !empty($consulta['cv_ao_id']) || !empty($consulta['cv_od_id']) || !empty($consulta['cv_oi_id']);

// Helper para renderizar ojo
function renderOjoLive($label, $data) {
    if (empty($data)) return '<div class="text-muted">Sin datos</div>';
    
    $html = '<div class="graduacion-formula">';
    $html .= '<span class="graduacion-ojo-label">' . $label . '</span>';
    $html .= '<span class="valor">' . ($data['esfera'] ?? '0.00') . '</span> <span class="simbolo">=</span> ';
    $html .= '<span class="valor">' . ($data['cilindro'] ?? '0.00') . '</span> <span class="simbolo">x</span> ';
    $html .= '<span class="valor">' . ($data['eje'] ?? '0') . '</span> <span class="simbolo">°</span>';
    if (!empty($data['adicion']) && $data['adicion'] != '0.00') {
        $html .= ' <span class="valor valor-add">' . $data['adicion'] . '</span>';
    }
    $html .= '</div>';
    return $html;
}

// Scripts específicos para esta página
$pageScripts = ['/js/graduaciones-dp.js'];
?>

<!-- Header con info del paciente -->
<div class="patient-header-fixed">
    <div>
        <h3>👤 <?= htmlspecialchars($fullName) ?></h3>
        <p class="text-muted">Consulta del <?= \FormatHelper::dateFull($consulta['fecha']) ?></p>
    </div>
    <div class="header-actions">
        <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">← Volver al Historial</a>
    </div>
</div>

<div class="page-content graduaciones-live">
    
    <?php 
    /* ==========================================================================
       COMPONENTES DE CARDS
       Cada card es un archivo independiente en /components/
       Esto reduce el archivo principal de 565 líneas a ~120 líneas
       ========================================================================== */
    
    // Card 0: AV/CV (Agudeza Visual / Corrección Visual)
    require __DIR__ . '/components/card_av_cv.php';
    
    // Card 1: Autorefractómetro
    require __DIR__ . '/components/card_auto.php';
    
    // Card 1.5: Distancia Pupilar
    require __DIR__ . '/components/card_dp.php';
    
    // Card 2: Foroptor
    require __DIR__ . '/components/card_foroptor.php';
    
    // Card 3: Ambulatoria
    require __DIR__ . '/components/card_ambulatoria.php';
    
    // Card 4: Lensómetro
    require __DIR__ . '/components/card_lensometro.php';
    
    // Card 5: Externa
    require __DIR__ . '/components/card_externa.php';
    ?>

</div>
