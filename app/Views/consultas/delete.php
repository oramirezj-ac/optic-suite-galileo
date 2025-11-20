<?php
// 1. Incluimos los helpers y el controlador
require_once __DIR__ . '/../../Controllers/ConsultaController.php';
require_once __DIR__ . '/../../Helpers/FormatHelper.php';

// 2. Reutilizamos la lógica de 'details' para buscar los datos
$_GET['action'] = 'details'; 
$data = handleConsultaAction();

// 3. Desempaquetamos los datos
$paciente = $data['paciente'];
$consulta = $data['consulta'];

// 4. (Seguridad) Si no se encuentran los datos, volvemos
if (!$paciente || !$consulta) {
    header('Location: /index.php?page=patients&error=data_not_found');
    exit();
}

// 5. Formateamos los datos para mostrar
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));
$fechaConsulta = FormatHelper::dateFull($consulta['fecha']);
?>

<div class="page-header">
    <h1>Confirmar Borrado de Consulta</h1>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            
            <p class="emphasis-text">
                ¿Estás seguro de que quieres borrar permanentemente la consulta del 
                <strong><?= $fechaConsulta ?></strong>
                para el paciente <strong><?= htmlspecialchars($fullName) ?></strong>?
            </p>
            
            <div class="alert alert-danger">
                <strong>Advertencia:</strong> Esta acción no se puede deshacer. Se eliminará la consulta y todas las graduaciones asociadas a ella.
            </div>

            <form action="/consulta_handler.php?action=delete" method="POST">
                
                <input type="hidden" name="id_consulta" value="<?= $consulta['id'] ?>">
                <input type="hidden" name="patient_id" value="<?= $paciente['id'] ?>"> 
                
                <div class="form-actions view-actions">
                    <button type="submit" class="btn btn-danger">Sí, borrar permanentemente</button>
                    
                    <a href="/index.php?page=consultas_details&id=<?= $consulta['id'] ?>&patient_id=<?= $paciente['id'] ?>" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
            
        </div>
    </div>
</div>