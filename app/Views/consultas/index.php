<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Obtenemos los datos del controlador
// (La acción por defecto es 'index', así que no forzamos nada)
$data = handleConsultaAction(); 

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$consultas = $data['consultas'];

// 4. (Seguridad)
if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

// 5. Creamos el nombre completo
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<!-- ==================================================
     ENCABEZADO DE PÁGINA (Corregido)
     ================================================== -->
<div class="page-header">
    <h1>
        <small>Expediente de:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <!-- Usamos la clase .view-actions para espaciar los botones -->
    <div class="view-actions">
        <a href="/index.php?page=patients_details&id=<?= $paciente['id'] ?>&tab=consults" class="btn btn-secondary">
            &larr; Volver al Expediente
        </a>
        <a href="/index.php?page=consultas_create&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary">
            ➕ Registrar Nueva Consulta
        </a>
    </div>
</div>

<!-- ==================================================
     CONTENIDO DE PÁGINA (La Lista)
     ================================================== -->
<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Historial de Consultas</h3>
        </div>
        <div class="card-body">
            <table>
                <!-- Encabezado de 3 columnas -->
                <thead>
                    <tr>
                        <th class="th-fecha">Fecha</th>
                        <th class="th-graduacion">Graduación Final</th>
                        <th class="th-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consultas)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No se encontraron consultas para este paciente.</td>
                        </tr>
                    <?php else: ?>
                        
                        <!-- 
                          LISTA DE CONSULTAS (Corregida)
                          Iteramos sobre '$consultas'
                        -->
                        <?php foreach ($consultas as $consulta): ?>
                            <tr>
                                <td><?= FormatHelper::dateFull($consulta['fecha']) ?></td>
                                
                                <td>
                                    <div class="graduacion-display">
                                        <!-- OD -->
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OD</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_esfera'] ?? '0.00') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_cilindro'] ?? '0.00') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['od_eje'] ?? '0') ?></span>
                                            <span class="simbolo">°</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['od_adicion'] ?? '0.00') ?></span>
                                        </div>
                                        <!-- OI -->
                                        <div class="graduacion-formula">
                                            <span class="graduacion-ojo-label">OI</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_esfera'] ?? '0.00') ?></span>
                                            <span class="simbolo">=</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_cilindro'] ?? '0.00') ?></span>
                                            <span class="simbolo">x</span>
                                            <span class="valor"><?= htmlspecialchars($consulta['oi_eje'] ?? '0') ?></span>
                                            <span class="simbolo">°</span>
                                            <span class="valor valor-add"><?= htmlspecialchars($consulta['oi_adicion'] ?? '0.00') ?></span>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Célula de Acciones (Corregida) -->
                                <td class="actions-cell">
                                    
                                    <!-- 1. Enlace al nuevo módulo de graduaciones -->
                                    <a href="/index.php?page=graduaciones_index&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-primary">
                                        Graduaciones
                                    </a>
                                    
                                    <!-- 2. Enlace para editar la Cita (fecha, motivo) -->
                                    <a href="/index.php?page=consultas_edit&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary btn-sm">
                                        Editar Cita
                                    </a>

                                    <!-- 3. Enlace para eliminar la Cita -->
                                    <a href="/index.php?page=consultas_delete&id=<?= $consulta['consulta_id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-danger btn-sm">
                                        Eliminar
                                    </a>
                                    
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>