<?php
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
$_GET['action'] = 'create'; 
$data = handleConsultaAction();

$paciente = $data['paciente'];
$patientId = $paciente['id'];

if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

$fullName = FormatHelper::patientName($paciente);
?>

<div class="page-header">
    <h1>
        <small>Registrar Consulta para:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            
            <form action="/index.php?page=consultas_index&action=store" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                <input type="hidden" name="motivo_consulta" value="Refractiva">

                <div class="form-row">
                    <div class="form-group form-group-quarter">
                        <label for="fecha">Fecha de Consulta</label>
                        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group form-group-three-quarters">
                        <label for="detalle_motivo">Motivo de la Consulta</label>
                        <input type="text" id="detalle_motivo" name="detalle_motivo" placeholder="Ej: Revisión anual, cambio de graduación...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones Generales</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Observaciones internas..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar Consulta y Añadir Graduaciones &rarr;
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>