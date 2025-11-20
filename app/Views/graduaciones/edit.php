<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Forzamos la acción 'edit' del *Controlador de Graduaciones*
// (Aunque usamos el de Consultas para *cargar* los datos)
$_GET['action'] = 'edit'; 
$data = handleConsultaAction(); // Reutilizamos el 'case edit' que ya busca todo

// 3. Desempaquetamos los 4 grupos de datos
$paciente = $data['paciente'];
$consulta = $data['consulta'];
$graduacion = $data['graduacion']; // Contiene ['OD'] y ['OI']
$tipo = $data['tipo'];

// 4. (Seguridad)
if (!$paciente || !$consulta || !$graduacion) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Creamos nombres y fechas para mostrar
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$fechaConsulta = FormatHelper::dateFull($consulta['fecha']);

// 6. Extraemos los datos de OD y OI (o arrays vacíos si uno no existe)
$od = $graduacion['OD'] ?? [];
$oi = $graduacion['OI'] ?? [];
?>

<div class="page-header">
    <h1>Editar Graduación (<?= htmlspecialchars(ucfirst($tipo)) ?>)</h1>
    <div class="view-actions">
        <a href="/index.php?page=graduaciones_index&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
            Cancelar
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
        <div class="card-header">
            <h3>Editando Graduación de tipo '<?= htmlspecialchars(ucfirst($tipo)) ?>'</h3>
        </div>
        <div class="card-body">
            
            <form action="/graduacion_handler.php?action=update" method="POST">
                
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>">
                <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                
                <input type="hidden" name="id_od" value="<?= $od['id'] ?? '' ?>">
                <input type="hidden" name="id_oi" value="<?= $oi['id'] ?? '' ?>">


                <div class="form-group form-group-third">
                    <label for="tipo_graduacion">Tipo de Graduación</label>
                    <select id="tipo_graduacion" name="tipo_disabled" disabled>
                        <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars(ucfirst($tipo)) ?></option>
                    </select>
                </div>

                <div class="graduacion-capture-form">
                    
                    <span class="graduacion-ojo-label">OD</span>
                    <div class="graduacion-formula">
                        <input type="number" name="od_esfera" placeholder="Esfera" class="valor" step="0.25" min="-20.00" max="20.00" value="<?= htmlspecialchars($od['esfera'] ?? '') ?>" required>
                        <span class="simbolo">=</span>
                        <input type="number" name="od_cilindro" placeholder="Cilindro" class="valor" step="0.25" max="0.00" min="-10.00" value="<?= htmlspecialchars($od['cilindro'] ?? '') ?>">
                        <span class="simbolo">x</span>
                        <input type="number" name="od_eje" placeholder="Eje" class="valor" min="0" max="180" step="1" value="<?= htmlspecialchars($od['eje'] ?? '') ?>">
                        <span class="simbolo">°</span>
                        <select name="od_adicion" class="valor valor-add">
                            <?php $add = $od['adicion'] ?? '0.00'; ?>
                            <option value="0.00" <?= $add == '0.00' ? 'selected' : '' ?>>0.00</option>
                            <option value="0.25" <?= $add == '0.25' ? 'selected' : '' ?>>0.25</option>
                            <option value="0.50" <?= $add == '0.50' ? 'selected' : '' ?>>0.50</option>
                            <option value="0.75" <?= $add == '0.75' ? 'selected' : '' ?>>0.75</option>
                            <option value="1.00" <?= $add == '1.00' ? 'selected' : '' ?>>1.00</option>
                            <option value="1.25" <?= $add == '1.25' ? 'selected' : '' ?>>1.25</option>
                            <option value="1.50" <?= $add == '1.50' ? 'selected' : '' ?>>1.50</option>
                            <option value="1.75" <?= $add == '1.75' ? 'selected' : '' ?>>1.75</option>
                            <option value="2.00" <?= $add == '2.00' ? 'selected' : '' ?>>2.00</option>
                            <option value="2.25" <?= $add == '2.25' ? 'selected' : '' ?>>2.25</option>
                            <option value="2.50" <?= $add == '2.50' ? 'selected' : '' ?>>2.50</option>
                            <option value="2.75" <?= $add == '2.75' ? 'selected' : '' ?>>2.75</option>
                            <option value="3.00" <?= $add == '3.00' ? 'selected' : '' ?>>3.00</option>
                            <option value="3.25" <?= $add == '3.25' ? 'selected' : '' ?>>3.25</option>
                            <option value="3.50" <?= $add == '3.50' ? 'selected' : '' ?>>3.50</option>
                            <option value="3.75" <?= $add == '3.75' ? 'selected' : '' ?>>3.75</option>
                            <option value="4.00" <?= $add == '4.00' ? 'selected' : '' ?>>4.00</option>
                        </select>
                    </div>

                    <span class="graduacion-ojo-label">OI</span>
                    <div class="graduacion-formula">
                        <input type="number" name="oi_esfera" placeholder="Esfera" class="valor" step="0.25" min="-20.00" max="20.00" value="<?= htmlspecialchars($oi['esfera'] ?? '') ?>" required>
                        <span class="simbolo">=</span>
                        <input type="number" name="oi_cilindro" placeholder="Cilindro" class="valor" step="0.25" max="0.00" min="-10.00" value="<?= htmlspecialchars($oi['cilindro'] ?? '') ?>">
                        <span class="simbolo">x</span>
                        <input type="number" name="oi_eje" placeholder="Eje" class="valor" min="0" max="180" step="1" value="<?= htmlspecialchars($oi['eje'] ?? '') ?>">
                        <span class="simbolo">°</span>
                        <select name="oi_adicion" class="valor valor-add">
                            <?php $add = $oi['adicion'] ?? '0.00'; ?>
                            <option value="0.00" <?= $add == '0.00' ? 'selected' : '' ?>>0.00</option>
                            <option value="0.25" <?= $add == '0.25' ? 'selected' : '' ?>>0.25</option>
                            <option value="0.50" <?= $add == '0.50' ? 'selected' : '' ?>>0.50</option>
                            <option value="0.75" <?= $add == '0.75' ? 'selected' : '' ?>>0.75</option>
                            <option value="1.00" <?= $add == '1.00' ? 'selected' : '' ?>>1.00</option>
                            <option value="1.25" <?= $add == '1.25' ? 'selected' : '' ?>>1.25</option>
                            <option value="1.50" <?= $add == '1.50' ? 'selected' : '' ?>>1.50</option>
                            <option value="1.75" <?= $add == '1.75' ? 'selected' : '' ?>>1.75</option>
                            <option value="2.00" <?= $add == '2.00' ? 'selected' : '' ?>>2.00</option>
                            <option value="2.25" <?= $add == '2.25' ? 'selected' : '' ?>>2.25</option>
                            <option value="2.50" <?= $add == '2.50' ? 'selected' : '' ?>>2.50</option>
                            <option value="2.75" <?= $add == '2.75' ? 'selected' : '' ?>>2.75</option>
                            <option value="3.00" <?= $add == '3.00' ? 'selected' : '' ?>>3.00</option>
                            <option value="3.25" <?= $add == '3.25' ? 'selected' : '' ?>>3.25</option>
                            <option value="3.50" <?= $add == '3.50' ? 'selected' : '' ?>>3.50</option>
                            <option value="3.75" <?= $add == '3.75' ? 'selected' : '' ?>>3.75</option>
                            <option value="4.00" <?= $add == '4.00' ? 'selected' : '' ?>>4.00</option>
                        </select>
                    </div>

                </div> <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar Graduación</button>
                </div>
            </form>

        </div>
    </div>
</div>