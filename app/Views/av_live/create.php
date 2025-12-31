<?php
/* ==========================================================================
   AV LIVE - Capturar Agudeza Visual / Corrección Visual
   Formulario para capturar AV o CV según el parámetro mode
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

// Determinar qué mostrar según el modo
$isCV = ($mode === 'cv');
$title = $isCV ? '👓 Capturar Corrección Visual' : '👁️ Capturar Agudeza Visual';
$titleShort = $isCV ? 'Corrección Visual (Con Lentes)' : 'Agudeza Visual (Sin Lentes)';
$prefix = $isCV ? 'cv' : 'av';
$action = $isCV ? 'store_cv' : 'store_av';
?>

<div class="page-header">
    <h1><?= $title ?></h1>
    <div class="view-actions">
        <a href="/index.php?page=graduaciones_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Cancelar</a>
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
    
    <!-- Formulario AV/CV -->
    <div class="card">
        <div class="card-header">
            <h3><?= $titleShort ?></h3>
        </div>
        <div class="card-body">
            <form action="/index.php?page=consultas_lentes_index&action=<?= $action ?>" method="POST">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <div class="av-centered-wrapper">
                    <div class="av-capture-card"> <!-- Contenedor central (~1/3 del ancho en pantallas grandes) -->
                        <div class="form-row">
                            <!-- AO (Ambos Ojos) -->
                            <div class="form-group form-group-third text-center px-1">
                                <label class="av-input-label"><?= strtoupper($prefix) ?> AO</label>
                                <select name="<?= $prefix ?>_ao_id" required class="form-control form-control-sm av-select-compact">
                                    <option value="">--</option>
                                    <?php foreach ($catalogoAV as $av): ?>
                                        <option value="<?= $av['id'] ?>"><?= htmlspecialchars($av['valor']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- OD (Ojo Derecho) -->
                            <div class="form-group form-group-third text-center px-1">
                                <label class="av-input-label"><?= strtoupper($prefix) ?> OD</label>
                                <select name="<?= $prefix ?>_od_id" required class="form-control form-control-sm av-select-compact">
                                    <option value="">--</option>
                                    <?php foreach ($catalogoAV as $av): ?>
                                        <option value="<?= $av['id'] ?>"><?= htmlspecialchars($av['valor']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- OI (Ojo Izquierdo) -->
                            <div class="form-group form-group-third text-center px-1">
                                <label class="av-input-label"><?= strtoupper($prefix) ?> OI</label>
                                <select name="<?= $prefix ?>_oi_id" required class="form-control form-control-sm av-select-compact">
                                    <option value="">--</option>
                                    <?php foreach ($catalogoAV as $av): ?>
                                        <option value="<?= $av['id'] ?>"><?= htmlspecialchars($av['valor']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar y Continuar</button>
                    <a href="/index.php?page=graduaciones_live_index&consulta_id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
</div>
