<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Reutilizamos la lógica de 'details' para buscar los datos de la venta
$_GET['action'] = 'details'; 
$data = handleVentaAction();

// 3. Desempaquetamos los datos necesarios
$paciente = $data['paciente'];
$venta = $data['venta'];

// 4. (Seguridad)
if (!$paciente || !$venta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Formato
$fechaVenta = FormatHelper::dateFull($venta['fecha_venta']);
$total = number_format($venta['costo_total'], 2);
?>

<div class="page-header">
    <h1>Confirmar Borrado de Venta</h1>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            
            <p class="emphasis-text">
                ¿Estás seguro de que quieres borrar permanentemente la <strong>Nota #<?= htmlspecialchars($venta['numero_nota']) ?></strong> 
                del <?= $fechaVenta ?> por un total de <strong>$<?= $total ?></strong>?
            </p>
            
            <div class="alert alert-danger">
                <strong>Advertencia:</strong> Esta acción es irreversible. Se eliminará la venta, sus detalles de productos y <strong>todo el historial de abonos</strong> asociados.
            </div>

            <form action="/venta_handler.php?action=delete" method="POST">
                
                <input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>"> 
                
                <div class="form-actions view-actions">
                    <button type="submit" class="btn btn-danger">Sí, borrar permanentemente</button>
                    
                    <a href="/index.php?page=ventas_details&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</div>