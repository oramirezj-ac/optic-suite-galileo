<?php
/**
 * Componente: Card AV/CV (Agudeza Visual y Corrección Visual)
 * Variables requeridas: $consulta, $paciente, $hasAV, $hasCV, $avLookup
 */
?>

<!-- 0. AV/CV (Agudeza Visual / Corrección Visual) -->
<div id="card-av-cv" class="card grad-card <?= ($hasAV && $hasCV) ? 'border-success' : '' ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>👁️ AV / CV</h4>
        <?php if ($hasAV && $hasCV): ?>
            <span class="badge badge-success">✓ Completado</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            
            <!-- COLUMNA IZQUIERDA: AV (Sin Lentes) -->
            <div class="col-md-6">
                <h5 class="text-muted mb-3">👁️ Sin Lentes (AV)</h5>
                
                <?php if ($hasAV): ?>
                    <div class="av-display-grid mb-3">
                        <div class="av-item">
                            <strong>AV AO</strong>
                            <span class="av-value"><?= $avLookup[$consulta['av_ao_id']] ?? '-' ?></span>
                        </div>
                        <div class="av-item">
                            <strong>AV OD</strong>
                            <span class="av-value"><?= $avLookup[$consulta['av_od_id']] ?? '-' ?></span>
                        </div>
                        <div class="av-item">
                            <strong>AV OI</strong>
                            <span class="av-value"><?= $avLookup[$consulta['av_oi_id']] ?? '-' ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-secondary empty-state-message text-center">Primer paso: Captura la agudeza visual sin lentes.</p>
                <?php endif; ?>
                
                <div class="text-center">
                    <a href="/index.php?page=av_live_index&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" 
                       class="btn <?= $hasAV ? 'btn-secondary' : 'btn-primary' ?> btn-sm">
                        <?= $hasAV ? '✏️ Editar AV' : '🚀 Capturar AV' ?>
                    </a>
                </div>
            </div>
            
            <!-- COLUMNA DERECHA: CV (Con Lentes) -->
            <div class="col-md-6 border-left">
                <h5 class="text-muted mb-3">👓 Con Lentes (CV)</h5>
                
                <?php if ($hasCV): ?>
                    <div class="cv-display-grid mb-3">
                        <div class="cv-item">
                            <strong>CV AO</strong>
                            <span class="cv-value"><?= $avLookup[$consulta['cv_ao_id']] ?? '-' ?></span>
                        </div>
                        <div class="cv-item">
                            <strong>CV OD</strong>
                            <span class="cv-value"><?= $avLookup[$consulta['cv_od_id']] ?? '-' ?></span>
                        </div>
                        <div class="cv-item">
                            <strong>CV OI</strong>
                            <span class="cv-value"><?= $avLookup[$consulta['cv_oi_id']] ?? '-' ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-secondary empty-state-message text-center">Último paso: Captura la corrección visual con lentes.</p>
                <?php endif; ?>
                
                <div class="text-center">
                    <a href="/index.php?page=av_live_index&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>&mode=cv" 
                       class="btn <?= $hasCV ? 'btn-secondary' : 'btn-primary' ?> btn-sm">
                        <?= $hasCV ? '✏️ Editar CV' : '🚀 Capturar CV' ?>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
    
</div>
