<?php
/* ==========================================================================
   GRADUACIONES LIVE - Crear / Capturar
   Maneja: Autorefractómetro, Foroptor, Ambulatoria, Final
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ConsultaLentesController.php';
require_once __DIR__ . '/../../Models/ConsultaModel.php';

$consultaId = $_GET['id'] ?? $_GET['consulta_id'] ?? null;
$patientId = $_GET['patient_id'] ?? null;
$step = $_GET['step'] ?? 'auto'; // auto, foro, final

if (!$consultaId || !$patientId) {
    header('Location: /index.php?page=consultas_lentes_index&error=missing_params');
    exit();
}

$pdo = getConnection();
$consultaModel = new ConsultaModel($pdo);
$consulta = $consultaModel->getConsultaById($consultaId);

if (!$consulta) {
    echo "Consulta no encontrada";
    exit();
}

// Configuración dinámica según el paso
$stepTitle = '';
$formAction = 'store_graduaciones'; // Acción unificada en el controlador

switch ($step) {
    case 'auto':
        $stepTitle = '🤖 2. Captura Autorefractómetro + DP';
        break;
    case 'foro':
        $stepTitle = '👓 3. Graduación Foroptor';
        break;
    case 'ambulatorio':
        $stepTitle = '🚶 Prueba Ambulatoria';
        break;
    case 'lensometro':
        $stepTitle = '🔍 Lensómetro';
        break;
    case 'externa':
        $stepTitle = '📄 Graduación Externa';
        break;
    case 'final':
        $stepTitle = '✅ 4. Graduación Final';
        break;
    default:
        $stepTitle = 'Captura de Datos';
}

// Manejar parámetros de copia (cuando se usa el botón "Copiar")
$copyOdEsfera = $_GET['copy_od_esfera'] ?? '';
$copyOdCilindro = $_GET['copy_od_cilindro'] ?? '';
$copyOdEje = $_GET['copy_od_eje'] ?? '';
$copyOdAdicion = $_GET['copy_od_adicion'] ?? '';
$copyOiEsfera = $_GET['copy_oi_esfera'] ?? '';
$copyOiCilindro = $_GET['copy_oi_cilindro'] ?? '';
$copyOiEje = $_GET['copy_oi_eje'] ?? '';
$copyOiAdicion = $_GET['copy_oi_adicion'] ?? '';

$hasCopyData = !empty($copyOdEsfera) || !empty($copyOiEsfera);
?>

<div class="page-header">
    <h1><?= htmlspecialchars($stepTitle) ?></h1>
    <a href="/index.php?page=graduaciones_live_index&id=<?= $consultaId ?>&patient_id=<?= $patientId ?>" class="btn btn-secondary">Cancelar</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <form action="/index.php?page=consultas_lentes_index&action=<?= $formAction ?>" method="POST">
                <input type="hidden" name="consulta_id" value="<?= $consultaId ?>">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                <input type="hidden" name="step_source" value="<?= $step ?>">

                <!-- Sección Graduación (Común para todos los pasos, cambia el prefijo) -->
                 <?php 
                    $prefix = match($step) {
                        'auto' => 'auto',
                        'foro' => 'foro',
                        'ambulatorio' => 'ambu',
                        'lensometro' => 'lens',
                        'externa' => 'ext',
                        'final' => 'final',
                        default => 'auto'
                    };
                 ?>
                 
                 <h4 class="mb-3 text-center">👁️ Datos de Graduación (<?= strtoupper($prefix) ?>)</h4>
                 
                 <?php if ($hasCopyData): ?>
                     <div class="alert alert-success mb-3 text-center">
                         ✓ Valores copiados de graduación anterior
                     </div>
                 <?php endif; ?>
                 
                 <div class="graduacion-capture-form">
                    <!-- Ojo Derecho -->
                    <span class="graduacion-ojo-label">OD</span>
                    <div class="graduacion-formula">
                        <input type="number" step="0.25" name="<?= $prefix ?>_od_esfera" 
                               placeholder="Esfera" class="valor" 
                               min="-20.00" max="20.00"
                               value="<?= htmlspecialchars($copyOdEsfera) ?>" required>
                        
                        <span class="simbolo">=</span>

                        <input type="number" step="0.25" name="<?= $prefix ?>_od_cilindro" 
                               placeholder="Cilindro" class="valor"
                               max="0.00" min="-10.00"
                               value="<?= htmlspecialchars($copyOdCilindro) ?>">

                        <span class="simbolo">x</span>

                        <input type="number" step="1" name="<?= $prefix ?>_od_eje" 
                               placeholder="Eje" class="valor"
                               min="0" max="180"
                               value="<?= htmlspecialchars($copyOdEje) ?>">

                        <span class="simbolo">°</span>

                        <?php if ($step === 'foro' || $step === 'final' || $step === 'ambulatorio' || $step === 'lensometro' || $step === 'externa'): ?>
                            <select name="<?= $prefix ?>_od_adicion" class="valor valor-add">
                                <option value="0.00" <?= $copyOdAdicion == '0.00' ? 'selected' : '' ?>>0.00</option>
                                <option value="0.25" <?= $copyOdAdicion == '0.25' ? 'selected' : '' ?>>0.25</option>
                                <option value="0.50" <?= $copyOdAdicion == '0.50' ? 'selected' : '' ?>>0.50</option>
                                <option value="0.75" <?= $copyOdAdicion == '0.75' ? 'selected' : '' ?>>0.75</option>
                                <option value="1.00" <?= $copyOdAdicion == '1.00' ? 'selected' : '' ?>>1.00</option>
                                <option value="1.25" <?= $copyOdAdicion == '1.25' ? 'selected' : '' ?>>1.25</option>
                                <option value="1.50" <?= $copyOdAdicion == '1.50' ? 'selected' : '' ?>>1.50</option>
                                <option value="1.75" <?= $copyOdAdicion == '1.75' ? 'selected' : '' ?>>1.75</option>
                                <option value="2.00" <?= $copyOdAdicion == '2.00' ? 'selected' : '' ?>>2.00</option>
                                <option value="2.25" <?= $copyOdAdicion == '2.25' ? 'selected' : '' ?>>2.25</option>
                                <option value="2.50" <?= $copyOdAdicion == '2.50' ? 'selected' : '' ?>>2.50</option>
                                <option value="2.75" <?= $copyOdAdicion == '2.75' ? 'selected' : '' ?>>2.75</option>
                                <option value="3.00" <?= $copyOdAdicion == '3.00' ? 'selected' : '' ?>>3.00</option>
                                <option value="3.25" <?= $copyOdAdicion == '3.25' ? 'selected' : '' ?>>3.25</option>
                                <option value="3.50" <?= $copyOdAdicion == '3.50' ? 'selected' : '' ?>>3.50</option>
                                <option value="3.75" <?= $copyOdAdicion == '3.75' ? 'selected' : '' ?>>3.75</option>
                                <option value="4.00" <?= $copyOdAdicion == '4.00' ? 'selected' : '' ?>>4.00</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <!-- Ojo Izquierdo -->
                    <span class="graduacion-ojo-label">OI</span>
                    <div class="graduacion-formula">
                        <input type="number" step="0.25" name="<?= $prefix ?>_oi_esfera" 
                               placeholder="Esfera" class="valor" 
                               min="-20.00" max="20.00"
                               value="<?= htmlspecialchars($copyOiEsfera) ?>" required>
                        
                        <span class="simbolo">=</span>

                        <input type="number" step="0.25" name="<?= $prefix ?>_oi_cilindro" 
                               placeholder="Cilindro" class="valor"
                               max="0.00" min="-10.00"
                               value="<?= htmlspecialchars($copyOiCilindro) ?>">

                        <span class="simbolo">x</span>

                        <input type="number" step="1" name="<?= $prefix ?>_oi_eje" 
                               placeholder="Eje" class="valor"
                               min="0" max="180"
                               value="<?= htmlspecialchars($copyOiEje) ?>">

                        <span class="simbolo">°</span>

                        <?php if ($step === 'foro' || $step === 'final' || $step === 'ambulatorio' || $step === 'lensometro' || $step === 'externa'): ?>
                            <select name="<?= $prefix ?>_oi_adicion" class="valor valor-add">
                                <option value="0.00" <?= $copyOiAdicion == '0.00' ? 'selected' : '' ?>>0.00</option>
                                <option value="0.25" <?= $copyOiAdicion == '0.25' ? 'selected' : '' ?>>0.25</option>
                                <option value="0.50" <?= $copyOiAdicion == '0.50' ? 'selected' : '' ?>>0.50</option>
                                <option value="0.75" <?= $copyOiAdicion == '0.75' ? 'selected' : '' ?>>0.75</option>
                                <option value="1.00" <?= $copyOiAdicion == '1.00' ? 'selected' : '' ?>>1.00</option>
                                <option value="1.25" <?= $copyOiAdicion == '1.25' ? 'selected' : '' ?>>1.25</option>
                                <option value="1.50" <?= $copyOiAdicion == '1.50' ? 'selected' : '' ?>>1.50</option>
                                <option value="1.75" <?= $copyOiAdicion == '1.75' ? 'selected' : '' ?>>1.75</option>
                                <option value="2.00" <?= $copyOiAdicion == '2.00' ? 'selected' : '' ?>>2.00</option>
                                <option value="2.25" <?= $copyOiAdicion == '2.25' ? 'selected' : '' ?>>2.25</option>
                                <option value="2.50" <?= $copyOiAdicion == '2.50' ? 'selected' : '' ?>>2.50</option>
                                <option value="2.75" <?= $copyOiAdicion == '2.75' ? 'selected' : '' ?>>2.75</option>
                                <option value="3.00" <?= $copyOiAdicion == '3.00' ? 'selected' : '' ?>>3.00</option>
                                <option value="3.25" <?= $copyOiAdicion == '3.25' ? 'selected' : '' ?>>3.25</option>
                                <option value="3.50" <?= $copyOiAdicion == '3.50' ? 'selected' : '' ?>>3.50</option>
                                <option value="3.75" <?= $copyOiAdicion == '3.75' ? 'selected' : '' ?>>3.75</option>
                                <option value="4.00" <?= $copyOiAdicion == '4.00' ? 'selected' : '' ?>>4.00</option>
                            </select>
                        <?php endif; ?>
                    </div>
                 </div>

                 <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">💾 Guardar Datos</button>
                 </div>

            </form>
        </div>
    </div>
</div>
