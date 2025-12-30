<?php
/**
 * Componente: Card Externa
 * Variables requeridas: $consulta, $paciente, $hasExterna, $externaIsFinal, $externaGrads
 */
?>

<!-- 5. Externa (Opcional) -->
<div id="card-externa" class="card grad-card <?= $hasExterna ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>5️⃣ Graduación Externa (Opcional)</h4>
        <div>
            <?php if ($externaIsFinal): ?>
                <span class="badge badge-warning">⭐ FINAL</span>
            <?php endif; ?>
            <?php if ($hasExterna): ?>
                <span class="badge badge-success">✓ Completado</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($externaGrads['OD']) && empty($externaGrads['OI'])): ?>
            <p class="text-secondary empty-state-message text-center">Graduación proporcionada por el paciente.</p>
            <div class="text-center mt-3">
                <a href="/index.php?page=graduaciones_live_create&step=externa&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                   class="btn btn-primary">
                    📄 Capturar Externa
                </a>
            </div>
        <?php else: ?>
            <div class="graduacion-display mini-graduacion">
                 <?= renderOjoLive('OD', $externaGrads['OD']) ?>
                 <?= renderOjoLive('OI', $externaGrads['OI']) ?>
            </div>
            <div class="card-actions mt-3 text-right">
                 <?php if (!$externaIsFinal): ?>
                     <a href="/index.php?page=consultas_lentes_index&action=mark_final&tipo=externa&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                        class="btn btn-warning btn-sm"
                        onclick="return confirm('¿Marcar esta graduación como FINAL para la venta?')">
                         ⭐ Marcar como Final
                     </a>
                 <?php endif; ?>
                 <a href="/index.php?page=graduaciones_live_create&step=externa&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-secondary btn-sm">
                     ✏️ Editar
                 </a>
                 <a href="/index.php?page=graduaciones_live_delete&step=externa&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-danger btn-sm">
                     🗑️ Borrar
                 </a>
            </div>
        <?php endif; ?>
    </div>
</div>
