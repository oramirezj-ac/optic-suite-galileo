<?php
/* ==========================================================================
   VISTA: REVISIÓN DE PACIENTES DUPLICADOS PARA CLÍNICA
   ========================================================================== */

// 1. Incluimos dependencias
require_once __DIR__ . '/../../Controllers/PatientController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Obtenemos los datos del paciente NUEVO de la sesión
if (!isset($_SESSION['new_patient_data'])) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Error de sesión, intente de nuevo.'));
    exit();
}
$newPatientData = $_SESSION['new_patient_data'];

// 3. Volvemos a ejecutar la búsqueda de duplicados
$pdo = getConnection();
$patientModel = new PatientModel($pdo);
$duplicates = $patientModel->findSimilar($newPatientData);

if (empty($duplicates)) {
    // No hay duplicados, crear paciente y continuar con wizard
    $newPatientId = $patientModel->create($newPatientData);
    if ($newPatientId) {
        unset($_SESSION['new_patient_data']);
        header('Location: /index.php?page=clinica_index&patient_id=' . $newPatientId . '&success=created');
    } else {
        header('Location: /index.php?page=clinica_index&error=' . urlencode('Error al crear paciente.'));
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

?>

<div class="page-header">
    <h1>Revisar Posibles Duplicados</h1>
    <p>Estás creando una consulta, pero hemos encontrado pacientes similares en el sistema.</p>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-body">
            
            <h3>Paciente que intentas registrar:</h3>
            <div class="data-grid highlight-new">
                <div class="data-item full"><strong>Nombre:</strong> <?= htmlspecialchars(formatPatientName($newPatientData)) ?></div>
                <div class="data-item half"><strong>Teléfono:</strong> <?= htmlspecialchars($newPatientData['telefono'] ?? 'N/A') ?></div>
                <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($newPatientData['domicilio'] ?? 'N/A') ?></div>
            </div>

            <hr class="section-divider">

            <h3>Coincidencias Encontradas (<?= count($duplicates) ?>):</h3>
            
            <?php foreach ($duplicates as $patient): ?>
                <div class="data-grid highlight-existing mb-1-5">
                    
                    <div class="data-item full"><strong>Nombre:</strong> <?= htmlspecialchars(formatPatientName($patient)) ?></div>
                    <div class="data-item half"><strong>Teléfono:</strong> <?= htmlspecialchars($patient['telefono'] ?? 'N/A') ?></div>
                    <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($patient['domicilio'] ?? 'N/A') ?></div>
                    
                    <div class="data-item half">
                        <strong>Edad:</strong> 
                        <?= !empty($patient['fecha_nacimiento']) ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) . ' años' : 'N/A' ?>
                    </div>
                    
                    <div class="data-item full">
                        <div class="actions-cell actions-cell-end">
                            
                            <a href="/index.php?page=clinica_index&patient_id=<?= $patient['id'] ?>" 
                               class="btn btn-secondary" 
                               title="Descartar captura actual y usar este paciente existente">
                                Usar este (Descartar cambios)
                            </a>

                            <form action="/patient_handler.php?action=force_update" method="POST" class="inline-block">
                                <input type="hidden" name="id" value="<?= $patient['id'] ?>">
                                <input type="hidden" name="redirect_to" value="wizard">
                                <?php \FormatHelper::renderNewPatientHiddenFields($newPatientData); ?>
                                
                                <button type="submit" class="btn btn-danger" title="Sobrescribir este registro con los datos nuevos">
                                    Actualizar este expediente
                                </button>
                            </form>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>

       </div>

       <div class="card-footer justify-start flex-wrap">
            
            <form action="/patient_handler.php?action=force_create" method="POST" class="inline-block">
                <input type="hidden" name="redirect_to" value="wizard">
                <?php \FormatHelper::renderNewPatientHiddenFields($newPatientData); ?>
                
                <button type="submit" class="btn btn-success" title="Ignorar coincidencias y crear registro nuevo">
                    Crear paciente nuevo
                </button>
            </form>

            <a href="/index.php?page=clinica_index" class="btn btn-secondary" title="Cancelar y volver al wizard">
                Cancelar
            </a>
        
        </div> 
    </div> 
</div>
