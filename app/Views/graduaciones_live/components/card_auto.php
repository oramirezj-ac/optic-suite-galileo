<?php
/**
 * Componente: Card Autorefractómetro
 * Variables requeridas: $consulta, $paciente, $hasAuto, $autoIsFinal, $autoGrads
 */
?>

<!-- 1. Autorefractómetro -->
<div id="card-auto" class="card grad-card <?= $hasAuto ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>1️⃣ Autorefractómetro</h4>
        <div>
            <?php if ($autoIsFinal): ?>
                <span class="badge badge-warning">⭐ FINAL</span>
            <?php endif; ?>
            <?php if ($hasAuto): ?>
                <span class="badge badge-success">Completado</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$hasAuto): ?>
            <p class="text-secondary empty-state-message text-center">Paso inicial: Captura los datos del equipo.</p>
            <div class="text-center mt-3">
                <a href="/index.php?page=graduaciones_live_create&step=auto&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary btn-lg">
                    🚀 Iniciar Captura
                </a>
            </div>
        <?php else: ?>
            <div class="graduacion-display mini-graduacion">
                 <?= renderOjoLive('OD', $autoGrads['OD']) ?>
                 <?= renderOjoLive('OI', $autoGrads['OI']) ?>
            </div>
            <div class="card-actions mt-3 text-right">
                 <button type="button" class="btn btn-info btn-sm btn-copiar-grad"
                         data-od-esfera="<?= $autoGrads['OD']['esfera'] ?? '0.00' ?>"
                         data-od-cilindro="<?= $autoGrads['OD']['cilindro'] ?? '0.00' ?>"
                         data-od-eje="<?= $autoGrads['OD']['eje'] ?? '0' ?>"
                         data-od-adicion="<?= $autoGrads['OD']['adicion'] ?? '0.00' ?>"
                         data-oi-esfera="<?= $autoGrads['OI']['esfera'] ?? '0.00' ?>"
                         data-oi-cilindro="<?= $autoGrads['OI']['cilindro'] ?? '0.00' ?>"
                         data-oi-eje="<?= $autoGrads['OI']['eje'] ?? '0' ?>"
                         data-oi-adicion="<?= $autoGrads['OI']['adicion'] ?? '0.00' ?>"
                         data-target-url="/index.php?page=graduaciones_live_create&step=foro&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>"
                         title="Copiar al Foroptor">
                     📋 Copiar
                 </button>
                 <a href="/index.php?page=graduaciones_live_create&step=auto&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">
                    ✏️ Editar Datos
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
