<?php
require_once __DIR__ . '/../../Controllers/PatientController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// El controlador ya maneja la lógica de tabs internamente
$data = handlePatientAction();
$patients = $data['patients'];
$activeTab = $data['activeTab']; // 'recent', 'all', 'search', 'dates'

// --- FUNCIÓN HELPER PARA DIBUJAR LA TABLA ---
// Evita repetir código HTML en cada pestaña
function renderPatientsTable($patients) {
    if (empty($patients)) {
        echo '<div class="alert alert-secondary text-center">No se encontraron pacientes con estos criterios.</div>';
        return;
    }
    
    echo '<table>
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Edad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($patients as $patient) {
        $nombreCompleto = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
        $edad = FormatHelper::calculateAge($patient['fecha_nacimiento']);
        
        echo "<tr>
                <td>" . htmlspecialchars($nombreCompleto) . "</td>
                <td>" . htmlspecialchars($patient['telefono'] ?? '') . "</td>
                <td>{$edad}</td>
                <td class='actions-cell'>
                    <a href='/index.php?page=patients_details&id={$patient['id']}' class='btn btn-secondary btn-sm'>Ver Expediente</a>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
}
?>

<div class="page-header">
    <h1>Directorio de Pacientes</h1>
    <a href="/index.php?page=patients_create" class="btn btn-primary">➕ Registrar Nuevo Paciente</a>
</div>

<div class="page-content">
    <div class="card">
        
        <div class="card-header view-actions">
            <a href="/index.php?page=patients&tab=recent" class="btn btn-secondary <?= $activeTab === 'recent' ? 'active' : '' ?>">Recientes</a>
            <a href="/index.php?page=patients&tab=all" class="btn btn-secondary <?= $activeTab === 'all' ? 'active' : '' ?>">Todos</a>
            <a href="/index.php?page=patients&tab=search" class="btn btn-secondary <?= $activeTab === 'search' ? 'active' : '' ?>">Buscador</a>
            <a href="/index.php?page=patients&tab=dates" class="btn btn-secondary <?= $activeTab === 'dates' ? 'active' : '' ?>">Por Fechas</a>
        </div>

        <div class="card-body">
            
            <?php if($activeTab === 'recent'): ?>
                <h3>Últimos 10 Pacientes Modificados</h3>
                <p class="text-secondary mb-1">Mostrando los expedientes en los que has trabajado recientemente.</p>
                <?php renderPatientsTable($patients); ?>
            <?php endif; ?>

            <?php if($activeTab === 'all'): ?>
                <h3>Directorio Completo</h3>
                <p class="text-secondary mb-1">Listado alfabético de todos los pacientes registrados.</p>
                <?php renderPatientsTable($patients); ?>
            <?php endif; ?>

            <?php if($activeTab === 'search'): ?>
                <form action="/index.php" method="GET" class="mb-2">
                    <input type="hidden" name="page" value="patients">
                    <input type="hidden" name="tab" value="search">
                    
                    <div class="search-bar">
                        <input type="text" name="q" placeholder="Buscar por Nombre, Apellidos o Teléfono..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
                        <button type="submit" class="btn btn-primary">Buscar</button>
                    </div>
                </form>
                <?php if(isset($_GET['q'])) renderPatientsTable($patients); ?>
            <?php endif; ?>

            <?php if($activeTab === 'dates'): ?>
                <p class="text-secondary">Filtrar por fecha de registro / primera visita.</p>
                <form action="/index.php" method="GET" class="mb-2">
                    <input type="hidden" name="page" value="patients">
                    <input type="hidden" name="tab" value="dates">
                    
                    <div class="form-row align-items-end">
                        <div class="form-group">
                            <label>Fecha Inicio</label>
                            <input type="date" name="date_start" value="<?= htmlspecialchars($_GET['date_start'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Fecha Fin</label>
                            <input type="date" name="date_end" value="<?= htmlspecialchars($_GET['date_end'] ?? '') ?>" required>
                        </div>
                        <div class="form-group flex-no-grow">
                            <button type="submit" class="btn btn-primary mb-02">Filtrar</button>
                        </div>
                    </div>
                </form>
                <?php if(isset($_GET['date_start'])) renderPatientsTable($patients); ?>
            <?php endif; ?>

        </div>
    </div>
</div>