<?php
/**
 * Componente: Card Ambulatoria
 * Variables requeridas: $consulta, $paciente, $hasAmbulatoria, $ambulatoriaIsFinal, $ambulatoriaGrads, $hasForo
 */
?>

<!-- 3. Prueba Ambulatoria (Opcional) -->
<div id="card-ambulatoria" class="card grad-card <?= $hasAmbulatoria ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>3️⃣ Prueba Ambulatoria (Opcional)</h4>
        <div>
            <?php if ($ambulatoriaIsFinal): ?>
                <span class="badge badge-warning">⭐ FINAL</span>
            <?php endif; ?>
            <?php if ($hasAmbulatoria): ?>
                <span class="badge badge-success">✓ Completado</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$hasForo): ?>
            <p class="text-muted text-center">Completa el Foroptor primero para habilitar esta sección.</p>
        <?php elseif (empty($ambulatoriaGrads['OD']) && empty($ambulatoriaGrads['OI'])): ?>
            <p class="text-secondary empty-state-message text-center">Graduación ajustada para adaptación del paciente.</p>
            <div class="text-center mt-3">
                <a href="/index.php?page=graduaciones_live_create&step=ambulatorio&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                   class="btn btn-primary">
                    🚶 Capturar Prueba Ambulatoria
                </a>
            </div>
        <?php else: ?>
            <div class="graduacion-display mini-graduacion">
                 <?= renderOjoLive('OD', $ambulatoriaGrads['OD']) ?>
                 <?= renderOjoLive('OI', $ambulatoriaGrads['OI']) ?>
            </div>
            <div class="card-actions mt-3 text-right">
                 <a href="/index.php?page=graduaciones_live_create&step=ambulatorio&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-secondary btn-sm">
                     ✏️ Editar
                 </a>
                 <a href="/index.php?page=graduaciones_live_delete&step=ambulatorio&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                    class="btn btn-danger btn-sm">
                     🗑️ Borrar
                 </a>
            </div>
        <?php endif; ?>
    </div>
</div>
