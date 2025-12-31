<?php
/* ==========================================================================
   CONSULTA DE LENTES - Eliminar
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';

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
     header('Location: /index.php?page=consultas_lentes_index&patient_id=' . $patientId . '&error=' . urlencode('Consulta no encontrada'));
     exit();
}

$fullName = FormatHelper::patientName($patient);
?>

<div class="page-content">
    <div class="card warning-card">
        <div class="card-body text-center">
            <h2 class="text-danger">⚠️ ¿Eliminar Consulta Refractiva?</h2>
            <p class="lead">Estás a punto de eliminar la consulta del <strong><?= $consulta['fecha'] ?></strong> de <strong><?= htmlspecialchars($fullName) ?></strong>.</p>
            <p>Se borrarán también todos los datos clínicos asociados (Agudeza Visual, Graduaciones, Notas).</p>
            <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>

            <form action="/index.php?page=consultas_lentes_index&action=delete_refractiva" method="POST" class="mt-4">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <button type="submit" class="btn btn-danger btn-lg">🗑️ Sí, Eliminar Permanentemente</button>
                <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" class="btn btn-secondary btn-lg">Cancelar</a>
            </form>
    </div>
</div>
