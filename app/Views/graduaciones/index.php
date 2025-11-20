<?php
// 1. Incluimos los helpers y el controlador de Consultas
// (Usamos el ConsultaController porque su acción 'details' ya 
// nos da los 3 paquetes de datos: Paciente, Consulta, y Graduaciones)
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';
// require_once __DIR__ . '/../../Helpers/FormHelper.php'; // (Aún no lo usamos)

// 2. Forzamos la acción 'details' del ConsultaController
$_GET['action'] = 'details'; 
$data = handleConsultaAction();

// 3. Desempaquetamos los 3 grupos de datos
$paciente = $data['paciente'];
$consulta = $data['consulta'];
$graduaciones = $data['graduaciones'];

// 4. (Seguridad)
if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Creamos nombres y fechas para mostrar
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$fechaConsulta = FormatHelper::dateFull($consulta['fecha']);
?>

<div class="page-header">
    <h1>Graduaciones</h1>
    <div class="view-actions">
        <a href="/index.php?page=consultas_index&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
            &larr; Volver al Historial de Consultas
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0;">Paciente: <?= htmlspecialchars($fullName) ?></h3>
        <h3 style="margin: 0;">Consulta: <?= $fechaConsulta ?></h3>
    </div>
</div>

<div class="page-content">

    <div class="card">
        <div class="card-header">
            <h3>Graduaciones Registradas</h3>
        </div>
        <div class="card-body">

            <?php
            // --- INICIO DE LÓGICA DE AGRUPACIÓN ---
            $graduacionesAgrupadas = [];
            foreach ($graduaciones as $grad) {
                $tipo = $grad['tipo'];
                $ojo = $grad['ojo']; 

                if (!isset($graduacionesAgrupadas[$tipo])) {
                    $graduacionesAgrupadas[$tipo] = [
                        'tipo_label' => ucfirst($tipo),
                        'es_final' => $grad['es_graduacion_final'],
                        'OD' => null,
                        'OI' => null
                    ];
                }
                if ($ojo === 'OD' || $ojo === 'AO') $graduacionesAgrupadas[$tipo]['OD'] = $grad;
                if ($ojo === 'OI' || $ojo === 'AO') $graduacionesAgrupadas[$tipo]['OI'] = $grad;
            }
            // --- FIN DE LÓGICA DE AGRUPACIÓN ---
            ?>

            <?php if (empty($graduacionesAgrupadas)): ?>
                <p style="text-align: center;">Aún no hay graduaciones registradas para esta consulta.</p>
            <?php else: ?>
                
                <div class="lista-graduaciones">
                
                <?php foreach ($graduacionesAgrupadas as $tipo => $grad): ?>
                    <?php
                    $od = $grad['OD'] ?? [];
                    $oi = $grad['OI'] ?? [];
                    ?>
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
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Registrar Nueva Graduación</h3>
        </div>
        <div class="card-body">
            
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
                    
                    <span class="graduacion-ojo-label">OD</span>
                    <div class="graduacion-formula">
                        <input type="number" name="od_esfera" placeholder="Esfera" class="valor" step="0.25" min="-20.00" max="20.00" required>
                        <span class="simbolo">=</span>
                        <input type="number" name="od_cilindro" placeholder="Cilindro" class="valor" step="0.25" max="0.00" min="-10.00" list="valores_cilindro">
                        <span class="simbolo">x</span>
                        <input type="number" name="od_eje" placeholder="Eje" class="valor" min="0" max="180" step="1">
                        <span class="simbolo">°</span>
                        <select name="od_adicion" class="valor valor-add">
                            <option value="0.00" selected>0.00</option>
                            <option value="0.25">0.25</option>
                            <option value="0.50">0.50</option>
                            <option value="0.75">0.75</option>
                            <option value="1.00">1.00</option>
                            <option value="1.25">1.25</option>
                            <option value="1.50">1.50</option>
                            <option value="1.75">1.75</option>
                            <option value="2.00">2.00</option>
                            <option value="2.25">2.25</option>
                            <option value="2.50">2.50</option>
                            <option value="2.75">2.75</option>
                            <option value="3.00">3.00</option>
                        </select>
                    </div>

                    <span class="graduacion-ojo-label">OI</span>
                    <div class="graduacion-formula">
                        <input type="number" name="oi_esfera" placeholder="Esfera" class="valor" step="0.25" min="-20.00" max="20.00" required>
                        <span class="simbolo">=</span>
                        <input type="number" name="oi_cilindro" placeholder="Cilindro" class="valor" step="0.25" max="0.00" min="-10.00" list="valores_cilindro">
                        <span class="simbolo">x</span>
                        <input type="number" name="oi_eje" placeholder="Eje" class="valor" min="0" max="180" step="1">
                        <span class="simbolo">°</span>
                        <select name="oi_adicion" class="valor valor-add">
                            <option value="0.00" selected>0.00</option>
                            <option value="0.25">0.25</option>
                            <option value="0.50">0.50</option>
                            <option value="0.75">0.75</option>
                            <option value="1.00">1.00</option>
                            <option value="1.25">1.25</option>
                            <option value="1.50">1.50</option>
                            <option value="1.75">1.75</option>
                            <option value="2.00">2.00</option>
                            <option value="2.25">2.25</option>
                            <option value="2.50">2.50</option>
                            <option value="2.75">2.75</option>
                            <option value="3.00">3.00</option>
                        </select>
                    </div>

                </div> <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Graduación</button>
                </div>
            </form>

        </div>
    </div>
</div>