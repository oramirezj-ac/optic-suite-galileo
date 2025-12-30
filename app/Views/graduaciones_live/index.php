<?php
/* ==========================================================================
   GRADUACIONES LIVE - Vista Principal (Solo Lectura)
   Muestra todas las cards con datos guardados + botones de acción
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'index'; 
$data = handleConsultaLentesAction();

$paciente = $data['patient'];
$consulta = $data['consultas'][0] ?? null; // Take the first one for now or pass specific ID

if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$edad = $paciente['fecha_nacimiento'] ? \FormatHelper::calculateAge($paciente['fecha_nacimiento']) : 0;

// Obtener graduaciones específicas para mostrar detalle
require_once __DIR__ . '/../../Models/GraduacionModel.php';
$pdo = getConnection();
$graduacionModel = new GraduacionModel($pdo);

// Obtener datos de Autorefractómetro
$autoGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'autorrefractometro');
$hasAuto = !empty($autoGrads['OD']) || !empty($autoGrads['OI']);
$autoIsFinal = !empty($autoGrads['OD']['es_graduacion_final']) || !empty($autoGrads['OI']['es_graduacion_final']);

// Obtener datos de Foroptor
$foroGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'foroptor');
$hasForo = !empty($foroGrads['OD']) || !empty($foroGrads['OI']);
$foroIsFinal = !empty($foroGrads['OD']['es_graduacion_final']) || !empty($foroGrads['OI']['es_graduacion_final']);

// Obtener datos de Ambulatoria
$ambulatoriaGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'ambulatorio');
$hasAmbulatoria = !empty($ambulatoriaGrads['OD']) || !empty($ambulatoriaGrads['OI']);
$ambulatoriaIsFinal = !empty($ambulatoriaGrads['OD']['es_graduacion_final']) || !empty($ambulatoriaGrads['OI']['es_graduacion_final']);

// Obtener datos de Lensómetro
$lensometroGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'lensometro');
$hasLensometro = !empty($lensometroGrads['OD']) || !empty($lensometroGrads['OI']);
$lensometroIsFinal = !empty($lensometroGrads['OD']['es_graduacion_final']) || !empty($lensometroGrads['OI']['es_graduacion_final']);

// Obtener datos de Externa
$externaGrads = $graduacionModel->getByConsultaAndType($consulta['id'], 'externa');
$hasExterna = !empty($externaGrads['OD']) || !empty($externaGrads['OI']);
$externaIsFinal = !empty($externaGrads['OD']['es_graduacion_final']) || !empty($externaGrads['OI']['es_graduacion_final']);

// Obtener catálogo de AV para AV/CV
require_once __DIR__ . '/../../Models/ConsultaModel.php';
$consultaModel = new ConsultaModel($pdo);
$catalogoAV = $consultaModel->getCatalogoAV();

// Crear lookup para AV
$avLookup = [];
foreach ($catalogoAV as $av) {
    $avLookup[$av['id']] = $av['valor'];
}

// Verificar si tiene AV o CV
$hasAV = !empty($consulta['av_ao_id']) || !empty($consulta['av_od_id']) || !empty($consulta['av_oi_id']);
$hasCV = !empty($consulta['cv_ao_id']) || !empty($consulta['cv_od_id']) || !empty($consulta['cv_oi_id']);

// Helper para renderizar ojo
function renderOjoLive($label, $data) {
    if (empty($data)) return '<div class="text-muted">Sin datos</div>';
    
    $html = '<div class="graduacion-formula">';
    $html .= '<span class="graduacion-ojo-label">' . $label . '</span>';
    $html .= '<span class="valor">' . ($data['esfera'] ?? '0.00') . '</span> <span class="simbolo">=</span> ';
    $html .= '<span class="valor">' . ($data['cilindro'] ?? '0.00') . '</span> <span class="simbolo">x</span> ';
    $html .= '<span class="valor">' . ($data['eje'] ?? '0') . '</span> <span class="simbolo">°</span>';
    if (!empty($data['adicion']) && $data['adicion'] != '0.00') {
        $html .= ' <span class="valor valor-add">' . $data['adicion'] . '</span>';
    }
    $html .= '</div>';
    return $html;
}
?>

<!-- Header con info del paciente -->
<div class="patient-header-fixed">
    <div>
        <h3>👤 <?= htmlspecialchars($fullName) ?></h3>
        <p class="text-muted">Consulta del <?= \FormatHelper::dateFull($consulta['fecha']) ?></p>
    </div>
    <div class="header-actions">
        <a href="/index.php?page=consultas_lentes_index&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">← Volver al Historial</a>
    </div>
</div>

<div class="page-content graduaciones-live">
    
    <!-- 0. AV / CV -->
    <div class="card grad-card <?= ($hasAV && $hasCV) ? 'border-success' : '' ?>">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>👁️ Agudeza Visual / Corrección Visual</h4>
            <?php if ($hasAV && $hasCV): ?>
                <span class="badge badge-success">✓ Completado</span>
            <?php elseif ($hasAV || $hasCV): ?>
                <span class="badge badge-warning">½ Parcial</span>
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
                                           onchange="calcularDNPIndex()"
                                           >
                                </div>
                                <div class="form-group mb-2 mx-2">
                                    <label for="dp_od_input" class="small">DNP OD</label>
                                    <input type="number" step="0.5" name="dp_od" 
                                           id="dp_od_input"
                                           class="form-control form-control-sm text-center" 
                                           value="<?= $consulta['dp_od'] ?? '' ?>" 
                                           placeholder="31"
                                           >
                                </div>
                                <div class="form-group mb-2 mx-2">
                                    <label for="dp_oi_input" class="small">DNP OI</label>
                                    <input type="number" step="0.5" name="dp_oi" 
                                           id="dp_oi_input"
                                           class="form-control form-control-sm text-center" 
                                           value="<?= $consulta['dp_oi'] ?? '' ?>" 
                                           placeholder="31"
                                           >
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
                                           onchange="calcularDNPIndex()"
                                           >
                                </div>
                                <div class="form-group mb-2 mx-2">
                                    <label for="dp_od_input" class="small">DNP OD</label>
                                    <input type="number" step="0.5" name="dp_od" 
                                           id="dp_od_input"
                                           class="form-control form-control-sm text-center" 
                                           value="<?= $consulta['dp_od'] ?? '' ?>" 
                                           placeholder="31"
                                           >
                                </div>
                                <div class="form-group mb-2 mx-2">
                                    <label for="dp_oi_input" class="small">DNP OI</label>
                                    <input type="number" step="0.5" name="dp_oi" 
                                           id="dp_oi_input"
                                           class="form-control form-control-sm text-center" 
                                           value="<?= $consulta['dp_oi'] ?? '' ?>" 
                                           placeholder="31"
                                           >
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

</div>

<?php
// Scripts específicos para esta página
$pageScripts = ['/js/graduaciones-dp.js'];
?>
