<?php
/* ==========================================================================
   VISTA: REVISIÓN DE PACIENTES DUPLICADOS (Diseño v3.1)
   ========================================================================== */

// 1. Incluimos el controlador
require_once __DIR__ . '/../../Controllers/PatientController.php';

// 2. Obtenemos los datos del paciente NUEVO de la sesión
if (!isset($_SESSION['new_patient_data'])) {
    header('Location: /index.php?page=patients_create&error=' . urlencode('Error de sesión, intente de nuevo.'));
    exit();
}
$newPatientData = $_SESSION['new_patient_data'];


// 3. Volvemos a ejecutar la búsqueda de duplicados
$pdo = getConnection();
$patientModel = new PatientModel($pdo);
$duplicates = $patientModel->findSimilar($newPatientData);

if (empty($duplicates)) {
    // Seguro: Si llegó aquí pero ya no hay duplicados, crea el paciente.
    $newPatientId = $patientModel->create($newPatientData);
    if ($newPatientId) {
        unset($_SESSION['new_patient_data']); // Limpiamos la sesión
        header('Location: /index.php?page=patients_details&id=' . $newPatientId . '&success=created');
    } else {
        header('Location: /index.php?page=patients_create&error=' . urlencode('Error al crear paciente.'));
    }
    exit();
}

// 4. Función helper para mostrar nombres
function formatPatientName($patient) {
    return implode(' ', array_filter([
        $patient['nombre'] ?? '',
        $patient['apellido_paterno'] ?? '',
        $patient['apellido_materno'] ?? ''
    ]));
}

// 5. OBTENEMOS EL ID DEL PRIMER DUPLICADO (el más relevante)
$firstDuplicateId = $duplicates[0]['id'];

// 6. Preparamos los datos nuevos para los formularios
function renderNewPatientHiddenFields($data) {
    echo '<input type="hidden" name="nombre" value="'.htmlspecialchars($data['nombre'] ?? '').'">';
    echo '<input type="hidden" name="apellido_paterno" value="'.htmlspecialchars($data['apellido_paterno'] ?? '').'">';
    echo '<input type="hidden" name="apellido_materno" value="'.htmlspecialchars($data['apellido_materno'] ?? '').'">';
    echo '<input type="hidden" name="domicilio" value="'.htmlspecialchars($data['domicilio'] ?? '').'">';
    echo '<input type="hidden" name="telefono" value="'.htmlspecialchars($data['telefono'] ?? '').'">';
    echo '<input type="hidden" name="edad" value="'.htmlspecialchars($data['edad'] ?? '').'">';
    echo '<input type="hidden" name="antecedentes_medicos" value="'.htmlspecialchars($data['antecedentes'] ?? '').'">';
}
?>

<div class="page-header">
    <h1>Revisar Posibles Duplicados</h1>
    <p>Estás intentando crear un paciente, pero hemos encontrado las siguientes coincidencias.</p>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-body">
            
            <h3>Paciente que intentas crear:</h3>
            <div class="data-grid highlight-new">
                <div class="data-item full"><strong>Nombre:</strong> <?= htmlspecialchars(formatPatientName($newPatientData)) ?></div>
                <div class="data-item half"><strong>Teléfono:</strong> <?= htmlspecialchars($newPatientData['telefono'] ?? 'N/A') ?></div>
                <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($newPatientData['domicilio'] ?? 'N/A') ?></div>
            </div>

            <hr style="margin: 2rem 0;">

            <h3>Coincidencias Encontradas (<?= count($duplicates) ?>):</h3>
            
            <?php foreach ($duplicates as $patient): ?>
                <div class="data-grid highlight-existing" style="margin-bottom: 1.5rem;">
                    
                    <div class="data-item full"><strong>Nombre:</strong> <?= htmlspecialchars(formatPatientName($patient)) ?></div>
                    <div class="data-item half"><strong>Teléfono:</strong> <?= htmlspecialchars($patient['telefono'] ?? 'N/A') ?></div>
                    <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($patient['domicilio'] ?? 'N/A') ?></div>
                </div>
            <?php endforeach; ?>

        </div> <div class="card-footer" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: flex-start;">
            
            <form action="/patient_handler.php?action=force_update" method="POST" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $firstDuplicateId ?>">
                <?php renderNewPatientHiddenFields($newPatientData); ?>
                
                <button type="submit" class="btn btn-primary" title="Conserva solo los nuevos datos capturados">
                    Actualizar Paciente
                </button>
            </form>

            <a href="/index.php?page=patients_details&id=<?= $firstDuplicateId ?>" class="btn btn-secondary" title="Se conserva paciente sin cambios">
                Descartar datos nuevos
            </a>

            <form action="/patient_handler.php?action=force_create" method="POST" style="display:inline-block;">
                <?php renderNewPatientHiddenFields($newPatientData); ?>
                
                <button type="submit" class="btn btn-success" title="Se crea paciente capturado">
                    Crear paciente nuevo
                </button>
            </form>

            <a href="/index.php?page=patients" class="btn btn-secondary" title="Se regresa a lista de pacientes sin hacer cambios">
                Cancelar
            </a>
        
        </div> </div> </div>