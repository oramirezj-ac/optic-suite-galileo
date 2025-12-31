<?php
require_once __DIR__ . '/../../Controllers/AbonoController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
require_once __DIR__ . '/../../Helpers/ConfigHelper.php';

// Usamos la acción 'edit' del controlador para obtener los datos actuales
$_GET['action'] = 'edit';
$data = handleAbonoAction();

$abono = $data['abono'];
$venta = $data['venta'];
$paciente = $data['paciente'];

if (!$abono || !$venta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = FormatHelper::patientName($paciente);
?>

<div class="page-header">
    <h1>Editar Abono</h1>
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
        <div class="card-body">
            
            <form action="/abono_handler.php?action=update" method="POST">
                <input type="hidden" name="id_abono" value="<?= $abono['id_abono'] ?>">
                <input type="hidden" name="venta_id" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="monto">Monto ($)</label>
                        <input type="number" id="monto" name="monto" step="0.01" value="<?= $abono['monto'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fecha">Fecha del Pago</label>
                        <input type="date" id="fecha" name="fecha" value="<?= $abono['fecha'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="metodo_pago">Método</label>
                        <select id="metodo_pago" name="metodo_pago">
                            <?php 
                            $actual = $abono['metodo_pago'] ?? 'Efectivo';
                            foreach (ConfigHelper::getMetodosPago() as $metodo): 
                                $selected = ($actual === $metodo) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($metodo) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($metodo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>