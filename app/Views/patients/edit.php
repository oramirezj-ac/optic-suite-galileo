<?php
// 1. Incluimos el controlador y helpers
require_once __DIR__ . '/../../Controllers/PatientController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Obtenemos el ID y buscamos al paciente
$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header('Location: /index.php?page=patients');
    exit();
}

$_GET['action'] = 'details';
$data = handlePatientAction('details', $patientId);

// 3. Verificamos
if (!$data || !$data['patient']) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}
$patient = $data['patient'];

// 4. Creamos el nombre completo
$fullName = FormatHelper::patientName($patient);

// 5. Lógica de Pre-cálculo para la Vista
// Si tenemos fecha de nacimiento, calculamos la edad (número) para llenar el input visual
$edadCalculada = '';
if (!empty($patient['fecha_nacimiento'])) {
    try {
        $nacimiento = new DateTime($patient['fecha_nacimiento']);
        $hoy = new DateTime();
        $edadCalculada = $hoy->diff($nacimiento)->y;
    } catch (Exception $e) {
        $edadCalculada = '';
    }
}
?>

<div class="page-header">
    <h1>Editar Paciente: <?= htmlspecialchars($fullName) ?></h1>
    <div class="view-actions">
        <a href="/index.php?page=patients_details&id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="/patient_handler.php?action=update" method="POST">
                <input type="hidden" name="id" value="<?= $patient['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($patient['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($patient['apellido_paterno'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($patient['apellido_materno'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row align-items-end">
                    
                    <div class="form-group">
                        <label for="fecha_primera_visita">Fecha de 1ª Visita</label>
                        <input type="date" id="fecha_primera_visita" name="fecha_primera_visita" 
                               value="<?= htmlspecialchars($patient['fecha_primera_visita'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad_calculadora" placeholder="Ej. 82" min="0" max="120" 
                               value="<?= htmlspecialchars($edadCalculada) ?>">
                    </div>

                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" 
                               value="<?= htmlspecialchars($patient['fecha_nacimiento'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="domicilio-group">
                        <label for="domicilio">Domicilio</label>
                        <input type="text" id="domicilio" name="domicilio" value="<?= htmlspecialchars($patient['domicilio'] ?? '') ?>">
                    </div>
                    <div class="form-group" id="telefono-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($patient['telefono'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="antecedentes_medicos">Antecedentes Médicos</label>
                    <textarea id="antecedentes_medicos" name="antecedentes_medicos" rows="3"><?= htmlspecialchars($patient['antecedentes_medicos'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>