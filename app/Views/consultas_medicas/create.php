<?php
/* ==========================================================================
   CONSULTA MÉDICA - Crear Nueva
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaMedicaController.php';
require_once __DIR__ . '/../../Models/ConsultaModel.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$patientId = $_GET['patient_id'] ?? null;

if (!$patientId) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
    exit();
}

$pdo = getConnection();
$patientModel = new PatientModel($pdo);
$patient = $patientModel->getById($patientId);

if (!$patient) {
    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no encontrado'));
    exit();
}

// Obtener catálogo de productos médicos
$consultaModel = new ConsultaModel($pdo);
$productosMedicos = $consultaModel->getCatalogoProductosMedicos();

$fullName = implode(' ', array_filter([
    $patient['nombre'], 
    $patient['apellido_paterno'], 
    $patient['apellido_materno']
]));
$edad = $patient['fecha_nacimiento'] ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) : 'Sin datos';
?>

<!-- Encabezado con datos del paciente -->
<div class="page-header">
    <h1>🏥 Nueva Consulta Médica</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_medicas_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">← Ver Historial</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Información del paciente -->
    <div class="card">
        <div class="card-body patient-info-header">
            <h4>👤 <?= htmlspecialchars($fullName) ?></h4>
            <div class="patient-info-details">
                <span><strong>Edad:</strong> <?= $edad === 'Sin datos' ? $edad : $edad . ' años' ?></span>
                <span><strong>Fecha de 1ª Visita:</strong> <?= \FormatHelper::dateFull($patient['fecha_primera_visita']) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Formulario con pestañas -->
    <div class="card">
        <div class="card-header view-actions">
            <button class="btn btn-secondary active" data-view="consulta">Datos de Consulta</button>
            <button class="btn btn-secondary" data-view="cobro">Cobro y Medicamentos</button>
        </div>
        
        <div class="card-body">
            <form action="/consulta_medica_handler.php?action=store" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                
                <!-- Pestaña: Datos de Consulta -->
                <div id="view-consulta" class="view-panel active">
                    <h3>Información de la Consulta</h3>
                    
                    <div class="form-row">
                        <div class="form-group form-group-quarter">
                            <label for="fecha_consulta">Fecha de Consulta</label>
                            <input type="date" id="fecha_consulta" name="fecha_consulta" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group form-group-three-quarters">
                            <label for="motivo">Motivo de Consulta</label>
                            <input type="text" id="motivo" name="motivo" placeholder="Ej. Dolor ocular, visión borrosa...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnostico">Diagnóstico</label>
                        <textarea id="diagnostico" name="diagnostico" rows="4" placeholder="Diagnóstico médico detallado..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="tratamiento">Tratamiento Recomendado</label>
                        <textarea id="tratamiento" name="tratamiento" rows="4" placeholder="Tratamiento y recomendaciones..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones">Observaciones Adicionales</label>
                        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
                
                <!-- Pestaña: Cobro y Medicamentos -->
                <div id="view-cobro" class="view-panel">
                    <h3>Cobro y Venta de Medicamentos</h3>
                    
                    <!-- Estado Financiero de la Consulta -->
                    <div class="form-group">
                        <label for="estado_financiero">Tipo de Consulta</label>
                        <select id="estado_financiero" name="estado_financiero" required>
                            <option value="cobrado">💰 Cobrado - Consulta pagada</option>
                            <option value="cortesia">🎁 Cortesía - Sin costo (Familiar/Amigo)</option>
                            <option value="garantia">🔄 Garantía/Seguimiento - Sin costo (Seguimiento semanal)</option>
                            <option value="pendiente">⏳ Pendiente - Por cobrar después</option>
                        </select>
                        <small class="text-secondary">Selecciona el tipo de consulta para determinar si se cobra o no</small>
                    </div>
                    
                    <!-- Campos de Cobro (se ocultan si es cortesía o garantía) -->
                    <div id="campos-cobro">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="costo_consulta">Costo de Consulta ($)</label>
                                <input type="number" id="costo_consulta" name="costo_consulta" class="calc-costo-consulta" step="0.01" placeholder="Ej. 500.00">
                            </div>
                            <div class="form-group">
                                <label for="metodo_pago">Método de Pago</label>
                                <select id="metodo_pago" name="metodo_pago">
                                    <option value="">Seleccionar...</option>
                                    <option value="efectivo">Efectivo (Sin comisión)</option>
                                    <option value="transferencia">Transferencia (Sin comisión)</option>
                                    <option value="tarjeta_debito">Tarjeta Débito (Comisión baja)</option>
                                    <option value="tarjeta_credito">Tarjeta Crédito (Comisión alta / MSI)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h4>Medicamentos Vendidos</h4>
                    
                    <?php if (empty($productosMedicos)): ?>
                        <div class="alert alert-warning">
                            <strong>⚠️ Catálogo vacío:</strong> No hay productos médicos registrados en el catálogo. 
                            <a href="/index.php?page=productos_medicos">Agregar productos al catálogo</a>
                        </div>
                    <?php else: ?>
                    
                    <div id="medicamentos-container">
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
                    </div>
                    
                    <button type="button" class="btn btn-secondary btn-sm" id="btn-agregar-medicamento">+ Agregar Otro Medicamento</button>
                    
                    <?php endif; ?>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="total_venta">Total de Venta ($)</label>
                        <input type="number" id="total_venta" name="total_venta" step="0.01" placeholder="0.00" readonly>
                        <small class="text-secondary">Se calcula automáticamente (Consulta + Medicamentos)</small>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">💾 Guardar Consulta</button>
                    <a href="/index.php?page=consultas_medicas_index&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    
</div>
