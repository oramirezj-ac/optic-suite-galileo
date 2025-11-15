<?php
// 1. Incluimos y ejecutamos el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
$data = handleConsultaAction(); // Esto nos da ['paciente' => ..., 'consultas' => ...]

// 2. Desempaquetamos los datos
$paciente = $data['paciente'];
$consultas = $data['consultas'];

// 3. (Seguridad)
if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

// 4. Creamos el nombre completo
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>
        <small>Expediente de:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="card-header view-actions">
        <a href="/index.php?page=patients_details&id=<?= $paciente['id'] ?>&tab=consults" class="btn btn-secondary">
            &larr; Volver al Paciente
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
                        <th class="th-graduacion">Graduación Final</th>
                        <th class="th-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consultas)): ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No se encontraron consultas para este paciente.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($consultas as $consulta): ?>
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
                                <td class="actions-cell">
                                    <a href="/index.php?page=consultas_edit&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
                                        Ver Detalles
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