<?php
require_once __DIR__ . '/../../Controllers/AbonoController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// Reutilizamos 'edit' para buscar los datos (necesitamos saber qué vamos a borrar)
$_GET['action'] = 'edit';
$data = handleAbonoAction();

$abono = $data['abono'];
$venta = $data['venta'];
$paciente = $data['paciente'];

if (!$abono) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>Confirmar Borrado de Pago</h1>
</div>

<div class="context-header">
    <div class="card-body">
        <h3>Paciente: <?= htmlspecialchars($fullName) ?></h3>
        <h3>Nota: #<?= htmlspecialchars($venta['numero_nota']) ?></h3>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            
            <p class="emphasis-text">
                ¿Estás seguro de que quieres eliminar el pago de 
                <strong>$<?= number_format($abono['monto'], 2) ?></strong> 
                realizado el <strong><?= FormatHelper::dateFull($abono['fecha']) ?></strong>?
            </p>
            
            <div class="alert alert-danger">
                <strong>Advertencia:</strong> Esta acción eliminará el registro del dinero y afectará el cálculo del saldo pendiente.
            </div>

            <form action="/abono_handler.php?action=delete" method="POST">
                <input type="hidden" name="id_abono" value="<?= $abono['id_abono'] ?>">
                <input type="hidden" name="venta_id" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                
                <div class="form-actions view-actions">
                    <button type="submit" class="btn btn-danger">Sí, eliminar pago</button>
                    
                    <a href="/index.php?page=ventas_details&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</div>