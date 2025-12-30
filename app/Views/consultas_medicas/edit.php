<?php
/* ==========================================================================
   CONSULTA MÉDICA - Editar
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
$edad = $patient['fecha_nacimiento'] ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) : 'Sin datos';

// Obtener catálogo de productos médicos
$productosMedicos = $consultaModel->getCatalogoProductosMedicos();

// Obtener productos médicos de esta consulta
$productosConsulta = $consultaModel->getProductosMedicosByConsulta($consultaId);

// Extraer solo la fecha (sin hora) para el input type="date"
$fechaSoloFecha = date('Y-m-d', strtotime($consulta['fecha']));
?>

<div class="page-header">
    <h1>✏️ Editar Consulta Médica</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_medicas_details&id=<?= $consultaId ?>" class="btn btn-secondary">← Cancelar</a>
    </div>
</div>


<div class="page-content">
    
    <!-- Información del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <div class="patient-info-details">
                <span><strong>Edad:</strong> <?= $edad === 'Sin datos' ? $edad : $edad . ' años' ?></span>
            </div>
        </div>
    </div>
    
    <!-- Formulario de edición -->
    <div class="card">
        <div class="card-body">
            <form action="/consulta_medica_handler.php?action=update&id=<?= $consultaId ?>" method="POST">
                <input type="hidden" name="patient_id" value="<?= $consulta['paciente_id'] ?>">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                
                <!-- Navegación de pestañas -->
                <div class="view-tabs">
                    <button type="button" class="view-tab active" data-view="consulta">Datos de Consulta</button>
                    <button type="button" class="view-tab" data-view="cobro">Cobro y Medicamentos</button>
                </div>
                
                <!-- Pestaña: Datos de Consulta -->
                <div id="view-consulta" class="view-panel active">
                    <h3>Información de la Consulta</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_consulta">Fecha de Consulta</label>
                            <input type="date" id="fecha_consulta" name="fecha_consulta" value="<?= $fechaSoloFecha ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="motivo">Motivo de Consulta</label>
                            <input type="text" id="motivo" name="motivo" value="<?= htmlspecialchars($consulta['detalle_motivo'] ?? '') ?>" placeholder="Ej. Dolor ocular, visión borrosa...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnostico">Diagnóstico</label>
                        <textarea id="diagnostico" name="diagnostico" rows="4" placeholder="Diagnóstico médico detallado..."><?= htmlspecialchars($consulta['diagnostico_dx'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="tratamiento">Tratamiento Recomendado</label>
                        <textarea id="tratamiento" name="tratamiento" rows="4" placeholder="Tratamiento y recomendaciones..."><?= htmlspecialchars($consulta['tratamiento_rx'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones Adicionales</label>
                        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Notas adicionales..."><?= htmlspecialchars($consulta['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
                
                
                <!-- Pestaña: Cobro y Medicamentos -->
                <div id="view-cobro" class="view-panel">
                    <h3>Cobro y Venta de Medicamentos</h3>
                    
                    <!-- Estado Financiero de la Consulta -->
                    <div class="form-group">
                        <label for="estado_financiero">Tipo de Consulta</label>
                        <select id="estado_financiero" name="estado_financiero" required>
                            <option value="cobrado" <?= ($consulta['estado_financiero'] ?? 'cobrado') === 'cobrado' ? 'selected' : '' ?>>💰 Cobrado - Consulta pagada</option>
                            <option value="cortesia" <?= ($consulta['estado_financiero'] ?? '') === 'cortesia' ? 'selected' : '' ?>>🎁 Cortesía - Sin costo (Familiar/Amigo)</option>
                            <option value="garantia" <?= ($consulta['estado_financiero'] ?? '') === 'garantia' ? 'selected' : '' ?>>🔄 Garantía/Seguimiento - Sin costo (Seguimiento semanal)</option>
                            <option value="pendiente" <?= ($consulta['estado_financiero'] ?? '') === 'pendiente' ? 'selected' : '' ?>>⏳ Pendiente - Por cobrar después</option>
                        </select>
                        <small class="text-secondary">Selecciona el tipo de consulta para determinar si se cobra o no</small>
                    </div>
                    
                    <!-- Campos de Cobro (se ocultan si es cortesía o garantía) -->
                    <div id="campos-cobro">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="costo_consulta">Costo de Consulta ($)</label>
                                <input type="number" id="costo_consulta" name="costo_consulta" class="calc-costo-consulta" step="0.01" value="<?= $consulta['costo_servicio'] ?? 0 ?>" placeholder="Ej. 500.00">
                            </div>
                            <div class="form-group">
                                <label for="metodo_pago">Método de Pago</label>
                                <select id="metodo_pago" name="metodo_pago">
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo" <?= ($consulta['metodo_pago'] ?? '') === 'efectivo' ? 'selected' : '' ?>>Efectivo (Sin comisión)</option>
                                    <option value="transferencia" <?= ($consulta['metodo_pago'] ?? '') === 'transferencia' ? 'selected' : '' ?>>Transferencia (Sin comisión)</option>
                                    <option value="tarjeta_debito" <?= ($consulta['metodo_pago'] ?? '') === 'tarjeta_debito' ? 'selected' : '' ?>>Tarjeta Débito (Comisión baja)</option>
                                    <option value="tarjeta_credito" <?= ($consulta['metodo_pago'] ?? '') === 'tarjeta_credito' ? 'selected' : '' ?>>Tarjeta Crédito (Comisión alta / MSI)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    
                    <hr>
                    
                    <h4>Medicamentos Vendidos</h4>
                    
                    <?php if (empty($productosMedicos)): ?>
                        <div class="alert alert-warning">
                            <strong>⚠️ Catálogo vacío:</strong> No hay productos médicos registrados en el catálogo.
                        </div>
                    <?php else: ?>
                    
                    <div id="medicamentos-container">
                        <?php 
                        // Si hay productos en la consulta, mostrarlos
                        if (!empty($productosConsulta)): 
                            foreach ($productosConsulta as $index => $prodConsulta): 
                        ?>
                        <div class="form-row medicamento-row">
                            <div class="form-group">
                                <label>Medicamento</label>
                                <select name="medicamentos[<?= $index ?>][producto_id]" class="select-medicamento">
                                    <option value="">Seleccionar medicamento...</option>
                                    <?php foreach ($productosMedicos as $prod): ?>
                                        <option value="<?= $prod['id'] ?>" 
                                                data-precio="<?= $prod['precio_sugerido'] ?>"
                                                <?= $prodConsulta['producto_id'] == $prod['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($prod['nombre']) ?> - $<?= number_format($prod['precio_sugerido'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="medicamentos[<?= $index ?>][cantidad]" 
                                       class="calc-cantidad" placeholder="1" min="1" 
                                       value="<?= $prodConsulta['cantidad'] ?>">
                            </div>
                            <div class="form-group">
                                <label>Precio Unitario ($)</label>
                                <input type="number" name="medicamentos[<?= $index ?>][precio]" 
                                       class="calc-precio" step="0.01" placeholder="0.00" 
                                       value="<?= $prodConsulta['precio'] ?>" readonly>
                            </div>
                            <div class="form-group" style="display: flex; align-items: flex-end;">
                                <button type="button" class="btn btn-danger btn-sm btn-eliminar-medicamento">🗑️</button>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        else: 
                            // Si no hay productos, mostrar una fila vacía
                        ?>
                        <div class="form-row medicamento-row">
                            <div class="form-group">
                                <label>Medicamento</label>
                                <select name="medicamentos[0][producto_id]" class="select-medicamento">
                                    <option value="">Seleccionar medicamento...</option>
                                    <?php foreach ($productosMedicos as $prod): ?>
                                        <option value="<?= $prod['id'] ?>" data-precio="<?= $prod['precio_sugerido'] ?>">
                                            <?= htmlspecialchars($prod['nombre']) ?> - $<?= number_format($prod['precio_sugerido'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="medicamentos[0][cantidad]" class="calc-cantidad" placeholder="1" min="1" value="1">
                            </div>
                            <div class="form-group">
                                <label>Precio Unitario ($)</label>
                                <input type="number" name="medicamentos[0][precio]" class="calc-precio" step="0.01" placeholder="0.00" readonly>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" class="btn btn-secondary btn-sm" id="btn-agregar-medicamento">+ Agregar Otro Medicamento</button>
                    
                    <?php endif; ?>
                </div>
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
                    <a href="/index.php?page=consultas_medicas_details&id=<?= $consultaId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
</div>
