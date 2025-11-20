<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/GraduacionController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Reutilizamos la lógica de la acción 'edit' para buscar los datos
//    ya que necesita la misma información (paciente, consulta, tipo).
$_GET['action'] = 'edit'; 
$data = handleGraduacionAction();

// 3. Desempaquetamos los 4 grupos de datos
$paciente = $data['paciente'];
$consulta = $data['consulta'];
$graduacion = $data['graduacion']; // Contiene ['OD'] y ['OI']
$tipo = $data['tipo'];

// 4. (Seguridad)
if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Creamos nombres y fechas para mostrar
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$fechaConsulta = FormatHelper::dateFull($consulta['fecha']);
?>

<div class="page-header">
    <h1>Confirmar Borrado de Graduación</h1>
</div>

<div class="context-header">
    <div class="card-body">
        <h3>Paciente: <?= htmlspecialchars($fullName) ?></h3>
        <h3>Consulta: <?= $fechaConsulta ?></h3>
    </div>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            
            <p class="emphasis-text">
                ¿Estás seguro de que quieres borrar permanentemente la graduación de tipo
                <strong>'<?= htmlspecialchars(ucfirst($tipo)) ?>'</strong>?
            </p>
            
            <div class="alert alert-danger">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminarán los datos de OD y OI para este tipo de graduación.
            </div>

            <form action="/graduacion_handler.php?action=delete" method="POST">
                
                <input type="hidden" name="consulta_id" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>"> <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
                
                <div class="form-actions view-actions">
                    <button type="submit" class="btn btn-danger">Sí, borrar permanentemente</button>
                    
                    <a href="/index.php?page=graduaciones_index&consulta_id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</div>