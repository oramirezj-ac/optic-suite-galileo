<?php
/* ==========================================================================
   Controlador para el Módulo de Clínica (Lógica de Aplicación)
   Maneja consultas refractivas (lentes) y médicas
   ========================================================================== */
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';

function handleClinicaAction()
{
    // Lógica inteligente para detectar la acción
    // Si es POST, la acción viene del formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? $_GET['action'] ?? 'index';
    } else {
        $action = $_GET['action'] ?? 'index';
    }
    
    $pdo = getConnection();
    $patientModel = new PatientModel($pdo);
    $consultaModel = new ConsultaModel($pdo);
    $graduacionModel = new GraduacionModel($pdo);

    switch ($action) {
        
        /* ==========================================================
           CASO: VISTA PRINCIPAL
           ========================================================== */
        case 'index':
        case 'wizard':
            $patientId = $_GET['patient_id'] ?? null;
            $patient = null;
            
            if ($patientId) {
                $patient = $patientModel->getById($patientId);
            }
            
            // Obtener últimos 5 pacientes editados
            $recentPatients = $patientModel->getRecentPatients(5);
            
            return [
                'patient' => $patient,
                'recentPatients' => $recentPatients
            ];
            break;
        
        /* ==========================================================
           CASO: GUARDAR CONSULTA REFRACTIVA (LENTES)
           ========================================================== */
        case 'store_refractiva':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$patientId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
                    exit();
                }
                
                // Obtener usuario_id de la sesión
                $usuario_id = $_SESSION['user_id'] ?? 1;
                
                // Preparar datos de la consulta refractiva
                $consultaData = [
                    'patient_id' => $patientId,
                    'usuario_id' => $usuario_id,
                    'fecha' => $_POST['fecha_consulta'] ?? date('Y-m-d'),
                    'motivo_consulta' => 'Refractiva',
                    'detalle_motivo' => $_POST['motivo'] ?? '',
                    'observaciones' => $_POST['observaciones'] ?? '',
                    
                    // Campos médicos (null para consultas refractivas)
                    'diagnostico_dx' => null,
                    'tratamiento_rx' => null,
                    'costo_servicio' => 0,
                    'estado_financiero' => 'pendiente',
                    
                    // Campos de agudeza visual y distancia pupilar (null por ahora)
                    'av_ao_id' => null,
                    'av_od_id' => null,
                    'av_oi_id' => null,
                    'cv_ao_id' => null,
                    'cv_od_id' => null,
                    'cv_oi_id' => null,
                    'dp_lejos_total' => null,
                    'dp_od' => null,
                    'dp_oi' => null,
                    'dp_cerca' => null,
                    'altura_oblea' => null
                ];
                
                // Log temporal para debug
                error_log("=== GUARDANDO CONSULTA REFRACTIVA ===");
                error_log("Patient ID: " . $patientId);
                error_log("Datos: " . print_r($consultaData, true));
                
                // Crear consulta
                $consultaId = $consultaModel->createConsulta($consultaData);
                
                error_log("Resultado createConsulta: " . ($consultaId ? $consultaId : 'false'));
                
                if ($consultaId) {
                    // Redirigir a graduaciones en vivo para capturar medidas ópticas
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Consulta creada. Ahora capture las graduaciones.'));
                } else {
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&error=' . urlencode('Error al guardar consulta'));
                }
                exit();
            }
            break;
        
        /* ==========================================================
           CASO: GUARDAR CONSULTA MÉDICA
           ========================================================== */
        case 'store_medica':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$patientId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
                    exit();
                }
                
                // Preparar datos de la consulta médica
                $consultaData = [
                    'patient_id' => $patientId,
                    'tipo_consulta' => 'medica',
                    'fecha' => $_POST['fecha_consulta'] ?? date('Y-m-d'),
                    'motivo' => $_POST['motivo'] ?? '',
                    'diagnostico' => $_POST['diagnostico'] ?? '',
                    'tratamiento' => $_POST['tratamiento'] ?? '',
                    'observaciones_generales' => $_POST['observaciones'] ?? '',
                    'presion_intraocular_od' => $_POST['pio_od'] ?? null,
                    'presion_intraocular_oi' => $_POST['pio_oi'] ?? null
                ];
                
                // Crear consulta médica
                $consultaId = $consultaModel->createConsulta($consultaData);
                
                if ($consultaId) {
                    // Si hay productos médicos, guardarlos
                    if (!empty($_POST['productos_medicos'])) {
                        $consultaModel->addProductosMedicos($consultaId, $_POST['productos_medicos']);
                    }
                    
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&success=consulta_guardada');
                } else {
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&error=' . urlencode('Error al guardar consulta'));
                }
                exit();
            }
            break;
        
        /* ==========================================================
           CASO: ACTUALIZAR CONSULTA
           ========================================================== */
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }
                
                // Preparar datos para actualizar
                $consultaData = [
                    'motivo' => $_POST['motivo'] ?? '',
                    'observaciones_generales' => $_POST['observaciones'] ?? '',
                    // Agregar más campos según sea necesario
                ];
                
                if ($consultaModel->updateConsulta($consultaId, $consultaData)) {
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&success=consulta_actualizada');
                } else {
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&error=' . urlencode('Error al actualizar'));
                }
                exit();
            }
            break;
        
        /* ==========================================================
           CASO: ELIMINAR CONSULTA
           ========================================================== */
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$consultaId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('ID no especificado'));
                    exit();
                }
                
                if ($consultaModel->deleteConsulta($consultaId)) {
                    header('Location: /index.php?page=clinica_index&patient_id=' . $patientId . '&success=consulta_eliminada');
                } else {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Error al eliminar'));
                }
                exit();
            }
            break;
        
        default:
            // Por defecto, mostrar vista principal
            return [
                'patient' => null
            ];
    }
}
