<?php
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
require_once __DIR__ . '/../../Helpers/FormHelper.php';

$_GET['action'] = 'details'; 
$data = handleConsultaAction();

$paciente = $data['paciente'];
$consulta = $data['consulta'];
$graduaciones = $data['graduaciones'];
$catalogoAV = $data['catalogoAV'] ?? []; // El catálogo nuevo

if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$fechaConsulta = FormatHelper::dateFull($consulta['fecha']);

// --- PREPARAR OPCIONES DE AV (Para no repetir código en el HTML) ---
// Generamos el string de <option> una sola vez
$avOptions = '<option value="" selected>-- Seleccionar --</option>';
foreach ($catalogoAV as $av) {
    $avOptions .= '<option value="' . $av['id'] . '">' . htmlspecialchars($av['valor']) . '</option>';
}
?>

<div class="page-header">
    <h1>Taller de Graduaciones</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_index&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
            &larr; Volver al Historial
        </a>
    </div>
</div>

<div class="context-header">
    <div class="card-body">
        <h3>Paciente: <?= htmlspecialchars($fullName) ?></h3>
        <h3>Consulta: <?= $fechaConsulta ?></h3>
    </div>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-header view-actions">
            <button class="btn btn-secondary active" data-view="graduaciones">Graduaciones</button>
            <button class="btn btn-secondary" data-view="biometria">Biometría (DP)</button>
            <button class="btn btn-secondary" data-view="clinicos">Datos Clínicos (AV/CV)</button>
        </div>

        <div class="card-body">

            <div id="view-graduaciones" class="view-panel active">
                
                <h3 class="mb-2">Graduaciones Registradas</h3>
                <?php
                $graduacionesAgrupadas = [];
                foreach ($graduaciones as $grad) {
                    $tipo = $grad['tipo'];
                    $ojo = $grad['ojo']; 
                    if (!isset($graduacionesAgrupadas[$tipo])) {
                        $graduacionesAgrupadas[$tipo] = [
                            'tipo_label' => ucfirst($tipo),
                            'es_final' => $grad['es_graduacion_final'],
                            'OD' => null, 'OI' => null
                        ];
                    }
                    if ($ojo === 'OD' || $ojo === 'AO') $graduacionesAgrupadas[$tipo]['OD'] = $grad;
                    if ($ojo === 'OI' || $ojo === 'AO') $graduacionesAgrupadas[$tipo]['OI'] = $grad;
                }
                ?>

                <?php if (empty($graduacionesAgrupadas)): ?>
                    <p class="text-center mb-2">Aún no hay graduaciones registradas.</p>
                <?php else: ?>
                    <div class="lista-graduaciones mb-2">
                    <?php foreach ($graduacionesAgrupadas as $tipo => $grad): ?>
                        <?php $od = $grad['OD'] ?? []; $oi = $grad['OI'] ?? []; ?>
                        <div class="graduacion-fila" data-tipo="<?= $tipo ?>">
                            <div class="graduacion-columna-tipo">
                                <strong><?= htmlspecialchars($grad['tipo_label']) ?></strong>
                                <?php if($grad['es_final']): ?>
                                    <span class="badge-final">FINAL</span>
                                <?php endif; ?>
                            </div>
                            <div class="graduacion-columna-formulas graduacion-display">
                                <div class="graduacion-formula" 
                                     data-od-esfera="<?= htmlspecialchars($od['esfera'] ?? '0.00') ?>"
                                     data-od-cilindro="<?= htmlspecialchars($od['cilindro'] ?? '0.00') ?>"
                                     data-od-eje="<?= htmlspecialchars($od['eje'] ?? '0') ?>"
                                     data-od-adicion="<?= htmlspecialchars($od['adicion'] ?? '0.00') ?>"
                                     data-oi-esfera="<?= htmlspecialchars($oi['esfera'] ?? '0.00') ?>"
                                     data-oi-cilindro="<?= htmlspecialchars($oi['cilindro'] ?? '0.00') ?>"
                                     data-oi-eje="<?= htmlspecialchars($oi['eje'] ?? '0') ?>"
                                     data-oi-adicion="<?= htmlspecialchars($oi['adicion'] ?? '0.00') ?>">
                                    <span class="graduacion-ojo-label">OD</span>
                                    <span class="valor"><?= htmlspecialchars($od['esfera'] ?? '0.00') ?></span>
                                    <span class="simbolo">=</span>
                                    <span class="valor"><?= htmlspecialchars($od['cilindro'] ?? '0.00') ?></span>
                                    <span class="simbolo">x</span>
                                    <span class="valor"><?= htmlspecialchars($od['eje'] ?? '0') ?></span>
                                    <span class="simbolo">°</span>
                                    <span class="valor valor-add"><?= htmlspecialchars($od['adicion'] ?? '0.00') ?></span>
                                </div>
                                <div class="graduacion-formula">
                                    <span class="graduacion-ojo-label">OI</span>
                                    <span class="valor"><?= htmlspecialchars($oi['esfera'] ?? '0.00') ?></span>
                                    <span class="simbolo">=</span>
                                    <span class="valor"><?= htmlspecialchars($oi['cilindro'] ?? '0.00') ?></span>
                                    <span class="simbolo">x</span>
                                    <span class="valor"><?= htmlspecialchars($oi['eje'] ?? '0') ?></span>
                                    <span class="simbolo">°</span>
                                    <span class="valor valor-add"><?= htmlspecialchars($oi['adicion'] ?? '0.00') ?></span>
                                </div>
                            </div>
                            <div class="graduacion-columna-acciones">
                                <button type="button" class="btn btn-info btn-sm btn-copiar-graduacion" 
                                        title="Copiar valores al formulario">
                                    📋 Copiar
                                </button>
                                <a href="/index.php?page=graduaciones_edit&consulta_id=<?= $consulta['id'] ?>&tipo=<?= $tipo ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">Editar</a>
                                <a href="/index.php?page=graduaciones_delete&consulta_id=<?= $consulta['id'] ?>&tipo=<?= $tipo ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-danger btn-sm">Borrar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <hr class="mb-2">

                <h3>Registrar Nueva Graduación</h3>
                <form action="/graduacion_handler.php?action=store" method="POST">
                    <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                    <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                    <div class="form-group form-group-third">
                        <label for="tipo_graduacion">Tipo de Graduación</label>
                        <select id="tipo_graduacion" name="tipo" required>
                            <option value="final">Final</option>
                            <option value="autorrefractometro">Autorefractómetro</option>
                            <option value="lensometro">Lensómetro</option>
                            <option value="foroptor">Foroptor</option>
                            <option value="ambulatorio">Ambulatorio</option>
                        </select>
                    </div>

                    <div class="graduacion-capture-form">
                        <?= FormHelper::renderGraduationRow('OD') ?>
                        <?= FormHelper::renderGraduationRow('OI') ?>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar Graduación</button>
                    </div>
                </form>
            </div>

            <div id="view-biometria" class="view-panel">
                <h3>Datos Biométricos</h3>
                <form action="/consulta_handler.php?action=update_biometria" method="POST">
                    <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                    <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                    <div class="form-row align-end">
                        <div class="form-group">
                            <label for="dp_lejos_total">DP Lejos Total</label>
                            <input type="number" id="dp_lejos_total" name="dp_lejos_total" 
                                   value="<?= htmlspecialchars($consulta['dp_lejos_total'] ?? '') ?>" 
                                   placeholder="Ej: 62" step="1" min="40" max="80">
                        </div>
                        <div class="form-group">
                            <label for="dp_od">DNP OD</label>
                            <input type="number" id="dp_od" name="dp_od" 
                                   value="<?= htmlspecialchars($consulta['dp_od'] ?? '') ?>" 
                                   placeholder="Ej: 31" step="0.5">
                        </div>
                        <div class="form-group">
                            <label for="dp_oi">DNP OI</label>
                            <input type="number" id="dp_oi" name="dp_oi" 
                                   value="<?= htmlspecialchars($consulta['dp_oi'] ?? '') ?>" 
                                   placeholder="Ej: 31" step="0.5">
                        </div>
                        <div class="form-group">
                            <label for="altura_oblea">Altura / Oblea</label>
                            <input type="text" id="altura_oblea" name="altura_oblea" 
                                   value="<?= htmlspecialchars($consulta['altura_oblea'] ?? '') ?>" 
                                   placeholder="Ej: 22mm">
                        </div>
                        <div class="form-group flex-no-grow">
                            <button type="submit" class="btn btn-primary mb-02">Guardar Medidas</button>
                        </div>
                    </div>
                </form>
            </div>


            <div id="view-clinicos" class="view-panel">
                <div class="row">
                    
                    <!-- COLUMNA IZQUIERDA: AGUDEZA VISUAL (Sin Lentes) -->
                    <div class="col-md-6">
                        <h3>👁️ Agudeza Visual (Sin Lentes)</h3>
                        
                        <!-- AV Registrada -->
                        <?php if (!empty($consulta['av_ao_id']) || !empty($consulta['av_od_id']) || !empty($consulta['av_oi_id'])): ?>
                        <div class="av-display-grid mb-2">
                            <div class="av-item">
                                <strong>AV AO</strong>
                                <span class="av-value"><?= $catalogoAV[array_search($consulta['av_ao_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                            <div class="av-item">
                                <strong>AV OD</strong>
                                <span class="av-value"><?= $catalogoAV[array_search($consulta['av_od_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                            <div class="av-item">
                                <strong>AV OI</strong>
                                <span class="av-value"><?= $catalogoAV[array_search($consulta['av_oi_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-center text-muted mb-2">No se ha capturado la agudeza visual.</p>
                        <?php endif; ?>
                        
                        <hr class="mb-2">
                        
                        <!-- Formulario AV -->
                        <h4 class="mb-2">Capturar / Actualizar AV</h4>
                        <form action="/consulta_handler.php?action=update_clinicos" method="POST">
                            <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                            <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                            <input type="hidden" name="tipo" value="av">
                            
                            <div class="form-group">
                                <label>AV AO (Ambos Ojos)</label>
                                <select name="av_ao_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['av_ao_id'] ?? '') . '"', 'value="' . ($consulta['av_ao_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>AV OD (Ojo Derecho)</label>
                                <select name="av_od_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['av_od_id'] ?? '') . '"', 'value="' . ($consulta['av_od_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>AV OI (Ojo Izquierdo)</label>
                                <select name="av_oi_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['av_oi_id'] ?? '') . '"', 'value="' . ($consulta['av_oi_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?= empty($consulta['av_ao_id']) ? '💾 Guardar AV' : '🔄 Actualizar AV' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- COLUMNA DERECHA: CORRECCIÓN VISUAL (Con Lentes) -->
                    <div class="col-md-6">
                        <h3>👓 Corrección Visual (Con Lentes)</h3>
                        
                        <!-- CV Registrada -->
                        <?php if (!empty($consulta['cv_ao_id']) || !empty($consulta['cv_od_id']) || !empty($consulta['cv_oi_id'])): ?>
                        <div class="cv-display-grid mb-2">
                            <div class="cv-item">
                                <strong>CV AO</strong>
                                <span class="cv-value"><?= $catalogoAV[array_search($consulta['cv_ao_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                            <div class="cv-item">
                                <strong>CV OD</strong>
                                <span class="cv-value"><?= $catalogoAV[array_search($consulta['cv_od_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                            <div class="cv-item">
                                <strong>CV OI</strong>
                                <span class="cv-value"><?= $catalogoAV[array_search($consulta['cv_oi_id'], array_column($catalogoAV, 'id'))]['valor'] ?? '-' ?></span>
                            </div>
                        </div>
                        <?php else: ?>
                        <p class="text-center text-muted mb-2">No se ha capturado la corrección visual.</p>
                        <?php endif; ?>
                        
                        <hr class="mb-2">
                        
                        <!-- Formulario CV -->
                        <h4 class="mb-2">Capturar / Actualizar CV</h4>
                        <form action="/consulta_handler.php?action=update_clinicos" method="POST">
                            <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                            <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                            <input type="hidden" name="tipo" value="cv">
                            
                            <div class="form-group">
                                <label>CV AO (Ambos Ojos)</label>
                                <select name="cv_ao_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['cv_ao_id'] ?? '') . '"', 'value="' . ($consulta['cv_ao_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>CV OD (Ojo Derecho)</label>
                                <select name="cv_od_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['cv_od_id'] ?? '') . '"', 'value="' . ($consulta['cv_od_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>CV OI (Ojo Izquierdo)</label>
                                <select name="cv_oi_id" class="form-control-sm">
                                    <?= str_replace('value="' . ($consulta['cv_oi_id'] ?? '') . '"', 'value="' . ($consulta['cv_oi_id'] ?? '') . '" selected', $avOptions) ?>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <?= empty($consulta['cv_ao_id']) ? '💾 Guardar CV' : '🔄 Actualizar CV' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                </div>
            </div>

        </div> <!-- card-body -->
    </div> <!-- card -->
</div>
