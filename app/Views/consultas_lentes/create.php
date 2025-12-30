<?php
/* ==========================================================================
   CONSULTA DE LENTES - Crear Nueva
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
    exit();
}

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
$edad = $patient['fecha_nacimiento'] ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) : 'Sin datos';
?>

<!-- Encabezado con datos del paciente -->
<div class="page-header">
    <h1>👓 Nueva Consulta de Lentes</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Ver Historial</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Información del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <div class="patient-info-details">
                <span><strong>Edad:</strong> <?= $edad === 'Sin datos' ? $edad : $edad . ' años' ?></span>
                <span><strong>Fecha de 1ª Visita:</strong> <?= \FormatHelper::dateFull($patient['fecha_primera_visita']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Formulario de consulta -->
    <div class="card">
        <div class="card-body">
            <h3>Formulario de Consulta Refractiva</h3>
            <p class="text-secondary">Próximamente: Captura de agudeza visual, graduaciones, DP, etc.</p>
            
            <form action="/index.php?page=consultas_lentes_index&action=store_refractiva" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <div class="form-row">
                    <div class="form-group form-row-date">
                        <label>Fecha de Consulta</label>
                        <input type="date" name="fecha_consulta" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group form-row-motive">
                        <label>Motivo</label>
                        <input type="text" name="motivo" placeholder="Ej. Revisión anual">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="3" placeholder="Notas adicionales..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Consulta</button>
                    <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
