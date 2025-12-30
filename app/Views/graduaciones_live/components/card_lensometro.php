<?php
/**
 * Componente: Card Lensómetro
 * Variables requeridas: $consulta, $paciente, $hasLensometro, $lensometroIsFinal, $lensometroGrads
 */
?>

<!-- 4. Lensómetro (Opcional) -->
<div id="card-lensometro" class="card grad-card <?= $hasLensometro ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>4️⃣ Lensómetro (Opcional)</h4>
        <div>
            <?php if ($lensometroIsFinal): ?>
                <span class="badge badge-warning">⭐ FINAL</span>
            <?php endif; ?>
            <?php if ($hasLensometro): ?>
                <span class="badge badge-success">✓ Completado</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($lensometroGrads['OD']) && empty($lensometroGrads['OI'])): ?>
            <p class="text-secondary empty-state-message text-center">Medición de lentes actuales del paciente.</p>
            <div class="text-center mt-3">
                <a href="/index.php?page=graduaciones_live_create&step=lensometro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                   class="btn btn-primary">
                    🔍 Capturar Lensómetro
                </a>
            </div>
        <?php else: ?>
            <div class="graduacion-display mini-graduacion">
                 <?= renderOjoLive('OD', $lensometroGrads['OD']) ?>
                 <?= renderOjoLive('OI', $lensometroGrads['OI']) ?>
            </div>
            <div class="card-actions mt-3 text-right">
                 <?php if (!$lensometroIsFinal): ?>
                     <a href="/index.php?page=consultas_lentes_index&action=mark_final&tipo=lensometro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                        class="btn btn-warning btn-sm"
                        onclick="return confirm('¿Marcar esta graduación como FINAL para la venta?')">
                         ⭐ Marcar como Final
                     </a>
                 <?php endif; ?>
                 <a href="/index.php?page=graduaciones_live_create&step=lensometro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-secondary btn-sm">
                     ✏️ Editar
                 </a>
                 <a href="/index.php?page=graduaciones_live_delete&step=lensometro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-danger btn-sm">
                     🗑️ Borrar
                 </a>
            </div>
        <?php endif; ?>
    </div>
</div>
