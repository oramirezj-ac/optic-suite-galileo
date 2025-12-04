<?php
require_once __DIR__ . '/../Controllers/DashboardController.php';
require_once __DIR__ . '/../Helpers/FormatHelper.php';

$data = handleDashboardAction();
$totalPacientes = $data['totalPacientes'];
$totalVentas = $data['totalVentas'];
$ingresosPorAno = $data['ingresosPorAno'];

// Fecha actual larga (Miércoles, 26 de Noviembre...)
$fechaHoy = FormatHelper::dateFull(date('Y-m-d'));
?>

<div class="page-header">
    <h1>
        <small><?= $fechaHoy ?></small><br>
        Dashboard General
    </h1>
</div>

<div class="page-content">

    <div class="data-grid mb-2">
        <div class="card data-item half text-center">
            <div class="card-body">
                <h3 class="text-secondary">Total Pacientes</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                    <?= number_format($totalPacientes) ?>
                </p>
            </div>
        </div>
        
        <div class="card data-item half text-center">
            <div class="card-body">
                <h3 class="text-secondary">Notas Capturadas</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 0.5rem 0;">
                    <?= number_format($totalVentas) ?>
                </p>
            </div>
        </div>
    </div>

    <h2 class="mb-1">Ingresos por año</h2>

    <div class="d-flex flex-wrap mb-2" style="gap: 1rem;">
        <?php foreach ($ingresosPorAno as $anio => $datosAnio): ?>
            <div class="year-card js-year-trigger" data-target="year-<?= $anio ?>">
                <h2><?= $anio ?></h2>
                <p>$<?= number_format($datosAnio['total_anual'], 2) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Desglose Mensual</h3>
        </div>
        <div class="card-body">
            
            <div id="dashboard-empty-state" class="text-center py-2">
                <p class="text-secondary" style="font-size: 1.2rem;">
                    👈 Selecciona un año arriba para ver el detalle mensual.
                </p>
            </div>

            <?php foreach ($ingresosPorAno as $anio => $datosAnio): ?>
                <div id="year-<?= $anio ?>" class="months-container">
                    <h3 class="mb-1 text-secondary">Detalle del año <?= $anio ?></h3>
                    
                    <div class="data-grid">
                        <?php foreach ($datosAnio['meses'] as $mesData): ?>
                            <div class="data-item quarter text-center">
                                <strong><?= $mesData['nombre'] ?></strong>
                                <p class="text-success fw-bold mt-2">
                                    $<?= number_format($mesData['total'], 2) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>