<?php
/* ==========================================================================
   CLÍNICA - Confirmar Borrado de Paciente
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ClinicaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// Obtener ID del paciente
$patientId = $_GET['id'] ?? null;

if (!$patientId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
    exit();
}

// Obtener datos del paciente
$pdo = getConnection();
$patientModel = new PatientModel($pdo);
$patient = $patientModel->getById($patientId);

if (!$patient) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no encontrado'));
    exit();
}

$fullName = implode(' ', array_filter([
    $patient['nombre'], 
    $patient['apellido_paterno'], 
    $patient['apellido_materno']
]));
?>

<div class="page-header">
    <h1>⚠️ Confirmar Borrado de Paciente</h1>
    <div class="view-actions">
        <a href="/index.php?page=clinica_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Cancelar</a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <p class="emphasis-text">
                ¿Estás seguro de que quieres borrar permanentemente al paciente <strong><?= htmlspecialchars($fullName) ?></strong>?
            </p>
            <p class="alert alert-danger">
                <strong>⚠️ Advertencia:</strong> Esta acción no se puede deshacer. Se eliminará el paciente y todo su historial de consultas y ventas asociadas.
            </p>

            <form action="/patient_handler.php?action=delete" method="POST" class="mt-2">
                <input type="hidden" name="id" value="<?= $patientId ?>">
                <input type="hidden" name="redirect_to" value="clinica">
                <div class="form-actions actions-clean">
                    <button type="submit" class="btn btn-danger">🗑️ Sí, borrar permanentemente</button>
                    <a href="/index.php?page=clinica_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
