<?php
/* ==========================================================================
   AV LIVE - Editar Agudeza Visual
   Formulario prellenado para editar AV
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

$fullName = FormatHelper::patientName($patient);
$catalogoAV = $consultaModel->getCatalogoAV();
?>

<div class="page-header">
    <h1>✏️ Editar Agudeza Visual</h1>
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
    
    <!-- Formulario AV -->
    <div class="card">
        <div class="card-header">
            <h3>Editar Agudeza Visual</h3>
        </div>
        <div class="card-body">
            <form action="/index.php?page=consultas_lentes_index&action=update_av" method="POST">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <div class="form-row">
                    <!-- AV AO (Ambos Ojos) -->
                    <div class="form-group">
                        <label>AV AO (Ambos Ojos)</label>
                        <select name="av_ao_id" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($catalogoAV as $av): ?>
                                <option value="<?= $av['id'] ?>" <?= ($consulta['av_ao_id'] == $av['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($av['valor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- AV OD (Ojo Derecho) -->
                    <div class="form-group">
                        <label>AV OD (Ojo Derecho)</label>
                        <select name="av_od_id" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($catalogoAV as $av): ?>
                                <option value="<?= $av['id'] ?>" <?= ($consulta['av_od_id'] == $av['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($av['valor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- AV OI (Ojo Izquierdo) -->
                    <div class="form-group">
                        <label>AV OI (Ojo Izquierdo)</label>
                        <select name="av_oi_id" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($catalogoAV as $av): ?>
                                <option value="<?= $av['id'] ?>" <?= ($consulta['av_oi_id'] == $av['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($av['valor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                    <a href="/index.php?page=av_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
</div>
