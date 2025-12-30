<?php
/* ==========================================================================
   CONSULTAS MÉDICAS - Historial del Paciente
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaMedicaController.php';
require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
    exit();
}

$pdo = getConnection();
$patientModel = new PatientModel($pdo);
$patient = $patientModel->getById($patientId);

if (!$patient) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no encontrado'));
    exit();
}

$fullName = implode(' ', array_filter([
    $patient['nombre'], 
    $patient['apellido_paterno'], 
    $patient['apellido_materno']
]));
$edad = $patient['fecha_nacimiento'] ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) : 'Sin datos';

// Obtener historial de consultas médicas del paciente
$consultaModel = new ConsultaModel($pdo);
$todasConsultas = $consultaModel->getAllByPaciente($patientId);

// Filtrar solo consultas médicas (motivo_consulta = 'Médica')
$consultas = array_filter($todasConsultas, function($c) {
    return $c['motivo_consulta'] === 'Médica';
});

// Ordenar por fecha descendente (más recientes primero)
usort($consultas, function($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});
?>

<div class="page-header">
    <h1>🏥 Historial de Consultas Médicas</h1>
    <div class="view-actions">
        <a href="/index.php?page=clinica_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Volver</a>
        <a href="/index.php?page=consultas_medicas_create&patient_id=<?= $patientId ?>" class="btn btn-primary">➕ Nueva Consulta</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Información del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <div class="patient-info-details">
                <span><strong>Edad:</strong> <?= $edad === 'Sin datos' ? $edad : $edad . ' años' ?></span>
                <span><strong>Fecha de 1ª Visita:</strong> <?= \FormatHelper::dateFull($patient['fecha_primera_visita']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Historial de consultas -->
    <div class="card">
        <div class="card-body">
            <h3>Consultas Médicas Registradas</h3>
            
            <?php if (empty($consultas)): ?>
                <p class="text-center">Este paciente aún no tiene consultas médicas registradas.</p>
            <?php else: ?>
                <table class="consultation-summary-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th>Diagnóstico</th>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultas as $consulta): ?>
                        <tr>
                            <td><?= \FormatHelper::dateFull($consulta['fecha']) ?></td>
                            <td><?= htmlspecialchars($consulta['detalle_motivo'] ?? 'Sin motivo') ?></td>
                            <td><?= htmlspecialchars(substr($consulta['diagnostico_dx'] ?? 'Sin diagnóstico', 0, 50)) ?>...</td>
                            <td>
                                <?php 
                                $estadosIcons = [
                                    'cobrado' => '💰',
                                    'cortesia' => '🎁',
                                    'garantia' => '🔄',
                                    'pendiente' => '⏳'
                                ];
                                echo $estadosIcons[$consulta['estado_financiero'] ?? 'cobrado'] ?? '💰';
                                ?>
                            </td>
                            <td class="actions-cell">
                                <?php 
                                $detailsUrl = "/index.php?page=consultas_medicas_details&id=" . $consulta['consulta_id'];
                                $editUrl = "/index.php?page=consultas_medicas_edit&id=" . $consulta['consulta_id'];
                                ?>
                                <a href="<?= $detailsUrl ?>" class="btn btn-secondary btn-sm">Ver</a>
                                <a href="<?= $editUrl ?>" class="btn btn-primary btn-sm">Editar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
</div>
