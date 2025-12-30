<?php
/* ==========================================================================
   AV LIVE - Confirmar Borrado
   Página de confirmación para borrar AV
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
    <h1>🗑️ Confirmar Borrado</h1>
    <div class="view-actions">
        <a href="/index.php?page=av_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Cancelar</a>
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
    
    <!-- Confirmación -->
    <div class="card">
        <div class="card-header bg-danger">
            <h3 style="color: white;">⚠️ Confirmar Borrado de Agudeza Visual</h3>
        </div>
        <div class="card-body">
            <p><strong>¿Está seguro que desea borrar los siguientes datos?</strong></p>
            
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
            
            <div class="alert alert-warning">
                <strong>⚠️ Advertencia:</strong> Esta acción no se puede deshacer. Los datos de agudeza visual se eliminarán permanentemente.
            </div>
            
            <form action="/index.php?page=consultas_lentes_index&action=delete_av" method="POST">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-danger">🗑️ Sí, Borrar Datos</button>
                    <a href="/index.php?page=av_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
</div>
