<?php
/* ==========================================================================
   Controlador para Consultas Médicas
   ========================================================================== */
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';

function handleConsultaMedicaAction()
{
    $action = $_GET['action'] ?? $_POST['action'] ?? 'index';
    
    $pdo = getConnection();
    $patientModel = new PatientModel($pdo);
    $consultaModel = new ConsultaModel($pdo);
    
    switch ($action) {
        case 'index':
            // TODO: Obtener historial de consultas médicas
            return [
                'consultas' => []
            ];
            break;
        
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$patientId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Paciente no especificado'));
                    exit();
                }
                
                // Preparar datos de la consulta médica con nombres correctos
                $consultaData = [
                    'patient_id' => $patientId,
                    'usuario_id' => $_SESSION['user_id'] ?? 1, // TODO: Obtener de sesión
                    'fecha' => $_POST['fecha_consulta'] ?? date('Y-m-d'),
                    'motivo_consulta' => 'Médica', // Tipo fijo: Médica (ya se seleccionó antes)
                    'detalle_motivo' => $_POST['motivo'] ?? '', // Detalle del motivo
                    'observaciones' => $_POST['observaciones'] ?? '',
                    'diagnostico_dx' => $_POST['diagnostico'] ?? '',
                    'tratamiento_rx' => $_POST['tratamiento'] ?? '',
                    'tratamiento_rx' => $_POST['tratamiento'] ?? '',
                    'costo_servicio' => $_POST['costo_consulta'] ?? 0,
                    'estado_financiero' => $_POST['estado_financiero'] ?? 'cobrado', // NUEVO: Estado financiero
                    'metodo_pago' => $_POST['metodo_pago'] ?? null, // Nuevo campo real
                    // Campos de agudeza visual (null para consultas médicas)
                    'av_ao_id' => null,
                    'av_od_id' => null,
                    'av_oi_id' => null,
                    // Campos de corrección visual (null para consultas médicas)
                    'cv_ao_id' => null,
                    'cv_od_id' => null,
                    'cv_oi_id' => null,
                    // Campos de distancia pupilar (null para consultas médicas)
                    'dp_lejos_total' => null,
                    'dp_od' => null,
                    'dp_oi' => null,
                    'dp_cerca' => null
                ];
                
                // Procesar productos médicos
                $productosMedicos = [];
                if (isset($_POST['medicamentos']) && is_array($_POST['medicamentos'])) {
                    foreach ($_POST['medicamentos'] as $med) {
                        // Solo agregar si tiene producto_id y cantidad
                        if (!empty($med['producto_id']) && !empty($med['cantidad'])) {
                            $productosMedicos[] = [
                                'producto_id' => $med['producto_id'],
                                'cantidad' => $med['cantidad'],
                                'precio' => $med['precio'] ?? 0
                            ];
                        }
                    }
                }
                
                // Log temporal para debug
                error_log("=== INICIO GUARDADO CONSULTA MÉDICA ===");
                error_log("Patient ID: " . $patientId);
                error_log("Datos consulta: " . print_r($consultaData, true));
                error_log("Productos médicos: " . print_r($productosMedicos, true));
                
                // Crear consulta médica completa (con productos médicos)
                try {
                    $consultaId = $consultaModel->createConsultaCompleta($consultaData, [], $productosMedicos);
                    error_log("Resultado de createConsultaCompleta: " . ($consultaId ? $consultaId : 'false/0'));
                } catch (Exception $e) {
                    error_log("EXCEPCIÓN al crear consulta: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                    $consultaId = false;
                }
                
                if ($consultaId) {
                    error_log("✓ Consulta guardada exitosamente con ID: " . $consultaId);
                    header('Location: /index.php?page=consultas_medicas_index&patient_id=' . $patientId . '&success=consulta_guardada');
                } else {
                    error_log("✗ ERROR: No se pudo guardar la consulta");
                    // Mostrar error en pantalla temporalmente
                    die("ERROR: No se pudo guardar la consulta. Revisa los logs de PHP para más detalles. <br><a href='/index.php?page=consultas_medicas_create&patient_id=" . $patientId . "'>Volver</a>");
                }
                exit();
            }
            break;
        
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_GET['id'] ?? $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=clinica_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }
                
                
                // Preparar datos actualizados
                $updateData = [
                    'fecha' => $_POST['fecha_consulta'] ?? date('Y-m-d'),
                    'motivo_consulta' => 'Médica', // Asegurar que se mantenga como Médica
                    'detalle_motivo' => $_POST['motivo'] ?? '',
                    'observaciones' => $_POST['observaciones'] ?? '',
                    'diagnostico_dx' => $_POST['diagnostico'] ?? '',
                    'tratamiento_rx' => $_POST['tratamiento'] ?? '',
                    'costo_servicio' => $_POST['costo_consulta'] ?? 0,
                    'estado_financiero' => $_POST['estado_financiero'] ?? 'cobrado', // Estado financiero
                    'metodo_pago' => $_POST['metodo_pago'] ?? null
                ];
                
                // Procesar productos médicos (si se enviaron)
                $productosMedicos = [];
                if (isset($_POST['medicamentos']) && is_array($_POST['medicamentos'])) {
                    foreach ($_POST['medicamentos'] as $med) {
                        // Solo agregar si tiene producto_id y cantidad
                        if (!empty($med['producto_id']) && !empty($med['cantidad'])) {
                            $productosMedicos[] = [
                                'producto_id' => $med['producto_id'],
                                'cantidad' => $med['cantidad'],
                                'precio' => $med['precio'] ?? 0
                            ];
                        }
                    }
                }
                
                // Actualizar consulta
                $success = $consultaModel->updateConsulta($consultaId, $updateData);
                
                // Actualizar productos médicos
                if ($success) {
                    $consultaModel->updateProductosMedicos($consultaId, $productosMedicos);
                }
                
                if ($success) {
                    header('Location: /index.php?page=consultas_medicas_details&id=' . $consultaId . '&success=' . urlencode('Consulta actualizada exitosamente'));
                } else {
                    header('Location: /index.php?page=consultas_medicas_edit&id=' . $consultaId . '&error=' . urlencode('Error al actualizar consulta'));
                }
                exit();
            }
            break;
        
        case 'delete':
            // TODO: Eliminar consulta médica
            break;
        
        default:
            return ['consultas' => []];
    }
}
