<?php
/* ==========================================================================
   CONSULTAS DE LENTES - Historial del Paciente
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// Delegar la lógica al controlador
// Si hay una acción POST (store_av, etc), el controlador la procesará y redirigirá.
// Si es GET (index), el controlador devolverá los datos necesarios.
$data = handleConsultaLentesAction();

// Extraer datos del array devuelto por el controlador
$patient = $data['patient'] ?? null;
$consultas = $data['consultas'] ?? [];
$patientId = $patient['id'] ?? null;

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
?>

<div class="page-header">
    <h1>👓 Historial de Consultas de Lentes</h1>
    <div class="view-actions">
        <a href="/index.php?page=clinica_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Volver</a>
        <a href="/index.php?page=consultas_lentes_create&patient_id=<?= $patientId ?>" class="btn btn-primary">➕ Nueva Consulta</a>
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
            <h3>Consultas Refractivas Registradas</h3>
            
            <?php if (empty($consultas)): ?>
                <p class="text-center">Este paciente aún no tiene consultas de lentes registradas.</p>
            <?php else: ?>
                <table class="consultation-summary-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th>Graduación Final</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consultas as $consulta): ?>
                        <tr>
                            <td><?= \FormatHelper::dateFull($consulta['fecha']) ?></td>
                            <td><?= htmlspecialchars($consulta['detalle_motivo'] ?? 'Examen de vista') ?></td>
                            <td>
                                <?php if (isset($consulta['od_esfera'])): ?>
                                <div class="graduacion-display mini-graduacion">
                                    <div class="graduacion-formula">
                                        <span class="graduacion-ojo-label">OD</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['od_esfera'] ?? '') ?></span>
                                        <span class="simbolo">Sph</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['od_cilindro'] ?? '') ?></span>
                                        <span class="simbolo">Cyl</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['od_eje'] ?? '') ?></span>
                                        <span class="simbolo">°</span>
                                        <?php if (!empty($consulta['od_adicion'])): ?>
                                            <span class="simbolo">Add</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['od_adicion']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="graduacion-formula">
                                        <span class="graduacion-ojo-label">OI</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['oi_esfera'] ?? '') ?></span>
                                        <span class="simbolo">Sph</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['oi_cilindro'] ?? '') ?></span>
                                        <span class="simbolo">Cyl</span>
                                        <span class="valor"><?= htmlspecialchars($consulta['oi_eje'] ?? '') ?></span>
                                        <span class="simbolo">°</span>
                                        <?php if (!empty($consulta['oi_adicion'])): ?>
                                            <span class="simbolo">Add</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['oi_adicion']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <span class="text-muted">Sin graduación final</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions-cell">
                                <?php 
                                $graduacionesUrl = "/index.php?page=graduaciones_live_index&id=" . $consulta['consulta_id'] . "&patient_id=" . $patientId;
                                $editUrl = "/index.php?page=consultas_lentes_edit&id=" . $consulta['consulta_id'] . "&patient_id=" . $patientId;
                                $deleteUrl = "/index.php?page=consultas_lentes_delete&id=" . $consulta['consulta_id'] . "&patient_id=" . $patientId;
                                ?>
                                <a href="<?= $graduacionesUrl ?>" class="btn btn-primary btn-sm">Graduaciones</a>
                                <a href="<?= $editUrl ?>" class="btn btn-secondary btn-sm">Editar</a>
                                <a href="<?= $deleteUrl ?>" class="btn btn-danger btn-sm">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
</div>
