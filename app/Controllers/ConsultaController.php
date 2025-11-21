<?php
/* ==========================================================================
   Controlador para la Gestión de Consultas
   ========================================================================== */

// 1. Cargamos las dependencias
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';


/**
 * Función principal para manejar las acciones del CRUD de Consultas.
 */
function handleConsultaAction()
{
    // Obtenemos la conexión y el ID del paciente
    $pdo = getConnection();
    
    $action = $_GET['action'] ?? 'index';
    if ($action === 'store' || $action === 'delete' || $action === 'update_biometria' || $action === 'update_clinicos') {
        $patientId = $_POST['patient_id'] ?? null;
    } else {
        $patientId = $_GET['patient_id'] ?? null;
    }
    
    // Casi todas las acciones de consulta REQUIEREN un ID de paciente.
    if (!$patientId) {
        header('Location: /index.php?page=patients&error=missing_patient_id');
        exit();
    }

    // Instanciamos los modelos que usaremos
    $consultaModel = new ConsultaModel($pdo);
    $patientModel = new PatientModel($pdo); // Para obtener datos del paciente
    $graduacionModel = new GraduacionModel($pdo); // Para gestionar graduaciones

    switch ($action) {
        
        case 'index':
        default:
            // Esta es la acción para 'page=consultas_index'
            
            // 1. Buscamos el historial de consultas
            $consultas = $consultaModel->getAllByPaciente($patientId);
            
            // 2. Buscamos los datos del paciente para el título
            $paciente = $patientModel->getById($patientId);
            
            // 3. Devolvemos AMBOS datos a la vista
            return [
                'paciente' => $paciente,
                'consultas' => $consultas
            ];
            break;
        
        // --- (Aquí irán 'create', 'store', 'edit', 'delete'...) ---
        case 'create':
            // Esta es la acción para 'page=consultas_create'
            
            // 1. Buscamos los datos del paciente para el título
            //    (El patientModel ya lo cargamos al inicio de la función)
            $paciente = $patientModel->getById($patientId);
            
            // 2. Devolvemos solo los datos del paciente
            return [
                'paciente' => $paciente
            ];
            break;

        case 'store':
            // Esta es la acción para 'action=store'
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // 1. Preparamos los datos del formulario
                $data = [
                    'patient_id' => $_POST['patient_id'] ?? null,
                    'usuario_id' => $_SESSION['user_id'], // Obtenemos al usuario de la sesión
                    'fecha' => $_POST['fecha'] ?? date('Y-m-d H:i:s'),
                    'motivo_consulta' => $_POST['motivo_consulta'] ?? null,
                    'detalle_motivo' => !empty($_POST['detalle_motivo']) ? $_POST['detalle_motivo'] : null,
                    'observaciones' => !empty($_POST['observaciones']) ? $_POST['observaciones'] : null
                ];

                // 2. Llamamos al modelo para crear la consulta
                $newConsultaId = $consultaModel->createConsulta($data);

                if ($newConsultaId) {
                    // 3. ¡ÉXITO! Redirigimos al "Index de Graduaciones" 
                    //    Usamos 'page=graduaciones_index' y el parámetro 'id'
                    header('Location: /index.php?page=graduaciones_index&id=' . $newConsultaId . '&patient_id=' . $data['patient_id']);
                } else {
                    // Error
                    header('Location: /index.php?page=consultas_create&patient_id=' . $data['patient_id'] . '&error=create_failed');
                }
                exit();
            }
            break;

        case 'details':
        // Esta es la acción para 'page=consultas_details'
        $consultaId = $_GET['id'] ?? null;
        if (!$consultaId) {
            header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&error=missing_id');
            exit();
        }

        // 1. Buscamos los datos del Paciente
        $paciente = $patientModel->getById($patientId);
        // 2. Buscamos los datos de la Consulta
        $consulta = $consultaModel->getConsultaById($consultaId);
       // 3. Buscamos la lista de Graduaciones
            $graduaciones = $graduacionModel->getAllByConsulta($consultaId);
            
            // 4. (NUEVO) Buscamos el catálogo de Agudeza Visual
            $catalogoAV = $consultaModel->getCatalogoAV();

            // 5. Devolvemos los 4 grupos de datos a la vista
            return [
                'paciente' => $paciente,
                'consulta' => $consulta,
                'graduaciones' => $graduaciones,
                'catalogoAV' => $catalogoAV // <-- Dato nuevo
            ];

        case 'delete':
            // Esta es la acción para 'action=delete'
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos los IDs del formulario oculto
                $consultaId = $_POST['id_consulta'] ?? null;
                $patientId = $_POST['patient_id'] ?? null; // Para la redirección

                if (!$consultaId || !$patientId) {
                    // Si faltan datos, volver al paciente
                    header('Location: /index.php?page=patients&error=delete_failed');
                    exit();
                }

                // 2. Llamamos al modelo para borrar
                if ($consultaModel->deleteConsulta($consultaId)) {
                    // 3. Éxito: Redirigimos al historial del paciente
                    header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&success=consulta_deleted');
                } else {
                    // 4. Error: Redirigimos de vuelta a la confirmación
                    header('Location: /index.php?page=consultas_delete&id=' . $consultaId . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
        
        case 'edit':
            // Esta es la acción para 'page=consultas_edit'
            $consultaId = $_GET['id'] ?? null;
            if (!$consultaId) {
                header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&error=missing_id');
                exit();
            }

            // 1. Buscamos los datos del Paciente
            $paciente = $patientModel->getById($patientId);
            // 2. Buscamos los datos de la Consulta
            $consulta = $consultaModel->getConsultaById($consultaId);

            // 3. Devolvemos el paquete de datos a la vista
            return [
                'paciente' => $paciente,
                'consulta' => $consulta
            ];
            break;

        case 'update':
            // Esta es la acción para 'action=update'
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos los IDs del formulario
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=update_failed');
                    exit();
                }

                // 2. Preparamos el paquete de datos
                $data = [
                    'fecha' => $_POST['fecha'],
                    'motivo_consulta' => $_POST['motivo_consulta'],
                    'detalle_motivo' => !empty($_POST['detalle_motivo']) ? $_POST['detalle_motivo'] : null,
                    'observaciones' => !empty($_POST['observaciones']) ? $_POST['observaciones'] : null
                ];

                // 3. Llamamos al modelo para actualizar
                if ($consultaModel->updateConsulta($consultaId, $data)) {
                    // 4. Éxito: Redirigimos al historial de consultas
                    header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&success=consulta_updated');
                } else {
                    // 5. Error: Redirigimos de vuelta al formulario de edición
                    header('Location: /index.php?page=consultas_edit&id=' . $consultaId . '&patient_id=' . $patientId . '&error=update_failed');
                }
                exit();
            }
            break;

        /* ------------------------------------------------------
           CASE: UPDATE_BIOMETRIA (Guardar DP y Altura)
           ------------------------------------------------------ */
        case 'update_biometria':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. IDs de contexto
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=missing_ids');
                    exit();
                }

                // 2. Preparamos los datos biométricos
                $data = [
                    'dp_lejos_total' => $_POST['dp_lejos_total'] ?? null,
                    'dp_od' => $_POST['dp_od'] ?? null,
                    'dp_oi' => $_POST['dp_oi'] ?? null,
                    'altura_oblea' => $_POST['altura_oblea'] ?? null
                ];

                // 3. Llamamos al modelo
                if ($consultaModel->updateBiometria($consultaId, $data)) {
                    // ÉXITO: Recargamos la misma página
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=bio_updated');
                } else {
                    // ERROR: Recargamos con mensaje de error
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=bio_failed');
                }
                exit();
            }
            break;
        
        /* ------------------------------------------------------
           CASE: UPDATE_CLINICOS (Guardar AV y CV)
           ------------------------------------------------------ */
        case 'update_clinicos':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=missing_ids');
                    exit();
                }

                // Preparamos los IDs de los catálogos
                $data = [
                    'av_od_id' => $_POST['av_od_id'] ?? null,
                    'av_oi_id' => $_POST['av_oi_id'] ?? null,
                    'cv_od_id' => $_POST['cv_od_id'] ?? null,
                    'cv_oi_id' => $_POST['cv_oi_id'] ?? null
                ];

                if ($consultaModel->updateDatosClinicos($consultaId, $data)) {
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&tab=clinicos&success=clinical_updated');
                } else {
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&tab=clinicos&error=clinical_failed');
                }
                exit();
            }
            break;
    }
}