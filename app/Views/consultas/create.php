<?php
// 1. Incluimos y ejecutamos el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
// Forzamos la acción 'create' para que el controlador nos dé los datos
$_GET['action'] = 'create'; 
$data = handleConsultaAction();

// 2. Desempaquetamos los datos
$paciente = $data['paciente'];
$patientId = $paciente['id'];

// 3. (Seguridad)
if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

// 4. Creamos el nombre completo
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>
        <small>Registrar Consulta para:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="header-actions" style="display: flex; gap: 0.5rem;">
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
                    <div class="form-group" style="flex-grow: 2;">
                        <label for="motivo_consulta">Motivo de la Consulta</label>
                        <select id="motivo_consulta" name="motivo_consulta" required>
                            <option value="" disabled selected>-- Seleccione un motivo --</option>
                            <option value="Primera vez - requiere lentes">Primera vez - requiere lentes</option>
                            <option value="Primera vez - malestar/infección">Primera vez - malestar/infección</option>
                            <option value="Reconsulta - requiere lentes">Reconsulta - requiere lentes</option>
                            <option value="Reconsulta - recaída">Reconsulta - recaída</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha" name="fecha" value="<?= date('Y-m-d\TH:i') ?>" required>
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