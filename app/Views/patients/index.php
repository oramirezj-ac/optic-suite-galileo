<?php
require_once __DIR__ . '/../../Controllers/PatientController.php';
$patients = handlePatientAction();
$searchTerm = $_GET['search'] ?? '';
?>

<div class="page-header">
    <h1>Pacientes</h1>
    <a href="/index.php?page=patients_create" class="btn btn-primary">➕ Registrar Nuevo Paciente</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <form action="/index.php" method="GET">
                <input type="hidden" name="page" value="patients">
                <div class="search-bar">
                    <input type="search" name="search" placeholder="Buscar por nombre o teléfono..." value="<?= htmlspecialchars($searchTerm) ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Teléfono</th>
                        <th>Edad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron pacientes.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?= htmlspecialchars($patient['nombre'] . ' ' . ($patient['apellido_paterno'] ?? '') . ' ' . ($patient['apellido_materno'] ?? '')) ?></td>
                                
                                <td><?= htmlspecialchars($patient['telefono'] ?? '') ?></td>
                                <td><?= htmlspecialchars($patient['edad'] ?? '') ?></td>
                                <td class="actions-cell">
                                    <a href="/index.php?page=patients_details&id=<?= $patient['id'] ?>" class="btn btn-secondary">Ver Expediente</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>