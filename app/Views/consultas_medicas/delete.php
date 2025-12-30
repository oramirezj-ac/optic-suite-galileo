<?php
/* ==========================================================================
   CONSULTA MÉDICA - Confirmar Eliminación
   ========================================================================== */

require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Models/PatientModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$consultaId = $_GET['id'] ?? null;

if (!$consultaId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Consulta no especificada'));
    exit();
}

$pdo = getConnection();
$consultaModel = new ConsultaModel($pdo);
$consulta = $consultaModel->getConsultaById($consultaId);

if (!$consulta) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Consulta no encontrada'));
    exit();
}

// Obtener información del paciente
$patientModel = new PatientModel($pdo);
$patient = $patientModel->getById($consulta['paciente_id']);

$fullName = implode(' ', array_filter([
    $patient['nombre'], 
    $patient['apellido_paterno'], 
    $patient['apellido_materno']
]));

// Procesar eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $success = $consultaModel->deleteConsulta($consultaId);
    
    if ($success) {
        header('Location: /index.php?page=consultas_medicas_index&patient_id=' . $consulta['paciente_id'] . '&success=' . urlencode('Consulta eliminada exitosamente'));
    } else {
        header('Location: /index.php?page=consultas_medicas_index&patient_id=' . $consulta['paciente_id'] . '&error=' . urlencode('Error al eliminar consulta'));
    }
    exit();
}
?>

<div class="page-header">
    <h1>🗑️ Eliminar Consulta Médica</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_medicas_details&id=<?= $consultaId ?>" class="btn btn-secondary">← Cancelar</a>
    </div>
</div>

<div class="page-content">
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-danger">
                <h3>⚠️ ¿Estás seguro de eliminar esta consulta?</h3>
                <p>Esta acción no se puede deshacer. Se eliminará toda la información de esta consulta.</p>
            </div>
            
            <h4>Información de la Consulta a Eliminar:</h4>
            
            <div class="detail-row">
                <strong>Paciente:</strong>
                <span><?= htmlspecialchars($fullName) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Fecha:</strong>
                <span><?= \FormatHelper::dateFull($consulta['fecha']) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Motivo:</strong>
                <span><?= htmlspecialchars($consulta['detalle_motivo'] ?? 'Sin motivo') ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Diagnóstico:</strong>
                <span><?= htmlspecialchars(substr($consulta['diagnostico_dx'] ?? 'Sin diagnóstico', 0, 100)) ?></span>
            </div>
            
            <hr>
            
            <form method="POST" style="display: flex; gap: 1rem; justify-content: center;">
                <input type="hidden" name="confirm_delete" value="1">
                <button type="submit" class="btn btn-danger">✓ Sí, Eliminar Consulta</button>
                <a href="/index.php?page=consultas_medicas_details&id=<?= $consultaId ?>" class="btn btn-secondary">✗ No, Cancelar</a>
            </form>
        </div>
    </div>
    
</div>
