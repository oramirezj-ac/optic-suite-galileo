<?php
/* ==========================================================================
   AV LIVE - Ver Agudeza Visual / Corrección Visual
   Muestra AV o CV según el parámetro mode
   ========================================================================== */

require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Models/PatientModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$consultaId = $_GET['consulta_id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;
$mode = $_GET['mode'] ?? 'av'; // 'av' o 'cv'

if (!$consultaId || !$patientId) {
    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos incompletos'));
    exit();
}

$pdo = getConnection();
$consultaModel = new ConsultaModel($pdo);
$patientModel = new PatientModel($pdo);

$consulta = $consultaModel->getConsultaById($consultaId);
$patient = $patientModel->getById($patientId);

if (!$consulta || !$patient) {
    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos no encontrados'));
    exit();
}

$fullName = FormatHelper::patientName($patient);
$catalogoAV = $consultaModel->getCatalogoAV();

// Crear lookup para valores de AV
$avLookup = [];
foreach ($catalogoAV as $av) {
    $avLookup[$av['id']] = $av['valor'];
}

// Determinar qué mostrar según el modo
$isCV = ($mode === 'cv');
$title = $isCV ? '👓 Corrección Visual (Con Lentes)' : '👁️ Agudeza Visual (Sin Lentes)';
$titleShort = $isCV ? 'Corrección Visual' : 'Agudeza Visual';
$prefix = $isCV ? 'cv' : 'av';
$aoField = $prefix . '_ao_id';
$odField = $prefix . '_od_id';
$oiField = $prefix . '_oi_id';
?>

<div class="page-header">
    <h1><?= $title ?></h1>
    <div class="view-actions">
        <a href="/index.php?page=graduaciones_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Volver a Graduaciones</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Info del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <p>📋 <?= htmlspecialchars($consulta['detalle_motivo'] ?? 'Examen de vista') ?></p>
        </div>
    </div>
    
    <!-- Datos de AV/CV -->
    <div class="card">
        <div class="card-header">
            <h3><?= $titleShort ?></h3>
        </div>
        <div class="card-body">
            <?php if (empty($consulta[$aoField]) && empty($consulta[$odField]) && empty($consulta[$oiField])): ?>
                <p class="text-secondary">No se ha capturado <?= $isCV ? 'la corrección visual' : 'la agudeza visual' ?>.</p>
                <a href="/index.php?page=av_live_create&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>&mode=<?= $mode ?>" class="btn btn-primary">
                    ➕ Capturar <?= $titleShort ?>
                </a>
            <?php else: ?>
                <div class="av-display">
                    <div class="av-row">
                        <strong><?= strtoupper($prefix) ?> AO (Ambos Ojos):</strong>
                        <span><?= $avLookup[$consulta[$aoField]] ?? '-' ?></span>
                    </div>
                    <div class="av-row">
                        <strong><?= strtoupper($prefix) ?> OD (Ojo Derecho):</strong>
                        <span><?= $avLookup[$consulta[$odField]] ?? '-' ?></span>
                    </div>
                    <div class="av-row">
                        <strong><?= strtoupper($prefix) ?> OI (Ojo Izquierdo):</strong>
                        <span><?= $avLookup[$consulta[$oiField]] ?? '-' ?></span>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="/index.php?page=av_live_edit&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>&mode=<?= $mode ?>" class="btn btn-secondary">
                        ✏️ Editar
                    </a>
                    <a href="/index.php?page=av_live_delete&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>&mode=<?= $mode ?>" class="btn btn-danger">
                        🗑️ Borrar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>
