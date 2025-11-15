<?php
// 1. Incluimos el controlador
require_once __DIR__ . '/../../Controllers/PatientController.php';

// 2. Obtenemos el ID de la URL
$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header('Location: /index.php?page=patients');
    exit();
}

// 3. Le decimos al controlador qué acción ejecutar y con qué ID
$_GET['action'] = 'details';
$data = handlePatientAction('details', $patientId); // <-- CAMBIO: Recibimos el PAQUETE

// 4. Verificamos y DESEMPAQUETAMOS
if (!$data || !$data['patient']) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}
$patient = $data['patient']; // <-- CAMBIO: Extraemos el paciente del paquete

// 5. Creamos el nombre completo
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
?>

<div class="page-header">
    <h1>Editar Paciente: <?= htmlspecialchars($fullName) ?></h1>
    <a href="/index.php?page=patients_details&id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
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

                <div class="form-row">
                    <div class="form-group" id="domicilio-group">
                        <label for="domicilio">Domicilio</label>
                        <input type="text" id="domicilio" name="domicilio" value="<?= htmlspecialchars($patient['domicilio'] ?? '') ?>">
                    </div>
                    <div class="form-group" id="telefono-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($patient['telefono'] ?? '') ?>">
                    </div>
                    <div class="form-group" id="edad-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" min="1" max="110" value="<?= htmlspecialchars($patient['edad'] ?? '') ?>">
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