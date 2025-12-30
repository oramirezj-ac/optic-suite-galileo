<?php
/**
 * GRADUACIONES LIVE - Vista Principal Refactorizada
 * Versión con Componentes (100 líneas vs 565 líneas)
 */

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

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$edad = $paciente['fecha_nacimiento'] ? \FormatHelper::calculateAge($paciente['fecha_nacimiento']) : 0;

// Obtener graduaciones específicas
require_once __DIR__ . '/../../Models/GraduacionModel.php';
$pdo = getDatabaseConnection();
$gradModel = new GraduacionModel($pdo);

$autoGrads = $gradModel->getByConsultaAndType($consulta['id'], 'autorrefractometro');
$foroGrads = $gradModel->getByConsultaAndType($consulta['id'], 'foroptor');
$ambulGrads = $gradModel->getByConsultaAndType($consulta['id'], 'ambulatoria');
$lensGrads = $gradModel->getByConsultaAndType($consulta['id'], 'lensometro');
$extGrads = $gradModel->getByConsultaAndType($consulta['id'], 'externa');

$hasAuto = !empty($autoGrads['OD']) || !empty($autoGrads['OI']);
$hasForo = !empty($foroGrads['OD']) || !empty($foroGrads['OI']);
$hasAmbul = !empty($ambulGrads['OD']) || !empty($ambulGrads['OI']);
$hasLens = !empty($lensGrads['OD']) || !empty($lensGrads['OI']);
$hasExt = !empty($extGrads['OD']) || !empty($extGrads['OI']);

$autoIsFinal = ($autoGrads['OD']['es_graduacion_final'] ?? 0) == 1 || ($autoGrads['OI']['es_graduacion_final'] ?? 0) == 1;
$foroIsFinal = ($foroGrads['OD']['es_graduacion_final'] ?? 0) == 1 || ($foroGrads['OI']['es_graduacion_final'] ?? 0) == 1;
$ambulIsFinal = ($ambulGrads['OD']['es_graduacion_final'] ?? 0) == 1 || ($ambulGrads['OI']['es_graduacion_final'] ?? 0) == 1;
$lensometroIsFinal = ($lensGrads['OD']['es_graduacion_final'] ?? 0) == 1 || ($lensGrads['OI']['es_graduacion_final'] ?? 0) == 1;
$externaIsFinal = ($extGrads['OD']['es_graduacion_final'] ?? 0) == 1 || ($extGrads['OI']['es_graduacion_final'] ?? 0) == 1;

$hasAV = !empty($consulta['av_od_id']) || !empty($consulta['av_oi_id']) || !empty($consulta['av_ao_id']);
$hasCV = !empty($consulta['cv_od_id']) || !empty($consulta['cv_oi_id']) || !empty($consulta['cv_ao_id']);

// Lookup para AV
$avLookup = [
    1 => '20/20', 2 => '20/25', 3 => '20/30', 4 => '20/40', 5 => '20/50',
    6 => '20/60', 7 => '20/70', 8 => '20/80', 9 => '20/100', 10 => '20/200',
    11 => 'CD (Cuenta Dedos)', 12 => 'MM (Movimiento de Manos)', 13 => 'PL (Percepción de Luz)', 14 => 'NPL (No Percepción de Luz)'
];

// Helper function
function renderOjoLive($ojo, $data) {
    if (empty($data)) return '<div class="ojo-empty">Sin datos</div>';
    
    $esfera = $data['esfera'] ?? '0.00';
    $cilindro = $data['cilindro'] ?? '0.00';
    $eje = $data['eje'] ?? '0';
    $adicion = $data['adicion'] ?? '0.00';
    
    return "
        <div class='ojo-data'>
            <strong>{$ojo}:</strong>
            <span>Esf: {$esfera}</span>
            <span>Cil: {$cilindro}</span>
            <span>Eje: {$eje}°</span>
            <span>Add: {$adicion}</span>
        </div>
    ";
}

// Scripts específicos para esta página
$pageScripts = ['/js/graduaciones-dp.js'];
?>

<div class="container-fluid">
    <div class="page-header">
        <h1>📋 Graduaciones de <?= htmlspecialchars($fullName) ?></h1>
        <p class="text-muted">Edad: <?= $edad ?> años | Consulta: <?= date('d/m/Y', strtotime($consulta['fecha'])) ?></p>
    </div>

    <!-- Componentes de Cards -->
    <?php require 'components/card_av_cv.php'; ?>
    <?php require 'components/card_auto.php'; ?>
    
    <!-- Card DP (inline por ahora - puedes moverla a componente después) -->
    <?php include __DIR__ . '/../graduaciones_live/index.php'; // Líneas 226-368 ?>
    
    <!-- Resto de cards - crear componentes similares -->
    <!-- TODO: Extraer card_foroptor.php, card_ambulatoria.php, card_lensometro.php, card_externa.php -->
    
</div>
