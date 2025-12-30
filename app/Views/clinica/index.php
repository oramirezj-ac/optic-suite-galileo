<?php
/* ==========================================================================
   CLÍNICA - Gestión de Paciente
   ========================================================================== */

require_once __DIR__ . '/../../Controllers/ClinicaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

$_GET['action'] = 'index';
$data = handleClinicaAction();

$patient = $data['patient'] ?? null;
$recentPatients = $data['recentPatients'] ?? [];
$patientId = $_GET['patient_id'] ?? null;

// Si hay paciente, obtener nombre completo
$fullName = '';
$edad = '';
if ($patient) {
    $fullName = implode(' ', array_filter([
        $patient['nombre'], 
        $patient['apellido_paterno'], 
        $patient['apellido_materno']
    ]));
    $edad = $patient['fecha_nacimiento'] ? \FormatHelper::calculateAge($patient['fecha_nacimiento']) : 'Sin datos';
}
?>

<div class="page-header">
    <h1>🏥 Clínica</h1>
    <div class="view-actions">
        <a href="/index.php?page=patients" class="btn btn-secondary">← Volver a Pacientes</a>
    </div>
</div>

<div class="page-content">
    
    <!-- Pestañas -->
    <div class="card">
        <div class="card-header view-actions">
            <button class="btn btn-secondary <?= !$patient ? 'active' : '' ?>" data-view="nuevo">Nuevo Paciente</button>
            <button class="btn btn-secondary" data-view="recientes">Pacientes Recientes</button>
            <button class="btn btn-secondary <?= $patient ? 'active' : '' ?>" data-view="paciente">Paciente</button>
        </div>
        
        <div class="card-body">
            
            <!-- Vista: Nuevo Paciente -->
            <div id="view-nuevo" class="view-panel <?= !$patient ? 'active' : '' ?>">
                <h3>Datos del Nuevo Paciente</h3>
                
                <form id="form-paciente" action="/patient_handler.php" method="POST">
                    <input type="hidden" name="action" value="store">
                    <input type="hidden" name="redirect_to" value="wizard">
                    
                    <!-- Nombre Completo -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre(s)</label>
                            <input type="text" id="nombre" name="nombre" 
                                   placeholder="Ej. Juan Carlos" required>
                        </div>
                        <div class="form-group">
                            <label for="apellido_paterno">Apellido Paterno</label>
                            <input type="text" id="apellido_paterno" name="apellido_paterno" 
                                   placeholder="Ej. García">
                        </div>
                        <div class="form-group">
                            <label for="apellido_materno">Apellido Materno</label>
                            <input type="text" id="apellido_materno" name="apellido_materno" 
                                   placeholder="Ej. López">
                        </div>
                    </div>
                    
                    <!-- Fecha y Edad -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_primera_visita">Fecha de 1ª Visita</label>
                            <input type="date" id="fecha_primera_visita" name="fecha_primera_visita" 
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="edad">Edad</label>
                            <input type="number" id="edad" name="edad_calculadora" 
                                   placeholder="Ej. 25" min="0" max="120">
                        </div>
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" readonly>
                            <small class="text-secondary">Se calcula automáticamente</small>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            ➕ Crear Paciente
                        </button>
                        <a href="/index.php?page=patients" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
            
            <!-- Vista: Pacientes Recientes -->
            <div id="view-recientes" class="view-panel">
                <h3>Últimos 5 Pacientes Editados</h3>
                
                <?php if (empty($recentPatients)): ?>
                    <p class="text-center">No hay pacientes recientes.</p>
                <?php else: ?>
                    <table class="consultation-summary-table">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Edad</th>
                                <th>Última Actualización</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPatients as $p): 
                                $nombreCompleto = implode(' ', array_filter([
                                    $p['nombre'], 
                                    $p['apellido_paterno'], 
                                    $p['apellido_materno']
                                ]));
                                $edadPaciente = $p['fecha_nacimiento'] ? \FormatHelper::calculateAge($p['fecha_nacimiento']) : 'Sin datos';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($nombreCompleto) ?></td>
                                <td><?= $edadPaciente === 'Sin datos' ? $edadPaciente : $edadPaciente . ' años' ?></td>
                                <td><?= \FormatHelper::dateFull($p['fecha_actualizacion'] ?? $p['fecha_primera_visita']) ?></td>
                                <td class="actions-cell">
                                    <a href="/index.php?page=clinica_index&patient_id=<?= $p['id'] ?>" 
                                       class="btn btn-primary btn-sm">
                                        Seleccionar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Vista: Paciente (Datos y Acciones) -->
            <div id="view-paciente" class="view-panel <?= $patient ? 'active' : '' ?>">
                <?php if ($patient): ?>
                <h3>Información del Paciente</h3>
                <div class="data-grid">
                    <div class="data-item full">
                        <strong>Nombre Completo:</strong> <?= htmlspecialchars($fullName) ?>
                    </div>
                    <div class="data-item half">
                        <strong>Edad:</strong> <?= $edad === 'Sin datos' ? $edad : $edad . ' años' ?>
                    </div>
                    <div class="data-item half">
                        <strong>Fecha de 1ª Visita:</strong> <?= \FormatHelper::dateFull($patient['fecha_primera_visita']) ?>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="clinica-footer" style="margin-top: 2rem;">
                    <h4 class="clinica-footer-title">Acciones Disponibles</h4>
                    <div class="clinica-buttons">
                        <a href="/index.php?page=patients_edit&id=<?= $patient['id'] ?>" 
                           class="btn btn-secondary clinica-btn">
                            ✏️ Editar Paciente
                            <br><small class="clinica-btn-subtitle">Modificar datos personales</small>
                        </a>
                        <a href="/index.php?page=clinica_delete&id=<?= $patient['id'] ?>" 
                           class="btn btn-danger clinica-btn">
                            🗑️ Eliminar Paciente
                            <br><small class="clinica-btn-subtitle">Borrar expediente completo</small>
                        </a>
                        <a href="/index.php?page=consultas_lentes_create&patient_id=<?= $patient['id'] ?>" 
                           class="btn btn-primary clinica-btn">
                            👓 Consulta de Lentes
                            <br><small class="clinica-btn-subtitle">Refractiva / Graduación</small>
                        </a>
                        <a href="/index.php?page=consultas_medicas_create&patient_id=<?= $patient['id'] ?>" 
                           class="btn btn-success clinica-btn">
                            🏥 Consulta Médica
                            <br><small class="clinica-btn-subtitle">Diagnóstico / Tratamiento</small>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-center text-secondary">Selecciona un paciente de "Pacientes Recientes" o crea uno nuevo en "Nuevo Paciente".</p>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
    
</div>
