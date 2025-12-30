<?php
/**
 * Componente: Card AV/CV (Agudeza Visual y Corrección Visual)
 * Variables requeridas: $consulta, $paciente, $hasAV, $hasCV
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
        <?php if ($hasAV): ?>
            <div class="av-display mb-3">
                <h6 class="text-muted">Agudeza Visual (Sin Lentes)</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>OD:</strong> <?= $consulta['av_od_lejos'] ?? '-' ?> (Lejos) | <?= $consulta['av_od_cerca'] ?? '-' ?> (Cerca)
                    </div>
                    <div class="col-md-6">
                        <strong>OI:</strong> <?= $consulta['av_oi_lejos'] ?? '-' ?> (Lejos) | <?= $consulta['av_oi_cerca'] ?? '-' ?> (Cerca)
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="text-secondary empty-state-message text-center">Primer paso: Captura la agudeza visual sin lentes.</p>
        <?php endif; ?>
        
        <div class="text-center">
            <a href="/index.php?page=av_live_index&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>&mode=av" 
               class="btn <?= $hasAV ? 'btn-secondary' : 'btn-primary' ?> btn-sm">
                <?= $hasAV ? '✏️ Editar AV' : '🚀 Capturar AV' ?>
            </a>
        </div>
        
        <hr class="my-3">
        
        <?php if ($hasCV): ?>
            <div class="cv-display">
                <h6 class="text-muted">Corrección Visual (Con Lentes)</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>OD:</strong> <?= $consulta['cv_od_lejos'] ?? '-' ?> (Lejos) | <?= $consulta['cv_od_cerca'] ?? '-' ?> (Cerca)
                    </div>
                    <div class="col-md-6">
                        <strong>OI:</strong> <?= $consulta['cv_oi_lejos'] ?? '-' ?> (Lejos) | <?= $consulta['cv_oi_cerca'] ?? '-' ?> (Cerca)
                    </div>
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
