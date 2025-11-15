<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/PatientController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Le decimos al controlador qué acción ejecutar
$_GET['action'] = 'details';

// 3. Llamamos al controlador
$data = handlePatientAction(); 

// 4. Verificamos y desempaquetamos los datos
if (!$data || !$data['patient']) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}
$patient = $data['patient'];
$resumenConsultas = $data['resumen'];

// 5. Creamos las variables que la vista necesita
$patientId = $patient['id'];
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
?>

<div class="page-header">
    <h1>Expediente: <?= htmlspecialchars($fullName) ?></h1>
    <div class="view-actions">
        <a href="/index.php?page=patients_edit&id=<?= $patientId ?>" class="btn btn-primary">Editar Paciente</a>
        <a href="/index.php?page=patients_delete&id=<?= $patientId ?>" class="btn btn-danger">Borrar Paciente</a>
        <a href="/index.php?page=patients" class="btn btn-secondary">Volver a la Lista</a>
    </div>
</div>

<div class="page-content">

    <div class="card">
        <div class="card-header">
            <h3>Información General</h3>
        </div>
        <div class="card-body">
            <div class="data-grid">
                <div class="data-item full"><strong>Nombre Completo:</strong> <?= htmlspecialchars($fullName) ?></div>
                <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($patient['domicilio'] ?? 'No especificado') ?></div>
                <div class="data-item quarter"><strong>Teléfono:</strong> <?= htmlspecialchars($patient['telefono'] ?? 'No especificado') ?></div>
                <div class="data-item quarter"><strong>Edad:</strong> <?= htmlspecialchars($patient['edad'] ?? 'No especificada') ?></div>
                <div class="data-item full"><strong>Antecedentes Médicos:</strong><br><?= nl2br(htmlspecialchars($patient['antecedentes_medicos'] ?? 'Sin antecedentes')) ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Resumen de Consultas Recientes</h3>
        </div>
        <div class="card-body">
            
            <?php if (empty($resumenConsultas)): ?>
                
                <p style="text-align: center;">Este paciente aún no tiene consultas registradas.</p>
            
            <?php else: ?>
                
                <table class="consultation-summary-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Graduación Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resumenConsultas as $consulta): ?>
                            <tr>
                                <td><?= FormatHelper::dateFull($consulta['fecha']) ?></td>
                                <td>
                                    <div class="graduacion-display">
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OD</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_esfera'] ?? '0.00') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_cilindro'] ?? '0.00') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_eje'] ?? '0') ?></span>
                                            <span class="simbolo">°</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['od_adicion'] ?? '0.00') ?></span>
                                        </div>
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OI</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_esfera'] ?? '0.00') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_cilindro'] ?? '0.00') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_eje'] ?? '0') ?></span>
                                            <span class="simbolo">°</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['oi_adicion'] ?? '0.00') ?></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </div>
        <div class="card-footer"> <a href="/index.php?page=consultas_index&patient_id=<?= $patientId ?>&tab=consults" class="btn btn-primary">
                Administrar Consultas del Paciente
            </a>
        </div>
    </div>
</div>