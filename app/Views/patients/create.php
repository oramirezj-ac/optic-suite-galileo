<div class="page-header">
    <h1>Registrar Nuevo Paciente</h1>
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

            <form action="/patient_handler.php?action=store" method="POST">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre(s)</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido_paterno">Apellido Paterno</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno">
                    </div>
                    <div class="form-group">
                        <label for="apellido_materno">Apellido Materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" id="domicilio-group">
                        <label for="domicilio">Domicilio</label>
                        <input type="text" id="domicilio" name="domicilio">
                    </div>
                    <div class="form-group" id="telefono-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono">
                    </div>
                    <div class="form-group" id="edad-group">
                        <label for="edad">Edad</label>
                        <input type="number" id="edad" name="edad" min="1" max="110">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="antecedentes_medicos">Antecedentes Médicos</label>
                    <textarea id="antecedentes_medicos" name="antecedentes_medicos" rows="2"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Paciente</button>
                </div>
            </form>
        </div>
    </div>
</div>