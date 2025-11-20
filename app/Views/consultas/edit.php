<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Forzamos la acción 'edit' para obtener los datos
$_GET['action'] = 'edit'; 
$data = handleConsultaAction();

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$consulta = $data['consulta'];
$patientId = $paciente['id'];

// 4. (Seguridad)
if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Creamos el nombre completo
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));

// 6. Formateamos la fecha para el input 'datetime-local'
$fechaInput = date('Y-m-d\TH:i', strtotime($consulta['fecha']));
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
                    <div class="form-group" style="flex-grow: 2;">
                        <label for="motivo_consulta">Motivo de la Consulta</label>
                        <select id="motivo_consulta" name="motivo_consulta" required>
                            <?php $motivo = $consulta['motivo_consulta']; ?>
                            <option value="Primera vez - requiere lentes" <?= $motivo == 'Primera vez - requiere lentes' ? 'selected' : '' ?>>Primera vez - requiere lentes</option>
                            <option value="Primera vez - malestar/infección" <?= $motivo == 'Primera vez - malestar/infección' ? 'selected' : '' ?>>Primera vez - malestar/infección</option>
                            <option value="Reconsulta - requiere lentes" <?= $motivo == 'Reconsulta - requiere lentes' ? 'selected' : '' ?>>Reconsulta - requiere lentes</option>
                            <option value="Reconsulta - recaída" <?= $motivo == 'Reconsulta - recaída' ? 'selected' : '' ?>>Reconsulta - recaída</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha y Hora</label>
                        <input type="datetime-local" id="fecha" name="fecha" value="<?= $fechaInput ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="detalle_motivo">Detalles del Motivo</label>
                    <input type="text" id="detalle_motivo" name="detalle_motivo" value="<?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?>" placeholder="Ej: Paciente refiere dolor de cabeza...">
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones Generales</label>
                    <textarea id="observaciones" name="observaciones" rows="3" placeholder="Observaciones internas..."><?= htmlspecialchars($consulta['observaciones'] ?? '') ?></textarea>
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