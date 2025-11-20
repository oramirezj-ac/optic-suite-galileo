<?php
// 1. Incluimos el controlador y helpers
require_once __DIR__ . '/../../Controllers/AbonoController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Forzamos la acción 'create' para obtener los datos de contexto
$_GET['action'] = 'create'; 
$data = handleAbonoAction();

// 3. Desempaquetamos
$paciente = $data['paciente'];
$venta = $data['venta'];
$saldoPendiente = $data['saldoPendiente'];

// 4. Seguridad
if (!$paciente || !$venta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>Registrar Nuevo Abono</h1>
    <div class="view-actions">
        <a href="/index.php?page=ventas_details&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>

<div class="context-header">
    <div class="card-body">
        <h3>Paciente: <?= htmlspecialchars($fullName) ?></h3>
        <h3>Nota: #<?= htmlspecialchars($venta['numero_nota']) ?></h3>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Saldo Pendiente: $<?= number_format($saldoPendiente, 2) ?></h3>
        </div>
        <div class="card-body">
            
            <form action="/abono_handler.php?action=store" method="POST">
                
                <input type="hidden" name="venta_id" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="monto">Monto a Pagar ($)</label>
                        <input type="number" id="monto" name="monto" step="0.01" value="<?= $saldoPendiente ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha del Pago</label>
                        <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">
                        Registrar Pago
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>