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

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
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
            
            <form action="/consulta_handler.php?action=store" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">

                <div class="form-row">
                    <div class="form-group flex-grow-2">
                        <label for="motivo_consulta">Tipo de Consulta</label>
                        <select id="motivo_consulta" name="motivo_consulta" required>
                            <option value="Refractiva" selected>👓 Examen de Vista (Lentes)</option>
                            <option value="Médica">🩺 Consulta Médica (Patología)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="detalle_motivo">Detalles del Motivo</label>
                    <input type="text" id="detalle_motivo" name="detalle_motivo" placeholder="Ej: Paciente refiere dolor de cabeza...">
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