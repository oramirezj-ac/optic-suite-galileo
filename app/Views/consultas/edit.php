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

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
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
            
            <form action="/consulta_handler.php?action=update" method="POST">
                
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">

                <div class="form-row">
                    <div class="form-group flex-grow-2">
                        <label for="motivo_consulta">Tipo de Consulta</label>
                        <select id="motivo_consulta" name="motivo_consulta" required>
                            <option value="Refractiva" <?= $consulta['motivo_consulta'] == 'Refractiva' ? 'selected' : '' ?>>👓 Examen de Vista (Lentes)</option>
                            <option value="Médica" <?= $consulta['motivo_consulta'] == 'Médica' ? 'selected' : '' ?>>🩺 Consulta Médica (Patología)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?= $fechaInput ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="detalle_motivo">Detalles del Motivo</label>
                    <input type="text" id="detalle_motivo" name="detalle_motivo" value="<?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="diagnostico_dx">Diagnóstico (Opcional)</label>
                    <input type="text" id="diagnostico_dx" name="diagnostico_dx" value="<?= htmlspecialchars($consulta['diagnostico_dx'] ?? '') ?>">
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