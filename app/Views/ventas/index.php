<?php
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'index';
$data = handleVentaAction();
$ventas = $data['ventas'];
$activeTab = $data['activeTab']; // 'recent', 'search', o 'dates'

// --- FUNCIÓN HELPER INTERNA PARA DIBUJAR LA TABLA ---
// Esto evita repetir el código de la tabla 3 veces
function renderSalesTable($ventas) {
    if (empty($ventas)) {
        echo '<div class="alert alert-secondary text-center">No se encontraron ventas con estos criterios.</div>';
        return;
    }
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
        $nombreCompleto = implode(' ', array_filter([$venta['nombre'], $venta['apellido_paterno'], $venta['apellido_materno']]));
        $estadoClass = $venta['estado_pago'] === 'pagado' ? 'badge-success' : 'badge-danger';
        $sufijo = $venta['numero_nota_sufijo'] ? ' <small>('.htmlspecialchars($venta['numero_nota_sufijo']).')</small>' : '';
        $fecha = FormatHelper::dateFull($venta['fecha_venta']);
        $total = number_format($venta['costo_total'], 2);
        $estado = ucfirst($venta['estado_pago']);
        
        echo "<tr>
                <td><strong>{$venta['numero_nota']}</strong>{$sufijo}</td>
                <td>{$fecha}</td>
                <td><a href='/index.php?page=patients_details&id={$venta['id_paciente']}'>" . htmlspecialchars($nombreCompleto) . "</a></td>
                <td>\${$total}</td>
                <td><span class='badge {$estadoClass}'>{$estado}</span></td>
                <td class='actions-cell'>
                    <a href='/index.php?page=ventas_details&id={$venta['id_venta']}&patient_id={$venta['id_paciente']}' class='btn btn-secondary btn-sm'>Ver Nota</a>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
}
?>

<div class="page-header">
    <h1>Reporte General de Ventas</h1>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-header view-actions">
            <button class="btn btn-secondary <?= $activeTab === 'recent' ? 'active' : '' ?>" data-view="recent">Recientes</button>
            <button class="btn btn-secondary <?= $activeTab === 'search' ? 'active' : '' ?>" data-view="search">Buscador</button>
            <button class="btn btn-secondary <?= $activeTab === 'dates' ? 'active' : '' ?>" data-view="dates">Por Fechas</button>
        </div>

        <div class="card-body">
            
            <div id="view-recent" class="view-panel <?= $activeTab === 'recent' ? 'active' : '' ?>">
                <h3>Últimas 50 Ventas</h3>
                <?php if($activeTab === 'recent') renderSalesTable($ventas); ?>
            </div>

            <div id="view-search" class="view-panel <?= $activeTab === 'search' ? 'active' : '' ?>">
                
                <form action="/index.php" method="GET" style="margin-bottom: 2rem;">
                    <input type="hidden" name="page" value="ventas_index">
                    <input type="hidden" name="tab" value="search"> <div class="search-bar">
                        <input type="text" name="q" placeholder="Buscar por Folio, Nombre o Apellidos..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>

                <?php if($activeTab === 'search') renderSalesTable($ventas); ?>
            </div>

            <div id="view-dates" class="view-panel <?= $activeTab === 'dates' ? 'active' : '' ?>">
                
                <form action="/index.php" method="GET" style="margin-bottom: 2rem;">
                    <input type="hidden" name="page" value="ventas_index">
                    <input type="hidden" name="tab" value="dates"> <div class="form-row" style="align-items: flex-end;">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" name="date_start" value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" name="date_end" value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>" required>
                        </div>
                        <div class="form-group" style="flex-grow: 0;">
                            <button type="submit" class="btn btn-primary" style="margin-bottom: 0.2rem;">Filtrar</button>
                        </div>
                    </div>
                </form>

                <?php if($activeTab === 'dates') renderSalesTable($ventas); ?>
            </div>

        </div>
    </div>
</div>