<?php
// 1. Incluimos el controlador
require_once __DIR__ . '/../../Controllers/PatientController.php';

// 2. Le decimos al controlador qué acción ejecutar
$_GET['action'] = 'details';
$data = handlePatientAction(); // <-- CAMBIO: Recibimos el PAQUETE

// 3. Verificamos y DESEMPAQUETAMOS
if (!$data || !$data['patient']) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}
$patient = $data['patient']; // <-- CAMBIO: Extraemos el paciente del paquete

// 4. El resto del código funciona igual
$patientId = $patient['id'];
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
?>

<div class="page-header">
    <h1>Confirmar Borrado de Paciente</h1>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <p class="emphasis-text">
                ¿Estás seguro de que quieres borrar permanentemente al paciente <strong><?= htmlspecialchars($fullName) ?></strong>?
            </p>
            <p class="alert alert-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminará el paciente y todo su historial de consultas y ventas asociadas.</p>

            <form action="/patient_handler.php?action=delete" method="POST" style="margin-top: 2rem;">
                <input type="hidden" name="id" value="<?= $patientId ?>">
                <div class="form-actions" style="border-top: none; padding-top: 0; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-danger">Sí, borrar permanentemente</button>
                    <a href="/index.php?page=patients_details&id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>