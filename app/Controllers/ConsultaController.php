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
    if ($action === 'store') {
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
                // 3. ¡ÉXITO! Redirigimos a la página de detalles/graduaciones
                // (Esta página 'consultas_details' la crearemos en el sig. paso)
                header('Location: /index.php?page=consultas_details&id=' . $newConsultaId . '&patient_id=' . $data['patient_id']);
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

        // 4. Devolvemos los 3 grupos de datos a la vista
        return [
            'paciente' => $paciente,
            'consulta' => $consulta,
            'graduaciones' => $graduaciones
        ];
        break;
    }
}