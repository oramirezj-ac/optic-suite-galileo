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
    
    // El wizard y store_complete no requieren patient_id obligatorio
    if (!$patientId && $action !== 'wizard' && $action !== 'store_complete') {
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
                    // Log del error para debugging
                    error_log("Failed to create consulta. Data: " . print_r($data, true));
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
        
        // ========================================================================
        // NUEVAS ACCIONES PARA EL WIZARD DE CONSULTAS
        // ========================================================================
        
        case 'wizard':
            // Mostrar wizard de consultas nuevas
            $patientId = $_GET['patient_id'] ?? null;
            $patient = null;
            
            if ($patientId) {
                $patient = $patientModel->getById($patientId);
            }
            
            return [
                'patient' => $patient,
                'catalogoAV' => $consultaModel->getCatalogoAV(),
                'productosMedicos' => $consultaModel->getCatalogoProductosMedicos()
            ];
            break;
        
        case 'store_complete':
            // Guardar consulta completa desde el wizard
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Obtener datos JSON del body
                $jsonData = file_get_contents('php://input');
                $data = json_decode($jsonData, true);
                
                if (!$data) {
                    // Si no hay JSON, intentar con POST normal
                    $data = $_POST;
                }
                
                // Preparar datos de consulta
                $consultaData = [
                    'patient_id' => $data['paciente_id'] ?? $data['patient_id'],
                    'usuario_id' => $_SESSION['user_id'] ?? 1,
                    'fecha' => $data['fecha'] ?? date('Y-m-d'),
                    'motivo_consulta' => $data['motivo_consulta'] ?? 'Refractiva',
                    'detalle_motivo' => $data['detalle_motivo'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                    'diagnostico_dx' => $data['diagnostico_dx'] ?? null,
                    'tratamiento_rx' => $data['tratamiento_rx'] ?? null,
                    'costo_servicio' => $data['costo_servicio'] ?? 0.00,
                    'estado_financiero' => $data['estado_financiero'] ?? 'cobrado',
                    'av_ao_id' => $data['av_ao_id'] ?? null,
                    'av_od_id' => $data['av_od_id'] ?? null,
                    'av_oi_id' => $data['av_oi_id'] ?? null,
                    'cv_ao_id' => $data['cv_ao_id'] ?? null,
                    'cv_od_id' => $data['cv_od_id'] ?? null,
                    'cv_oi_id' => $data['cv_oi_id'] ?? null,
                    'dp_lejos_total' => $data['dp_lejos_total'] ?? null,
                    'dp_od' => $data['dp_od'] ?? null,
                    'dp_oi' => $data['dp_oi'] ?? null,
                    'dp_cerca' => $data['dp_cerca'] ?? null
                ];
                
                // Preparar graduaciones
                $graduaciones = [];
                
                // Autorefractómetro
                if (!empty($data['auto_od_esfera'])) {
                    $graduaciones[] = [
                        'tipo' => 'autorrefractometro',
                        'ojo' => 'OD',
                        'esfera' => $data['auto_od_esfera'],
                        'cilindro' => $data['auto_od_cilindro'] ?? 0,
                        'eje' => $data['auto_od_eje'] ?? 0,
                        'adicion' => $data['auto_od_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                    $graduaciones[] = [
                        'tipo' => 'autorrefractometro',
                        'ojo' => 'OI',
                        'esfera' => $data['auto_oi_esfera'],
                        'cilindro' => $data['auto_oi_cilindro'] ?? 0,
                        'eje' => $data['auto_oi_eje'] ?? 0,
                        'adicion' => $data['auto_oi_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                }
                
                // Foroptor
                if (!empty($data['foro_od_esfera'])) {
                    $graduaciones[] = [
                        'tipo' => 'foroptor',
                        'ojo' => 'OD',
                        'esfera' => $data['foro_od_esfera'],
                        'cilindro' => $data['foro_od_cilindro'] ?? 0,
                        'eje' => $data['foro_od_eje'] ?? 0,
                        'adicion' => $data['foro_od_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                    $graduaciones[] = [
                        'tipo' => 'foroptor',
                        'ojo' => 'OI',
                        'esfera' => $data['foro_oi_esfera'],
                        'cilindro' => $data['foro_oi_cilindro'] ?? 0,
                        'eje' => $data['foro_oi_eje'] ?? 0,
                        'adicion' => $data['foro_oi_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                }
                
                // Ambulatoria (opcional)
                if (!empty($data['amb_od_esfera'])) {
                    $graduaciones[] = [
                        'tipo' => 'ambulatorio',
                        'ojo' => 'OD',
                        'esfera' => $data['amb_od_esfera'],
                        'cilindro' => $data['amb_od_cilindro'] ?? 0,
                        'eje' => $data['amb_od_eje'] ?? 0,
                        'adicion' => $data['amb_od_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                    $graduaciones[] = [
                        'tipo' => 'ambulatorio',
                        'ojo' => 'OI',
                        'esfera' => $data['amb_oi_esfera'],
                        'cilindro' => $data['amb_oi_cilindro'] ?? 0,
                        'eje' => $data['amb_oi_eje'] ?? 0,
                        'adicion' => $data['amb_oi_adicion'] ?? 0,
                        'es_graduacion_final' => 0
                    ];
                }
                
                // Final
                if (!empty($data['final_od_esfera'])) {
                    $graduaciones[] = [
                        'tipo' => 'final',
                        'ojo' => 'OD',
                        'esfera' => $data['final_od_esfera'],
                        'cilindro' => $data['final_od_cilindro'] ?? 0,
                        'eje' => $data['final_od_eje'] ?? 0,
                        'adicion' => $data['final_od_adicion'] ?? 0,
                        'es_graduacion_final' => 1
                    ];
                    $graduaciones[] = [
                        'tipo' => 'final',
                        'ojo' => 'OI',
                        'esfera' => $data['final_oi_esfera'],
                        'cilindro' => $data['final_oi_cilindro'] ?? 0,
                        'eje' => $data['final_oi_eje'] ?? 0,
                        'adicion' => $data['final_oi_adicion'] ?? 0,
                        'es_graduacion_final' => 1
                    ];
                }
                
                // Preparar productos médicos (si aplica)
                $productosMedicos = $data['productos_medicos'] ?? [];
                
                // Guardar todo en transacción
                $consultaId = $consultaModel->createConsultaCompleta(
                    $consultaData, 
                    $graduaciones, 
                    $productosMedicos
                );
                
                if ($consultaId) {
                    // Si es petición AJAX, devolver JSON
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'consulta_id' => $consultaId]);
                        exit();
                    } else {
                        // Redirección normal
                        header('Location: /index.php?page=consultas_index&patient_id=' . $consultaData['patient_id'] . '&success=created');
                        exit();
                    }
                } else {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        http_response_code(500);
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Error al guardar la consulta']);
                        exit();
                    } else {
                        header('Location: /index.php?page=consultas_wizard&error=' . urlencode('Error al guardar'));
                        exit();
                    }
                }
            }
            break;
    }
}