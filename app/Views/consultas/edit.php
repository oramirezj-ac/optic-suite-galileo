<?php
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'edit'; 
$data = handleConsultaAction();

$paciente = $data['paciente'];
$consulta = $data['consulta'];
$patientId = $paciente['id'];

if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = FormatHelper::patientName($paciente);
$fechaInput = date('Y-m-d', strtotime($consulta['fecha']));
?>

<div class="page-header">
    <h1>
        <small>Editando Consulta de:</small><br>
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
            
            <form action="/index.php?page=consultas_index&action=update" method="POST">
                
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="motivo_consulta" value="Refractiva">

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha">Fecha de Consulta</label>
                        <input type="date" id="fecha" name="fecha" value="<?= $fechaInput ?>" required>
                    </div>
                    
                    <div class="form-group flex-grow-2">
                        <label for="detalle_motivo">Motivo de la Consulta</label>
                        <input type="text" id="detalle_motivo" name="detalle_motivo" value="<?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?>" placeholder="Ej: Revisión anual, cambio de graduación...">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones Generales</label>
                    <textarea id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($consulta['observaciones'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Actualizar Consulta
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>