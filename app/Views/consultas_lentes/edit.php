<?php
/* ==========================================================================
   CONSULTA DE LENTES - Editar
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$consultaId = $_GET['id'] ?? null;
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
    echo "Datos no encontrados.";
    exit();
}

$fullName = FormatHelper::patientName($patient);
?>

<div class="page-header">
    <h1>✏️ Editar Consulta Refractiva</h1>
    <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">Paciente: <?= htmlspecialchars($fullName) ?></h4>
            <form action="/index.php?page=consultas_lentes_index&action=update_refractiva" method="POST">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de Consulta</label>
                        <input type="date" name="fecha_consulta" value="<?= date('Y-m-d', strtotime($consulta['fecha'])) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                     <label>Motivo</label>
                     <!-- Usamos detalle_motivo si existe, o 'Refractiva' por defecto -->
                     <input type="text" name="motivo" value="<?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?>" placeholder="Ej. Revisión anual">
                </div>
                
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3"><?= htmlspecialchars($consulta['observaciones'] ?? '') ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Actualizar Datos Generales</button>
                    <!-- Enlace directo para editar la parte clínica -->
                    <a href="/index.php?page=graduaciones_live_index&id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-info ml-2">
                         🩺 Ir a Datos Clínicos (AV, Graduación)
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
