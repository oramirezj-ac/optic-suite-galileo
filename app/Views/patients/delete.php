<?php
// Obtenemos el ID del paciente de la URL.
$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header('Location: /index.php?page=patients');
    exit();
}

// Seleccionamos las columnas de nombre por separado
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT nombre, apellido_paterno, apellido_materno FROM pacientes WHERE id = ?");
$stmt->execute([$patientId]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: /index.php?page=patients');
    exit();
}

// CORRECCIÓN: Construimos el nombre completo de forma segura
// Usamos array_filter para eliminar partes del nombre que puedan estar vacías (null)
// y luego implode para unirlas con un espacio.
$fullName = implode(' ', array_filter([$patient['nombre'], $patient['apellido_paterno'], $patient['apellido_materno']]));
?>

<div class="page-header">
    <h1>Confirmar Borrado de Paciente</h1>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">
                ¿Estás seguro de que quieres borrar permanentemente al paciente <strong><?= htmlspecialchars($fullName) ?></strong>?
            </p>
            <p class="alert alert-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminará el paciente y todo su historial de consultas y ventas asociadas.</p>

            <form action="/patient_handler.php?action=delete" method="POST" style="margin-top: 2rem;">
                <input type="hidden" name="id" value="<?= $patientId ?>">
                <div class="form-actions" style="border-top: none; padding-top: 0; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-danger">Sí, borrar permanentemente</button>
                    <a href="/index.php?page=patients" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>