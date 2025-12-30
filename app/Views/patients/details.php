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
$ventas = $data['ventas'] ?? [];

// 5. Filtramos solo consultas refractivas (lentes)
$resumenConsultas = array_filter($resumenConsultas, function($c) {
    return ($c['motivo_consulta'] ?? '') === 'Refractiva';
});

// 6. Creamos las variables que la vista necesita
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
        
        <div class="card-header view-actions">
            <button class="btn btn-secondary active" data-view="details">Detalles</button>
            <button class="btn btn-secondary" data-view="consults">Consultas</button>
            <button class="btn btn-secondary" data-view="ventas">Ventas</button>
            
            <!-- Accesos directos a módulos de consultas -->
            <a href="/index.php?page=consultas_medicas_index&patient_id=<?= $patientId ?>" 
               class="btn btn-outline-primary" 
               style="margin-left: auto;">
                🏥 Consultas Médicas
            </a>
            <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $patientId ?>" 
               class="btn btn-outline-success">
                👓 Consultas Lentes
            </a>
        </div>

        <div class="card-body">
            
            <div id="view-details" class="view-panel active">
                <h3>Información General</h3>
                <div class="data-grid">
                    <div class="data-item full"><strong>Nombre Completo:</strong> <?= htmlspecialchars($fullName) ?></div>
                    
                    <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($patient['domicilio'] ?? 'No especificado') ?></div>
                    <div class="data-item quarter"><strong>Teléfono:</strong> <?= htmlspecialchars($patient['telefono'] ?? 'No especificado') ?></div>
                    
                    <div class="data-item quarter">
                        <strong>Edad Actual:</strong> 
                        <?= FormatHelper::calculateAge($patient['fecha_nacimiento']) ?>
                    </div>
                    
                    <div class="data-item full"><strong>Antecedentes Médicos:</strong><br><?= nl2br(htmlspecialchars($patient['antecedentes_medicos'] ?? 'Sin antecedentes')) ?></div>

                    <div class="data-item full">
                        <small class="text-secondary">
                            Fecha de 1ª Visita/Alta: <?= FormatHelper::dateFull($patient['fecha_primera_visita']) ?>
                        </small>
                    </div>
                </div>
            </div>

            <div id="view-consults" class="view-panel">
                <h3>Resumen de Consultas Recientes</h3>
                
                <?php if (empty($resumenConsultas)): ?>
                    <p class="text-center">Este paciente aún no tiene consultas registradas.</p>
                <?php else: ?>
                    
                    <?php 
                    // Header de Biometría (Última Consulta)
                    // Re-indexar el array después del filtro
                    $resumenConsultas = array_values($resumenConsultas);
                    $ultimaConsulta = $resumenConsultas[0];
                    $tieneBiometria = !empty($ultimaConsulta['dp_lejos_total']) || !empty($ultimaConsulta['altura_oblea']);
                    ?>

                    <?php if ($tieneBiometria): ?>
                        <div class="info-box">
                            <strong class="info-box-title">Últimos Datos Biométricos:</strong>
                            <div class="info-box-content">
                                <?php if($ultimaConsulta['dp_lejos_total']): ?>
                                    <div>
                                        <strong>DP:</strong> <?= htmlspecialchars($ultimaConsulta['dp_lejos_total']) ?> mm
                                        <small class="text-muted">(<?= htmlspecialchars($ultimaConsulta['dp_od']) ?> / <?= htmlspecialchars($ultimaConsulta['dp_oi']) ?>)</small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if($ultimaConsulta['altura_oblea']): ?>
                                    <div>
                                        <strong>Altura:</strong> <?= htmlspecialchars($ultimaConsulta['altura_oblea']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

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

                <div class="card-footer">
                    <a href="/index.php?page=consultas_index&patient_id=<?= $patientId ?>" class="btn btn-primary">
                        Administrar Consultas del Paciente
                    </a>
                </div>
            </div>

            <div id="view-ventas" class="view-panel">
                <h3>Historial de Ventas</h3>
                
                <?php if (empty($ventas)): ?>
                    <p class="text-center">Este paciente no tiene ventas registradas.</p>
                <?php else: ?>
                    <table class="consultation-summary-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Nota #</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?= FormatHelper::dateFull($venta['fecha_venta']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($venta['numero_nota']) ?></strong>
                                        <?= $venta['numero_nota_sufijo'] ? ' (' . htmlspecialchars($venta['numero_nota_sufijo']) . ')' : '' ?>
                                    </td>
                                    <td>$<?= number_format($venta['costo_total'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $venta['estado_pago'] === 'pagado' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= ucfirst($venta['estado_pago']) ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <a href="/index.php?page=ventas_details&id=<?= $venta['id_venta'] ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary btn-sm">
                                            Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div class="card-footer">
                    <a href="/index.php?page=ventas_create&patient_id=<?= $patientId ?>" class="btn btn-primary">
                        ➕ Registrar Nueva Venta
                    </a>
                </div>
            </div>

        </div> </div>
</div>