<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
require_once __DIR__ . '/../../Helpers/ConfigHelper.php';

// 2. Forzamos la acción 'edit'
$_GET['action'] = 'edit'; 
$data = handleVentaAction();

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$venta = $data['venta'];

// 4. (Seguridad)
if (!$paciente || !$venta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Nombre completo
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>
        <small>Editando Nota #<?= htmlspecialchars($venta['numero_nota']) ?> de:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=ventas_details&id=<?= $venta['id_venta'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Datos de la Nota</h3>
        </div>
        <div class="card-body">
            
            <form action="/venta_handler.php?action=update" method="POST">
                <input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_nota">Número de Nota (Folio)</label>
                        <input type="number" id="numero_nota" name="numero_nota" value="<?= htmlspecialchars($venta['numero_nota']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_venta">Fecha de Venta</label>
                        <input type="date" id="fecha_venta" name="fecha_venta" value="<?= $venta['fecha_venta'] ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costo_total">Costo Total ($)</label>
                        <input type="number" id="costo_total" name="costo_total" step="0.01" value="<?= htmlspecialchars($venta['costo_total']) ?>" required>
                    </div>
                    </div>

                <div class="form-group">
                    <label for="observaciones">Descripción de Productos / Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars($venta['observaciones_venta'] ?? '') ?></textarea>
                </div>

                <div class="form-group form-group-third">
                        <label for="vendedor_armazon">Vendedor (Comisión)</label>
                        <select id="vendedor_armazon" name="vendedor_armazon">
                            <option value="">-- No Aplica --</option>
                            <?php 
                            $actual = $venta['vendedor_armazon'] ?? '';
                            foreach (ConfigHelper::getVendedoresList() as $vendedor): 
                                $selected = ($actual === $vendedor) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($vendedor) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($vendedor) ?>
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