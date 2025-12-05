<?php
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'details';
$data = handleVentaAction();

// Desempaquetamos
$paciente = $data['paciente'];
$venta = $data['venta'];
$abonos = $data['abonos'];
$totalPagado = $data['totalPagado'];

// Cálculos en vivo
$saldoPendiente = $venta['costo_total'] - $totalPagado;
$estadoPago = ($saldoPendiente <= 0) ? 'Pagado' : 'Pendiente';
?>

<div class="page-header">
    <h1>
        <small>Nota de Venta #<?= htmlspecialchars($venta['numero_nota']) ?></small><br>
        Paciente: <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=ventas_edit&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary">
            Editar Nota
        </a>
        
        <a href="/index.php?page=ventas_delete&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-danger">
            Eliminar Nota
        </a>

        <a href="/index.php?page=patients_details&id=<?= $paciente['id'] ?>&tab=ventas" class="btn btn-secondary">
            &larr; Volver al Expediente
        </a>
    </div>
</div>

<div class="page-content">
    
    <div class="card mb-2">
        <div class="card-header">
            <h3>Detalles de la Venta</h3>
        </div>
        <div class="card-body">
            <div class="data-grid">
                <div class="data-item quarter"><strong>Fecha:</strong> <?= FormatHelper::dateFull($venta['fecha_venta']) ?></div>
                <div class="data-item quarter"><strong>Total:</strong> $<?= number_format($venta['costo_total'], 2) ?></div>
                <div class="data-item quarter"><strong>Pagado:</strong> $<?= number_format($totalPagado, 2) ?></div>
                <div class="data-item quarter">
                    <strong>Saldo:</strong> 
                   <span class="fw-bold <?= $saldoPendiente > 0 ? 'text-danger' : 'text-success' ?>">
                        $<?= number_format($saldoPendiente, 2) ?>
                   </span>
                </div>

                <div class="data-item full">
                    <strong>Descripción de Productos / Observaciones:</strong><br>
                    <p class="observation-text"><?= htmlspecialchars($venta['observaciones_venta'] ?? 'Sin detalles registrados.') ?></p>
                </div>
                <?php if (!empty($venta['vendedor_armazon'])): ?>
                    <div class="data-item full">
                        <strong>Vendedor (Comisión Armazón):</strong> 
                        <?= htmlspecialchars($venta['vendedor_armazon']) ?>
                    </div>
                <?php endif; ?>
                </div> 

                <div class="data-item full">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-between align-center">
            <h3>Historial de Pagos</h3>
            
            <?php if ($saldoPendiente > 0): ?>
                <a href="/index.php?page=abonos_create&venta_id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-success btn-sm">
                    Registrar Abono
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($abonos)): ?>
                        <tr><td colspan="3" class="text-center">No hay pagos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($abonos as $abono): ?>
                            <tr>
                                <td><?= FormatHelper::dateFull($abono['fecha']) ?></td>
                                <td>$<?= number_format($abono['monto'], 2) ?></td>
                                <td>
                                    <span class="badge-neutral">
                                        <?= htmlspecialchars($abono['metodo_pago']) ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <a href="/index.php?page=abonos_edit&id=<?= $abono['id_abono'] ?>&venta_id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">
                                        Editar
                                    </a>
                                    <a href="/index.php?page=abonos_delete&id=<?= $abono['id_abono'] ?>&venta_id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-danger btn-sm">
                                        Borrar
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