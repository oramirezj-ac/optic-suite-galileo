<?php
/**
 * Componente: Card Distancia Pupilar
 * Variables requeridas: $consulta, $paciente
 */
?>

<!-- 1.5 Distancia Pupilar -->
<div id="card-dp" class="card grad-card <?= !empty($consulta['dp_lejos_total']) ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>📏 Distancia Pupilar</h4>
        <?php if (!empty($consulta['dp_lejos_total'])): ?>
            <span class="badge badge-success">✓ Completado</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($consulta['dp_lejos_total'])): ?>
            <p class="text-secondary empty-state-message text-center mb-3">Medición necesaria para la fabricación de lentes.</p>
            
            <form id="dp-form" action="/index.php?page=consultas_lentes_index&action=update_dp" method="POST" class="dp-form-inline">
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="form-row align-items-end justify-content-center">
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_total_input" class="small">DP Total</label>
                                <input type="number" step="1" name="dp_lejos_total" 
                                       id="dp_total_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_lejos_total'] ?? '' ?>" 
                                       placeholder="60"
                                       min="50"
                                       max="79"
                                       onchange="calcularDNPIndex()">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_od_input" class="small">DNP OD</label>
                                <input type="number" step="0.5" name="dp_od" 
                                       id="dp_od_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_od'] ?? '' ?>" 
                                       placeholder="31">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_oi_input" class="small">DNP OI</label>
                                <input type="number" step="0.5" name="dp_oi" 
                                       id="dp_oi_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_oi'] ?? '' ?>" 
                                       placeholder="31">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_cerca_input" class="small">DP Cerca</label>
                                <input type="number" step="0.5" name="dp_cerca" 
                                       id="dp_cerca_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_cerca'] ?? '' ?>" 
                                       placeholder="60">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Guardar DP</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <!-- Display de valores guardados (compacto) -->
            <div id="dp-display">
                <div class="text-center mb-2">
                    <span class="mr-3"><strong class="text-muted small">DP Total:</strong> <span class="text-success font-weight-bold"><?= $consulta['dp_lejos_total'] ?? '-' ?></span> mm</span>
                    <span class="mr-3"><strong class="text-muted small">DNP OD:</strong> <span class="text-primary font-weight-bold"><?= $consulta['dp_od'] ?? '-' ?></span> mm</span>
                    <span class="mr-3"><strong class="text-muted small">DNP OI:</strong> <span class="text-primary font-weight-bold"><?= $consulta['dp_oi'] ?? '-' ?></span> mm</span>
                    <span><strong class="text-muted small">DP Cerca:</strong> <span class="text-info font-weight-bold"><?= $consulta['dp_cerca'] ?? '-' ?></span> mm</span>
                </div>
                
                <div class="card-actions mt-3 text-right">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDPForm()">
                        ✏️ Editar
                    </button>
                    <a href="/index.php?page=consultas_lentes_index&action=delete_dp&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('¿Eliminar la Distancia Pupilar?')">
                        🗑️ Borrar
                    </a>
                </div>
            </div>
            
            <!-- Formulario de edición (oculto por defecto) -->
            <form id="dp-form" action="/index.php?page=consultas_lentes_index&action=update_dp" method="POST" class="dp-form-inline d-none">
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="form-row align-items-end justify-content-center">
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_total_input" class="small">DP Total</label>
                                <input type="number" step="1" name="dp_lejos_total" 
                                       id="dp_total_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_lejos_total'] ?? '' ?>" 
                                       placeholder="60"
                                       min="55"
                                       max="71"
                                       onchange="calcularDNPIndex()">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_od_input" class="small">DNP OD</label>
                                <input type="number" step="0.5" name="dp_od" 
                                       id="dp_od_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_od'] ?? '' ?>" 
                                       placeholder="31">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_oi_input" class="small">DNP OI</label>
                                <input type="number" step="0.5" name="dp_oi" 
                                       id="dp_oi_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_oi'] ?? '' ?>" 
                                       placeholder="31">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <label for="dp_cerca_edit_input" class="small">DP Cerca</label>
                                <input type="number" step="0.5" name="dp_cerca" 
                                       id="dp_cerca_edit_input"
                                       class="form-control form-control-sm text-center" 
                                       value="<?= $consulta['dp_cerca'] ?? '' ?>" 
                                       placeholder="60">
                            </div>
                            <div class="form-group mb-2 mx-2">
                                <button type="submit" class="btn btn-primary btn-sm">💾 Guardar</button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="toggleDPForm()">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
