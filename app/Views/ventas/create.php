<?php
// 1. Incluimos y ejecutamos el controlador
require_once __DIR__ . '/../../Controllers/VentaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Forzamos la acción 'create'
$_GET['action'] = 'create'; 
$data = handleVentaAction();

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$patientId = $paciente['id'];

// 4. (Seguridad)
if (!$paciente) {
    header('Location: /index.php?page=patients&error=patient_not_found');
    exit();
}

// 5. Nombre completo para el título
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
?>

<div class="page-header">
    <h1>
        <small>Nueva Venta para:</small><br>
        <?= htmlspecialchars($fullName) ?>
    </h1>
    <div class="view-actions">
        <a href="/index.php?page=patients_details&id=<?= $patientId ?>" class="btn btn-secondary">
            Cancelar
        </a>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-header">
            <h3>Datos de la Nota</h3>
        </div>
        <div class="card-body">
            
            <form action="/venta_handler.php?action=store" method="POST">
                <input type="hidden" name="patient_id" value="<?= $patientId ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="numero_nota">Número de Nota (Folio)</label>
                        <input type="number" id="numero_nota" name="numero_nota" placeholder="Ej: 0123" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_venta">Fecha de Venta</label>
                        <input type="date" id="fecha_venta" name="fecha_venta" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="costo_total">Costo Total ($)</label>
                        <input type="number" id="costo_total" name="costo_total" step="0.01" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto_anticipo">Anticipo Inicial ($)</label>
                        <input type="number" id="monto_anticipo" name="monto_anticipo" step="0.01" placeholder="0.00">
                    </div>

                    <div class="form-group">
                        <label for="fecha_anticipo">Fecha del Anticipo</label>
                        <input type="date" id="fecha_anticipo" name="fecha_anticipo" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Descripción de Productos / Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Describa aquí el armazón, micas, tratamientos y cualquier otro detalle de la nota antigua..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Guardar Venta y Registrar Anticipo
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>