<?php
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$data = handleConsultaAction(); 
$paciente = $data['paciente'];
$consultas = $data['consultas'];

if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));

// Filter out non-Refractive consultations (Legacy module is for Lenses only)
$consultas = array_filter($consultas, function($c) {
    return $c['motivo_consulta'] === 'Refractiva';
});
?>

<div class="page-header">
    <h1>
        <small>Expediente de:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=patients_details&id=<?= $paciente['id'] ?>&tab=consults" class="btn btn-secondary">
            &larr; Volver al Expediente
        </a>
        <a href="/index.php?page=consultas_create&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary">
            ➕ Registrar Nueva Consulta
        </a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Historial de Consultas</h3>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th class="th-fecha">Fecha</th>
                        <th>Tipo / Motivo</th>
                        <th>Graduación / Dx</th>
                        <th class="th-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consultas)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron consultas para este paciente.</td>
                        </tr>
                    <?php else: ?>
                        
                        <?php foreach ($consultas as $consulta): ?>
                            <tr>
                                <td><?= FormatHelper::dateFull($consulta['fecha']) ?></td>
                                
                                <td>
                                    <strong><?= htmlspecialchars($consulta['motivo_consulta']) ?></strong><br>
                                    <small class="text-secondary"><?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?></small>
                                </td>

                                <td>
                                    <div class="graduacion-display">
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OD</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_esfera'] ?? '-') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_cilindro'] ?? '-') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_eje'] ?? '-') ?></span>
                                            <span class="simbolo">°</span>
                                        </div>
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OI</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_esfera'] ?? '-') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_cilindro'] ?? '-') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_eje'] ?? '-') ?></span>
                                            <span class="simbolo">°</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="actions-cell">
                                    <a href="/index.php?page=graduaciones_index&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary btn-sm">
                                        Graduaciones
                                    </a>
                                    
                                    <a href="/index.php?page=consultas_edit&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">
                                        Editar
                                    </a>

                                    <a href="/index.php?page=consultas_delete&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-danger btn-sm">
                                        Eliminar
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>