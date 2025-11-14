<?php
/*
 * NOTA DE REFACTORIZACIÓN:
 * Este archivo (edit.php) ahora es "tonto".
 * Se espera que la vista que lo incluye (ej. details.php)
 * ya haya cargado al paciente en una variable llamada $patient.
 * Ya no hace su propia consulta a la BD.
 */

// Si $patient no existe (porque se intentó cargar este archivo directamente)
if (!isset($patient) || !$patient) {
     // Mostramos un error en lugar de cargar un formulario vacío
     echo "<p class='alert alert-danger'>Error: No se pudieron cargar los datos del paciente para editar.</p>";
     
     // Detenemos la ejecución de este include para no mostrar un formulario roto
     return; 
}
?>

<div class="page-header">
    <h1>Editar Paciente: <?= htmlspecialchars($patient['nombre'] . ' ' . $patient['apellido_paterno']) ?></h1>
    <a href="/index.php?page=patients" class="btn btn-secondary">Cancelar</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message" style="margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <form action="/patient_handler.php?action=update" method="POST">
                <input type="hidden" name="id" value="<?= $patient['id'] ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($patient['nombre'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($patient['apellido_paterno'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($patient['apellido_materno'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="domicilio-group">
                        <label for="domicilio">Domicilio</label>
                        <input type="text" id="domicilio" name="domicilio" value="<?= htmlspecialchars($patient['domicilio'] ?? '') ?>">
                    </div>
                    <div class="form-group" id="telefono-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($patient['telefono'] ?? '') ?>">
                    </div>
                    <div class="form-group" id="edad-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" min="1" max="110" value="<?= htmlspecialchars($patient['edad'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="antecedentes_medicos">Antecedentes Médicos</label>
                    <textarea id="antecedentes_medicos" name="antecedentes_medicos" rows="2"><?= htmlspecialchars($patient['antecedentes_medicos'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>