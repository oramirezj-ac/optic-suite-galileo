<?php
/**
 * Componente: Card Foroptor (Subjetivo)
 * Variables requeridas: $consulta, $paciente, $hasForo, $foroIsFinal, $foroGrads, $hasAuto
 */
?>

<!-- 2. Foroptor (Subjetivo) -->
<div id="card-foro" class="card grad-card <?= $hasForo ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>2️⃣ Foroptor (Subjetivo)</h4>
        <div>
            <?php if ($foroIsFinal): ?>
                <span class="badge badge-warning">⭐ FINAL</span>
            <?php endif; ?>
            <?php if ($hasForo): ?>
                <span class="badge badge-success">✓ Completado</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$hasAuto): ?>
            <p class="text-muted text-center">Completa el paso anterior para habilitar esta sección.</p>
        <?php elseif (empty($foroGrads['OD']) && empty($foroGrads['OI'])): ?>
            <p class="text-secondary empty-state-message text-center">Refinamiento de la graduación con el paciente.</p>
            <div class="text-center mt-3">
                <a href="/index.php?page=graduaciones_live_create&step=foro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary">
                    👓 Capturar Foroptor
                </a>
            </div>
        <?php else: ?>
            <div class="graduacion-display mini-graduacion">
                 <?= renderOjoLive('OD', $foroGrads['OD']) ?>
                 <?= renderOjoLive('OI', $foroGrads['OI']) ?>
            </div>
            <div class="card-actions mt-3 text-right">
                 <button type="button" class="btn btn-info btn-sm btn-copiar-grad"
                         data-od-esfera="<?= $foroGrads['OD']['esfera'] ?? '0.00' ?>"
                         data-od-cilindro="<?= $foroGrads['OD']['cilindro'] ?? '0.00' ?>"
                         data-od-eje="<?= $foroGrads['OD']['eje'] ?? '0' ?>"
                         data-od-adicion="<?= $foroGrads['OD']['adicion'] ?? '0.00' ?>"
                         data-oi-esfera="<?= $foroGrads['OI']['esfera'] ?? '0.00' ?>"
                         data-oi-cilindro="<?= $foroGrads['OI']['cilindro'] ?? '0.00' ?>"
                         data-oi-eje="<?= $foroGrads['OI']['eje'] ?? '0' ?>"
                         data-oi-adicion="<?= $foroGrads['OI']['adicion'] ?? '0.00' ?>"
                         data-target-url="/index.php?page=graduaciones_live_create&step=ambulatorio&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>"
                         title="Copiar a Ambulatoria">
                     📋 Copiar
                 </button>
                 <a href="/index.php?page=graduaciones_live_create&step=foro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">
                    ✏️ Editar
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
