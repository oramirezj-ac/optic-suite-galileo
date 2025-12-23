<?php
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 1. Ejecutamos el controlador
$_GET['action'] = 'index';
$data = handleVentaAction();

// 2. Desempaquetamos datos
$ventas = $data['ventas'] ?? [];
$activeTab = $data['activeTab'] ?? 'recent';

// Datos específicos para la pestaña de Auditoría
$auditResults = $data['auditResults'] ?? [];
$folioStart = $data['folioStart'] ?? '';
$folioEnd = $data['folioEnd'] ?? '';

// --- HELPER LOCAL PARA TABLA DE VENTAS ---
function renderSalesTable($ventas) {
    if (empty($ventas)) {
        echo '<div class="alert alert-secondary text-center">No se encontraron ventas con los criterios seleccionados.</div>';
        return;
    }

    echo '<p class="text-muted mb-1">Mostrando ' . count($ventas) . ' resultados.</p>';
    
    echo '<table>
            <thead>
                <tr>
                    <th>Nota #</th>
                    <th>Fecha</th>
                    <th>Paciente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($ventas as $venta) {
        $nombreCompleto = implode(' ', array_filter([
            $venta['nombre'] ?? '', 
            $venta['apellido_paterno'] ?? '', 
            $venta['apellido_materno'] ?? ''
        ]));
        
        $estadoPago = $venta['estado_pago'] ?? 'pendiente';
        $estadoClass = $estadoPago === 'pagado' ? 'badge-success' : 'badge-danger';
        $sufijo = !empty($venta['numero_nota_sufijo']) ? ' <small>('.htmlspecialchars($venta['numero_nota_sufijo']).')</small>' : '';
        
        $fecha = FormatHelper::dateFull($venta['fecha_venta']);
        $total = FormatHelper::money($venta['costo_total']);
        $estado = ucfirst($estadoPago);
        
        $idVenta = $venta['id_venta'] ?? $venta['id'];
        $idPaciente = $venta['id_paciente'] ?? $venta['patient_id'];

        echo "<tr>
                <td><strong>{$venta['numero_nota']}</strong>{$sufijo}</td>
                <td>{$fecha}</td>
                <td><a href='/index.php?page=patients_details&id={$idPaciente}'>" . htmlspecialchars($nombreCompleto) . "</a></td>
                <td>{$total}</td>
                <td><span class='badge {$estadoClass}'>{$estado}</span></td>
                <td class='actions-cell'>
                    <a href='/index.php?page=ventas_details&id={$idVenta}&patient_id={$idPaciente}' class='btn btn-secondary btn-sm'>Ver Nota</a>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
}
?>

<div class="page-header">
    <h1>Reporte General de Ventas</h1>
    <div class="view-actions">
        <a href="/index.php?page=patients" class="btn btn-primary">➕ Nueva Venta</a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-header view-actions">
            <a href="/index.php?page=ventas_index&tab=recent" class="btn btn-secondary <?= $activeTab === 'recent' ? 'active' : '' ?>">Recientes (50)</a>
            <a href="/index.php?page=ventas_index&tab=all" class="btn btn-secondary <?= $activeTab === 'all' ? 'active' : '' ?>">Todas</a>
            <a href="/index.php?page=ventas_index&tab=search" class="btn btn-secondary <?= $activeTab === 'search' ? 'active' : '' ?>">Buscador</a>
            <a href="/index.php?page=ventas_index&tab=dates" class="btn btn-secondary <?= $activeTab === 'dates' ? 'active' : '' ?>">Por Fechas</a>
            <a href="/index.php?page=ventas_index&tab=audit" class="btn btn-secondary <?= $activeTab === 'audit' ? 'active' : '' ?>">Auditoría de Folios</a>
        </div>

        <div class="card-body">
            
            <?php if($activeTab === 'recent'): ?>
                <h3>Últimas 50 Ventas</h3>
                <?php renderSalesTable($ventas); ?>
            <?php endif; ?>

            <?php if($activeTab === 'all'): ?>
                <h3>Historial Completo de Ventas</h3>
                <?php renderSalesTable($ventas); ?>
            <?php endif; ?>

            <?php if($activeTab === 'search'): ?>
                <form action="/index.php" method="GET" class="mb-2">
                    <input type="hidden" name="page" value="ventas_index">
                    <input type="hidden" name="tab" value="search">
                    <div class="search-bar">
                        <input type="text" name="q" placeholder="Buscar por Folio, Nombre o Apellidos..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
                <?php if(isset($_GET['q'])) renderSalesTable($ventas); ?>
            <?php endif; ?>

            <?php if($activeTab === 'dates'): ?>
                <form action="/index.php" method="GET" class="mb-2">
                    <input type="hidden" name="page" value="ventas_index">
                    <input type="hidden" name="tab" value="dates">
                    <div class="form-row align-items-end">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" name="date_start" value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" name="date_end" value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>" required>
                        </div>
                        <div class="form-group flex-no-grow">
                            <button type="submit" class="btn btn-primary mb-02">Filtrar</button>
                        </div>
                    </div>
                </form>
                <?php if(isset($_GET['date_start'])) renderSalesTable($ventas); ?>
            <?php endif; ?>

            <?php if($activeTab === 'audit'): ?>
                <h3>Auditoría de Secuencia de Folios</h3>
                <p class="text-secondary mb-1">Detecta notas faltantes (huecos) y duplicados en un rango específico.</p>
                
                <form action="/index.php" method="GET" class="mb-2 form-toolbar">
                    <input type="hidden" name="page" value="ventas_index">
                    <input type="hidden" name="tab" value="audit">
                    
                    <div class="form-row align-items-end">
                        <div class="form-group">
                            <label>Folio Inicial</label>
                            <input type="number" name="folio_start" placeholder="Ej. 100" value="<?= htmlspecialchars($folioStart) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Folio Final</label>
                            <input type="number" name="folio_end" placeholder="Ej. 150" value="<?= htmlspecialchars($folioEnd) ?>" required>
                        </div>
                        <div class="form-group flex-no-grow">
                            <button type="submit" class="btn btn-primary">Auditar Rango</button>
                        </div>
                    </div>
                </form>

                <?php if(!empty($auditResults)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Estado</th>
                                <th>Detalle / Paciente</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($auditResults as $row): ?>
                                <?php 
                                    // Lógica de visualización limpia
                                    $rowClass = '';
                                    $statusBadge = '';
                                    $detalle = '';
                                    $accion = '';
                                    
                                    if ($row['status'] === 'missing') {
                                        $rowClass = 'row-missing'; // Clase CSS
                                        $statusBadge = '<span class="badge badge-danger">FALTANTE</span>';
                                        $detalle = '<span>Este folio no existe en la base de datos.</span>';
                                        $accion = '<a href="/index.php?page=patients_create" class="btn btn-secondary btn-sm">Capturar</a>';

                                    } elseif ($row['status'] === 'duplicate') {
                                        $rowClass = 'row-duplicate'; // Clase CSS
                                        $statusBadge = '<span class="badge badge-warning">DUPLICADO</span>';
                                        
                                        $v = $row['data'];
                                        $paciente = htmlspecialchars(implode(' ', array_filter([$v['nombre'], $v['apellido_paterno']])));
                                        $sufijo = $v['numero_nota_sufijo'] ? ' ('.$v['numero_nota_sufijo'].')' : '';
                                        $monto = FormatHelper::money($v['costo_total']);
                                        
                                        $detalle = "<strong>Expediente:</strong> $paciente <br> <small>Sufijo: $sufijo | Total: $monto</small>";
                                        $accion = '<a href="/index.php?page=ventas_details&id='.$v['id_venta'].'&patient_id='.$v['id_paciente'].'" class="btn btn-secondary btn-sm">Ver</a>';

                                    } else {
                                        // OK
                                        $statusBadge = '<span class="badge badge-success">OK</span>';
                                        $v = $row['data'];
                                        $paciente = htmlspecialchars(implode(' ', array_filter([$v['nombre'], $v['apellido_paterno']])));
                                        $detalle = "$paciente";
                                        $accion = '<a href="/index.php?page=ventas_details&id='.$v['id_venta'].'&patient_id='.$v['id_paciente'].'" class="btn btn-secondary btn-sm">Ver</a>';
                                    }
                                ?>
                                
                                <tr class="<?= $rowClass ?>">
                                    <td><strong><?= $row['folio'] ?></strong></td>
                                    <td><?= $statusBadge ?></td>
                                    <td><?= $detalle ?></td>
                                    <td><?= $accion ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif(isset($_GET['folio_start'])): ?>
                    <div class="alert alert-secondary">No hay resultados (huecos o duplicados) para mostrar en este rango.</div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>