<?php
// 1. Incluimos el controlador
require_once __DIR__ . '/../../Controllers/PatientController.php';
require_once __DIR__ . '/../../Models/ConsultaModel.php';

// 2. ESTA ES LA MAGIA:
// Le decimos al controlador qué acción ejecutar
// ANTES de llamarlo.
$_GET['action'] = 'details';

// 3. Llamamos al controlador (ahora sin parámetros)
// Él leerá $_GET['action'] y $_GET['id'] por sí mismo.
$patient = handlePatientAction();

// 4. Verificamos si el paciente existe
if (!$patient) {
    header('Location: /index.php?page=patients&error=not_found');
    exit();
}

// 5. El resto del código funciona igual
$patientId = $patient['id'];
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));

// 6. OBTENER RESUMEN DE CONSULTAS
$pdo = getConnection(); // Obtenemos la conexión (ya está cargada por el index.php)
$consultaModel = new ConsultaModel($pdo);
$resumenConsultas = $consultaModel->getResumenConsultasPorPaciente($patientId);
?>

<div class="page-header">
    <h1>Expediente: <?= htmlspecialchars($fullName) ?></h1>
    <a href="/index.php?page=patients" class="btn btn-secondary">Volver a la Lista</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header view-actions">
            <button class="btn btn-secondary active" data-view="details">Ver Detalles</button>
            <button class="btn btn-secondary" data-view="edit">Editar Expediente</button>
            <button class="btn btn-secondary" data-view="consults">Consultas</button>
        </div>

        <div class="card-body">
            <div id="view-details" class="view-panel active">
                <h3>Información General</h3>
                <div class="data-grid">
                    <div class="data-item full"><strong>Nombre Completo:</strong> <?= htmlspecialchars($fullName) ?></div>
                    <div class="data-item half"><strong>Domicilio:</strong> <?= htmlspecialchars($patient['domicilio'] ?? 'No especificado') ?></div>
                    <div class="data-item quarter"><strong>Teléfono:</strong> <?= htmlspecialchars($patient['telefono'] ?? 'No especificado') ?></div>
                    <div class="data-item quarter"><strong>Edad:</strong> <?= htmlspecialchars($patient['edad'] ?? 'No especificada') ?></div>
                    <div class="data-item full"><strong>Antecedentes Médicos:</strong><br><?= nl2br(htmlspecialchars($patient['antecedentes_medicos'] ?? 'Sin antecedentes')) ?></div>
                </div>
            </div>

            <div id="view-edit" class="view-panel">
                <h3>Editando Información del Paciente</h3>
                <?php require __DIR__ . '/edit.php'; ?>
            </div>

            <div id="view-consults" class="view-panel">
                <h3>Resumen de Consultas Recientes</h3>
                
                <?php if (empty($resumenConsultas)): ?>
                    
                    <p>Este paciente aún no tiene consultas registradas.</p>
                
                <?php else: ?>
                    
                    <table class="consultation-summary-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Ojo Derecho (OD)</th>
                                <th>Ojo Izquierdo (OI)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumenConsultas as $consulta): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($consulta['fecha']))) ?></td>
                                    <td>
                                        <?= sprintf(
                                            '%s / %s / %s° / %s',
                                            $consulta['od_esfera'] ?? '0.00',
                                            $consulta['od_cilindro'] ?? '0.00',
                                            $consulta['od_eje'] ?? '0',
                                            $consulta['od_adicion'] ?? '0.00'
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= sprintf(
                                            '%s / %s / %s° / %s',
                                            $consulta['oi_esfera'] ?? '0.00',
                                            $consulta['oi_cilindro'] ?? '0.00',
                                            $consulta['oi_eje'] ?? '0',
                                            $consulta['oi_adicion'] ?? '0.00'
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 2rem;">
                    <a href="/index.php?page=consultas_index&patient_id=<?= $patientId ?>" class="btn btn-primary">
                        Administrar Consultas del Paciente
                    </a>
                </div>

            </div>
        </div>

        <div class="card-footer">
            <a href="/index.php?page=patients_delete&id=<?= $patient['id'] ?>" class="btn btn-danger">Borrar Paciente</a>
        </div>
    </div>
</div>