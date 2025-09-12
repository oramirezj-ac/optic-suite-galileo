<?php
$patientId = $_GET['id'] ?? null;
if (!$patientId) { header('Location: /index.php?page=patients'); exit(); }

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$patientId]);
$patient = $stmt->fetch();

if (!$patient) { header('Location: /index.php?page=patients'); exit(); }
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
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
                <h3>Historial de Consultas</h3>
                <p>Aquí se mostrará la lista de consultas del paciente en el futuro.</p>
            </div>
        </div>

        <div class="card-footer">
            <a href="/index.php?page=patients_delete&id=<?= $patient['id'] ?>" class="btn btn-danger">Borrar Paciente</a>
        </div>
    </div>
</div>