<?php
// 1. Incluimos dependencias
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
require_once __DIR__ . '/../../Helpers/ConfigHelper.php';

// 2. Forzamos la acción 'edit'
$_GET['action'] = 'edit'; 
$data = handleVentaAction();

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$venta = $data['venta'];
$patientId = $paciente['id'];

// 4. Seguridad
if (!$paciente || !$venta) {
    header('Location: /index.php?page=ventas_index&error=data_not_found');
    exit();
}

// 5. Preparar datos para visualización
$fullName = FormatHelper::patientName($paciente);
$fechaVentaInput = date('Y-m-d', strtotime($venta['fecha_venta']));
$fechaAnticipoInput = !empty($venta['fecha_anticipo']) ? date('Y-m-d', strtotime($venta['fecha_anticipo'])) : '';
?>

<div class="page-header">
    <h1>
        <small>Editando Venta de:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=ventas_index" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Datos de la Nota #<?= htmlspecialchars($venta['numero_nota']) ?></h3>
        </div>
        <div class="card-body">
            
            <form action="/index.php?page=ventas_index&action=update" method="POST">
                
                <input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_nota">Número de Nota (Folio)</label>
                        <input type="number" id="numero_nota" name="numero_nota" value="<?= htmlspecialchars($venta['numero_nota']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_venta">Fecha de Venta</label>
                        <input type="date" id="fecha_venta" name="fecha_venta" value="<?= $fechaVentaInput ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costo_total">Costo Total ($)</label>
                        <input type="number" id="costo_total" name="costo_total" step="0.01" value="<?= $venta['costo_total'] ?>" required>
                    </div>
                </div>

                <div class="form-toolbar">
                    <span class="toolbar-title">Agregar detalle rápido:</span>
                    
                    <div class="form-row">
                        
                        <div class="form-group">
                            <select class="js-text-helper" data-prefix="Lente">
                                <option value="" selected disabled>Tipo de Lente...</option>
                                <option value="Monofocal">Monofocal</option>
                                <option value="Bifocal Flap-Top">Bifocal Flap-Top</option>
                                <option value="Bifocal Invisible">Bifocal Invisible</option>
                                <option value="Progresivo">Progresivo</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="js-text-helper" data-prefix="">
                                <option value="" selected disabled>Material...</option>
                                <option value="CR-39">CR-39</option>
                                <option value="Policarbonato">Policarbonato</option>
                                <option value="Hi-Index">Hi-Index</option>
                                <option value="Trivex">Trivex</option>
                                <option value="Cristal">Cristal</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="js-text-helper" data-prefix="con">
                                <option value="" selected disabled>Tratamiento...</option>
                                <option value="Antireflejante (AR)">AR</option>
                                <option value="Blue Ray">Blue Ray</option>
                                <option value="Fotocromático">Fotocromático</option>
                                <option value="Transitions">Transitions</option>
                                <option value="Crizal">Crizal</option>
                                <option value="Blanco">Blanco</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <select class="js-text-helper" data-prefix="en Armazón">
                                <option value="" selected disabled>Armazón...</option>
                                <option value="Propio">Propio</option>
                                <option value="Genérico">Genérico</option>
                                <option value="De Marca">De Marca</option>
                            </select>
                        </div>

                    </div>
                    <span class="toolbar-help-text">* Al seleccionar se agregará al texto de abajo.</span>
                </div>

                <div class="form-group">
                    <label for="observaciones">Descripción de Productos / Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4"><?= htmlspecialchars($venta['observaciones_venta'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    
                    <div class="form-group form-group-third">
                        <label for="metodo_pago">Método de Pago</label>
                        <select id="metodo_pago" name="metodo_pago">
                            <?php foreach (ConfigHelper::getMetodosPago() as $metodo): ?>
                                <option value="<?= htmlspecialchars($metodo) ?>" <?= ($venta['metodo_pago'] ?? '') == $metodo ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($metodo) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group form-group-third">
                        <label for="vendedor_armazon">Vendedor</label>
                        <select id="vendedor_armazon" name="vendedor_armazon">
                            <option value="">-- No Aplica --</option>
                            <?php foreach (ConfigHelper::getVendedoresList() as $vendedor): ?>
                                <option value="<?= htmlspecialchars($vendedor) ?>" <?= ($venta['vendedor_armazon'] ?? '') == $vendedor ? 'selected' : '' ?>>
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