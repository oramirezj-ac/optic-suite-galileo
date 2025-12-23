<?php
/* ==========================================================================
   Controlador para la Gestión de Consultas (Lógica Original Restaurada)
   ========================================================================== */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';

function handleConsultaAction()
{
    $pdo = getConnection();
    $action = $_GET['action'] ?? 'index';

    if ($action === 'store' || $action === 'delete' || $action === 'update_biometria' || $action === 'update_clinicos' || $action === 'update') {
        $patientId = $_POST['patient_id'] ?? null;
    } else {
        $patientId = $_GET['patient_id'] ?? null;
    }
    
    if (!$patientId) {
        header('Location: /index.php?page=patients&error=missing_patient_id');
        exit();
    }

    $consultaModel = new ConsultaModel($pdo);
    $patientModel = new PatientModel($pdo);
    $graduacionModel = new GraduacionModel($pdo);

    switch ($action) {
        
        case 'index':
        default:
            $consultas = $consultaModel->getAllByPaciente($patientId);
            $paciente = $patientModel->getById($patientId);
            return [ 'paciente' => $paciente, 'consultas' => $consultas ];
            break;
        
        case 'create':
            $paciente = $patientModel->getById($patientId);
            return [ 'paciente' => $paciente ];
            break;

        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Recibimos los datos tal cual estaban, más los nuevos campos como opcionales/nulos
                // para no romper la base de datos nueva, pero sin lógica de negocio extra.
                $data = [
                    'patient_id' => $_POST['patient_id'] ?? null,
                    'usuario_id' => $_SESSION['user_id'] ?? 1,
                    'fecha' => $_POST['fecha'] ?? date('Y-m-d'),
                    'motivo_consulta' => $_POST['motivo_consulta'] ?? 'Refractiva', // Aquí entra el nuevo valor
                    'detalle_motivo' => !empty($_POST['detalle_motivo']) ? $_POST['detalle_motivo'] : null,
                    'observaciones' => !empty($_POST['observaciones']) ? $_POST['observaciones'] : null,
                    
                    // Campos nuevos en DB se van nulos para no causar error de SQL
                    'diagnostico_dx' => !empty($_POST['diagnostico_dx']) ? $_POST['diagnostico_dx'] : null,
                    'tratamiento_rx' => !empty($_POST['tratamiento_rx']) ? $_POST['tratamiento_rx'] : null,
                    'costo_servicio' => 0.00,
                    'estado_financiero' => 'cobrado'
                ];

                $newConsultaId = $consultaModel->createConsulta($data);

                if ($newConsultaId) {
                    // COMPORTAMIENTO ORIGINAL: Redirigir a Graduaciones
                    header('Location: /index.php?page=graduaciones_index&id=' . $newConsultaId . '&patient_id=' . $data['patient_id']);
                } else {
                    header('Location: /index.php?page=consultas_create&patient_id=' . $data['patient_id'] . '&error=create_failed');
                }
                exit();
            }
            break;

        case 'details':
            $consultaId = $_GET['id'] ?? null;
            if (!$consultaId) {
                header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&error=missing_id');
                exit();
            }
            $paciente = $patientModel->getById($patientId);
            $consulta = $consultaModel->getConsultaById($consultaId);
            $graduaciones = $graduacionModel->getAllByConsulta($consultaId);
            $catalogoAV = $consultaModel->getCatalogoAV();

            return [
                'paciente' => $paciente,
                'consulta' => $consulta,
                'graduaciones' => $graduaciones,
                'catalogoAV' => $catalogoAV 
            ];
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['id_consulta'] ?? null;
                $patientId = $_POST['patient_id'] ?? null; 

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=delete_failed');
                    exit();
                }

                if ($consultaModel->deleteConsulta($consultaId)) {
                    header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&success=consulta_deleted');
                } else {
                    header('Location: /index.php?page=consultas_delete&id=' . $consultaId . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
        
        case 'edit':
            $consultaId = $_GET['id'] ?? null;
            if (!$consultaId) {
                header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&error=missing_id');
                exit();
            }
            $paciente = $patientModel->getById($patientId);
            $consulta = $consultaModel->getConsultaById($consultaId);

            return [ 'paciente' => $paciente, 'consulta' => $consulta ];
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=update_failed');
                    exit();
                }

                $data = [
                    'fecha' => $_POST['fecha'],
                    'motivo_consulta' => $_POST['motivo_consulta'],
                    'detalle_motivo' => !empty($_POST['detalle_motivo']) ? $_POST['detalle_motivo'] : null,
                    'observaciones' => !empty($_POST['observaciones']) ? $_POST['observaciones'] : null,
                    // Campos nuevos nulos/defaults para mantener compatibilidad DB
                    'diagnostico_dx' => !empty($_POST['diagnostico_dx']) ? $_POST['diagnostico_dx'] : null,
                    'tratamiento_rx' => !empty($_POST['tratamiento_rx']) ? $_POST['tratamiento_rx'] : null,
                    'costo_servicio' => 0.00, 
                    'estado_financiero' => 'cobrado'
                ];

                if ($consultaModel->updateConsulta($consultaId, $data)) {
                    header('Location: /index.php?page=consultas_index&patient_id=' . $patientId . '&success=consulta_updated');
                } else {
                    header('Location: /index.php?page=consultas_edit&id=' . $consultaId . '&patient_id=' . $patientId . '&error=update_failed');
                }
                exit();
            }
            break;

        case 'update_biometria':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                if (!$consultaId || !$patientId) { header('Location: /index.php?page=patients&error=missing_ids'); exit(); }

                $data = [
                    'dp_lejos_total' => $_POST['dp_lejos_total'] ?? null,
                    'dp_od' => $_POST['dp_od'] ?? null,
                    'dp_oi' => $_POST['dp_oi'] ?? null,
                    'altura_oblea' => $_POST['altura_oblea'] ?? null
                ];

                if ($consultaModel->updateBiometria($consultaId, $data)) {
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=bio_updated');
                } else {
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=bio_failed');
                }
                exit();
            }
            break;
        
        case 'update_clinicos':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                if (!$consultaId || !$patientId) { header('Location: /index.php?page=patients&error=missing_ids'); exit(); }

                $data = [
                    'av_ao_id' => $_POST['av_ao_id'] ?? null,
                    'av_od_id' => $_POST['av_od_id'] ?? null,
                    'av_oi_id' => $_POST['av_oi_id'] ?? null,
                    'cv_ao_id' => $_POST['cv_ao_id'] ?? null,
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