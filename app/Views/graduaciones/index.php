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
                        <div class="graduacion-fila">
                            <div class="graduacion-columna-tipo">
                                <strong><?= htmlspecialchars($grad['tipo_label']) ?></strong>
                                <?php if($grad['es_final']): ?>
                                    <span class="badge-final">FINAL</span>
                                <?php endif; ?>
                            </div>
                            <div class="graduacion-columna-formulas graduacion-display">
                                <div class="graduacion-formula">
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
                <h3>Agudeza y Corrección Visual</h3>
                <form action="/consulta_handler.php?action=update_clinicos" method="POST">
                    <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                    <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">

                    <div class="form-row">
                        
                        <div class="form-group">
                            <h4>Ambos Ojos (AO)</h4>
                            <label class="mt-2">AV (Entrada)</label>
                            <select name="av_ao_id">
                                <?= str_replace('value="' . ($consulta['av_ao_id'] ?? '') . '"', 'value="' . ($consulta['av_ao_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>

                            <label class="mt-2">CV (Salida)</label>
                            <select name="cv_ao_id">
                                <?= str_replace('value="' . ($consulta['cv_ao_id'] ?? '') . '"', 'value="' . ($consulta['cv_ao_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <h4>Ojo Derecho (OD)</h4>
                            <label class="mt-2">AV (Entrada)</label>
                            <select name="av_od_id">
                                <?= str_replace('value="' . ($consulta['av_od_id'] ?? '') . '"', 'value="' . ($consulta['av_od_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>

                            <label class="mt-2">CV (Salida)</label>
                            <select name="cv_od_id">
                                <?= str_replace('value="' . ($consulta['cv_od_id'] ?? '') . '"', 'value="' . ($consulta['cv_od_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <h4>Ojo Izquierdo (OI)</h4>
                            <label class="mt-2">AV (Entrada)</label>
                            <select name="av_oi_id">
                                <?= str_replace('value="' . ($consulta['av_oi_id'] ?? '') . '"', 'value="' . ($consulta['av_oi_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>

                            <label class="mt-2">CV (Salida)</label>
                            <select name="cv_oi_id">
                                <?= str_replace('value="' . ($consulta['cv_oi_id'] ?? '') . '"', 'value="' . ($consulta['cv_oi_id'] ?? '') . '" selected', $avOptions) ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar Datos Clínicos</button>
                    </div>
                </form>
            </div>

        </div> <!-- card-body -->
    </div> <!-- card -->
</div>