<?php
/* ==========================================================================
   CONSULTA MÉDICA - Ver Detalles
   ========================================================================== */

require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Models/PatientModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$consultaId = $_GET['id'] ?? null;

if (!$consultaId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Consulta no especificada'));
    exit();
}

$pdo = getConnection();
$consultaModel = new ConsultaModel($pdo);
$consulta = $consultaModel->getConsultaById($consultaId);

if (!$consulta) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Consulta no encontrada'));
    exit();
}

// Obtener información del paciente
$patientModel = new PatientModel($pdo);
$patient = $patientModel->getById($consulta['paciente_id']);

$fullName = implode(' ', array_filter([
    $patient['nombre'], 
    $patient['apellido_paterno'], 
    $patient['apellido_materno']
]));

// Obtener productos médicos vendidos en esta consulta
$productosMedicos = $consultaModel->getProductosMedicosByConsulta($consultaId);
?>

<div class="page-header">
    <h1>🏥 Detalles de Consulta Médica</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_medicas_index&patient_id=<?= $consulta['paciente_id'] ?>" class="btn btn-secondary">← Volver al Historial</a>
        <a href="/index.php?page=consultas_medicas_edit&id=<?= $consultaId ?>" class="btn btn-primary">✏️ Editar</a>
        <a href="/index.php?page=consultas_medicas_delete&id=<?= $consultaId ?>" class="btn btn-danger">🗑️ Eliminar</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Información del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <p><strong>Fecha de Consulta:</strong> <?= \FormatHelper::dateFull($consulta['fecha']) ?></p>
        </div>
    </div>
    
    <!-- Datos de la consulta -->
    <div class="card">
        <div class="card-body">
            <h3>Información de la Consulta</h3>
            
            <div class="detail-row">
                <strong>Tipo de Consulta:</strong>
                <span><?= htmlspecialchars($consulta['motivo_consulta']) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Motivo:</strong>
                <span><?= htmlspecialchars($consulta['detalle_motivo'] ?? 'Sin motivo especificado') ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Diagnóstico:</strong>
                <span><?= nl2br(htmlspecialchars($consulta['diagnostico_dx'] ?? 'Sin diagnóstico')) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Tratamiento:</strong>
                <span><?= nl2br(htmlspecialchars($consulta['tratamiento_rx'] ?? 'Sin tratamiento')) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Observaciones:</strong>
                <span><?= nl2br(htmlspecialchars($consulta['observaciones'] ?? 'Sin observaciones')) ?></span>
            </div>
        </div>
    </div>
    
    
    <!-- Información financiera -->
    <div class="card">
        <div class="card-body">
            <h3>Información de Cobro</h3>
            
            <div class="detail-row">
                <strong>Tipo de Consulta:</strong>
                <span>
                    <?php 
                    $estadosFinancieros = [
                        'cobrado' => '💰 Cobrado - Consulta pagada',
                        'cortesia' => '🎁 Cortesía - Sin costo (Familiar/Amigo)',
                        'garantia' => '🔄 Garantía/Seguimiento - Sin costo',
                        'pendiente' => '⏳ Pendiente - Por cobrar después'
                    ];
                    echo $estadosFinancieros[$consulta['estado_financiero'] ?? 'cobrado'] ?? 'No especificado';
                    ?>
                </span>
            </div>
            
            <div class="detail-row">
                <strong>Costo de Consulta:</strong>
                <span>$<?= number_format($consulta['costo_servicio'] ?? 0, 2) ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Método de Pago:</strong> 
                <span>
                    <?php 
                    $metodos = [
                        'efectivo' => 'Efectivo',
                        'transferencia' => 'Transferencia',
                        'tarjeta_debito' => 'Tarjeta Débito',
                        'tarjeta_credito' => 'Tarjeta Crédito',
                    ];
                    echo $metodos[$consulta['metodo_pago'] ?? ''] ?? 'No especificado';
                    ?>
                </span>
            </div>
            
            <?php if (!empty($productosMedicos)): ?>
            <hr>
            <h4>Medicamentos Vendidos</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalMedicamentos = 0;
                    foreach ($productosMedicos as $prod): 
                        $subtotal = $prod['cantidad'] * $prod['precio'];
                        $totalMedicamentos += $subtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['producto_nombre']) ?></td>
                        <td><?= $prod['cantidad'] ?></td>
                        <td>$<?= number_format($prod['precio'], 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="3"><strong>Total Medicamentos:</strong></td>
                        <td><strong>$<?= number_format($totalMedicamentos, 2) ?></strong></td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3"><strong>Total General:</strong></td>
                        <td><strong>$<?= number_format(($consulta['costo_servicio'] ?? 0) + $totalMedicamentos, 2) ?></strong></td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    
</div>
