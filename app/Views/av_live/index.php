<?php
/* ==========================================================================
   AV LIVE - Ver Agudeza Visual
   Muestra la AV guardada con opciones para editar/borrar
   ========================================================================== */

require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Models/PatientModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$consultaId = $_GET['consulta_id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;

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

$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
$catalogoAV = $consultaModel->getCatalogoAV();

// Crear lookup para valores de AV
$avLookup = [];
foreach ($catalogoAV as $av) {
    $avLookup[$av['id']] = $av['valor'];
}
?>

<div class="page-header">
    <h1>👁️ Agudeza Visual</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Volver al Historial</a>
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
    
    <!-- Datos de AV -->
    <div class="card">
        <div class="card-header">
            <h3>Agudeza Visual (Sin Lentes)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($consulta['av_ao_id']) && empty($consulta['av_od_id']) && empty($consulta['av_oi_id'])): ?>
                <p class="text-secondary">No se ha capturado la agudeza visual.</p>
                <a href="/index.php?page=av_live_create&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-primary">
                    ➕ Capturar Agudeza Visual
                </a>
            <?php else: ?>
                <div class="av-display">
                    <div class="av-row">
                        <strong>AV AO (Ambos Ojos):</strong>
                        <span><?= $avLookup[$consulta['av_ao_id']] ?? '-' ?></span>
                    </div>
                    <div class="av-row">
                        <strong>AV OD (Ojo Derecho):</strong>
                        <span><?= $avLookup[$consulta['av_od_id']] ?? '-' ?></span>
                    </div>
                    <div class="av-row">
                        <strong>AV OI (Ojo Izquierdo):</strong>
                        <span><?= $avLookup[$consulta['av_oi_id']] ?? '-' ?></span>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="/index.php?page=av_live_edit&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">
                        ✏️ Editar
                    </a>
                    <a href="/index.php?page=av_live_delete&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-danger">
                        🗑️ Borrar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>
