<?php
/* ==========================================================================
   Controlador para Consultas de Lentes (Refractivas)
   Maneja: Agudeza Visual, Graduaciones, Distancia Pupilar
   ========================================================================== */
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';

function handleConsultaLentesAction()
{
    // Si es POST, la acción suele venir a veces en el query param o en el body
    // Prioridad: GET (para rutas limpias) > POST
    $action = $_GET['action'] ?? $_POST['action'] ?? 'index';
    
    $pdo = getConnection();
    $patientModel = new PatientModel($pdo);
    $consultaModel = new ConsultaModel($pdo);
    $graduacionModel = new GraduacionModel($pdo);
    
    // Validar sesión para acciones de escritura
    if (in_array($action, ['store_av', 'update_av', 'delete_av', 'store_graduaciones']) && !isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit();
    }

    switch ($action) {
        
        /* ==========================================================
           CASO: INDEX - Historial de Consultas
           ========================================================== */
        /* ==========================================================
           CASO: STORE REFRACTIVA (Nueva Consulta)
           ========================================================== */
        case 'store_refractiva':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("DEBUG: store_refractiva HIT. POST: " . print_r($_POST, true));
                $patientId = $_POST['patient_id'] ?? null;
                $fecha = $_POST['fecha_consulta'] ?? date('Y-m-d');
                $motivo = $_POST['motivo'] ?? 'Refractiva';
                $observaciones = $_POST['observaciones'] ?? '';

                if (!$patientId) {
                    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }

                $data = [
                    'patient_id' => $patientId,
                    'usuario_id' => $_SESSION['user_id'],
                    'fecha' => $fecha,
                    'motivo_consulta' => 'Refractiva', // Fijo para este módulo
                    'detalle_motivo' => $motivo,
                    'observaciones' => $observaciones,
                    'costo_servicio' => 0.00,
                    'estado_financiero' => 'pendiente'
                ];

                $newId = $consultaModel->createConsulta($data);

                if ($newId) {
                    // Redirigir al primer paso del flujo: Agudeza Visual
                    header('Location: /index.php?page=av_live_create&consulta_id=' . $newId . '&patient_id=' . $patientId);
                } else {
                    header('Location: /index.php?page=consultas_lentes_create&patient_id=' . $patientId . '&error=' . urlencode('Error al crear consulta'));
                }
                exit();
            }
            break;

        /* ==========================================================
           CASO: UPDATE REFRACTIVA (Editar Dat. Gen. Consulta)
           ========================================================== */
        case 'update_refractiva':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                
                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }

                $updateData = [
                    'fecha' => $_POST['fecha_consulta'] ?? date('Y-m-d'),
                    'motivo_consulta' => 'Refractiva',
                    'detalle_motivo' => $_POST['motivo'] ?? '',
                    'observaciones' => $_POST['observaciones'] ?? '',
                    'costo_servicio' => 0.00,
                    'estado_financiero' => 'pendiente',
                    'metodo_pago' => null // No aplica aqui
                ];

                if ($consultaModel->updateConsulta($consultaId, $updateData)) {
                     header('Location: /index.php?page=consultas_lentes_index&patient_id=' . $patientId . '&success=' . urlencode('Datos actualizados'));
                } else {
                     header('Location: /index.php?page=consultas_lentes_edit&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode('Error al actualizar'));
                }
                exit();
            }
            break;

        /* ==========================================================
           CASO: DELETE REFRACTIVA (Borrar Consulta y Graduaciones)
           ========================================================== */
        case 'delete_refractiva':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                 $consultaId = $_POST['consulta_id'] ?? null;
                 $patientId = $_POST['patient_id'] ?? null;

                 if (!$consultaId) {
                     header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos faltantes'));
                     exit();
                 }

                 // Borrar consulta (El modelo se encarga de borrar graduaciones y productos en cascada)
                 // $pdo->exec("DELETE FROM graduaciones WHERE consulta_id = " . (int)$consultaId);
                 
                 // Borrar consulta
                 if ($consultaModel->deleteConsulta($consultaId)) {
                      header('Location: /index.php?page=consultas_lentes_index&patient_id=' . $patientId . '&success=' . urlencode('Consulta eliminada correctamente'));
                 } else {
                      header('Location: /index.php?page=consultas_lentes_index&patient_id=' . $patientId . '&error=' . urlencode('Error al eliminar'));
                 }
                 exit();
            }
            break;

        /* ==========================================================
           CASO: INDEX - Historial de Consultas
           ========================================================== */
        case 'index':
        case 'details': // Alias for details view
            $patientId = $_GET['patient_id'] ?? null;
            $consultaId = $_GET['id'] ?? $_GET['consulta_id'] ?? null;

             if (!$patientId && !$consultaId) {
                header('Location: /index.php?page=patients');
                exit();
            }

            // If we only have consultaId, find patientId
            if ($consultaId && !$patientId) {
                $consulta = $consultaModel->getConsultaById($consultaId);
                $patientId = $consulta['patient_id'] ?? null;
            }
            
            $patient = $patientModel->getById($patientId);
            
            // If fetch specific ID
            if ($consultaId) {
                 $consultas = [$consultaModel->getConsultaById($consultaId)];
            } else {
                 $consultas = $consultaModel->getAllByPaciente($patientId, 'refractiva');
            }

            return [
                'consultas' => $consultas,
                'patient' => $patient
            ];
            break;

        /* ==========================================================
           CASO: STORE AV (Agudeza Visual)
           ========================================================== */
        case 'store_av':
        case 'update_av':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }

                $avData = [
                    'av_ao_id' => !empty($_POST['av_ao_id']) ? (int)$_POST['av_ao_id'] : null,
                    'av_od_id' => !empty($_POST['av_od_id']) ? (int)$_POST['av_od_id'] : null,
                    'av_oi_id' => !empty($_POST['av_oi_id']) ? (int)$_POST['av_oi_id'] : null,
                ];

                $sql = "UPDATE consultas SET 
                        av_ao_id = :av_ao_id,
                        av_od_id = :av_od_id,
                        av_oi_id = :av_oi_id
                        WHERE id = :consulta_id";
                
                $stmt = $pdo->prepare($sql);
                $avData['consulta_id'] = $consultaId;
                
                if ($stmt->execute($avData)) {
                    // Éxito: Redirigir al índice de graduaciones para el siguiente paso
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Agudeza Visual guardada'));
                } else {
                    header('Location: /index.php?page=av_live_create&consulta_id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode('Error al guardar AV'));
                }
                exit();
            }
            break;

        /* ==========================================================
           CASO: DELETE AV
           ========================================================== */
        case 'delete_av':
            $consultaId = $_GET['consulta_id'] ?? $_POST['consulta_id'] ?? null;
            $patientId = $_GET['patient_id'] ?? $_POST['patient_id'] ?? null;

            if ($consultaId) {
                $sql = "UPDATE consultas SET 
                        av_ao_id = NULL,
                        av_od_id = NULL,
                        av_oi_id = NULL
                        WHERE id = :consulta_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['consulta_id' => $consultaId]);
                
                header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('AV eliminada'));
            } else {
                header('Location: /index.php?page=patients');
            }
            exit();
            break;
        
        /* ==========================================================
           CASO: STORE CV (Corrección Visual)
           ========================================================================== */
        case 'store_cv':
        case 'update_cv':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$consultaId || !$patientId) {
                    header('Location: /index.php?page=consultas_lentes_index&error=' . urlencode('Datos incompletos'));
                    exit();
                }

                $cvData = [
                    'cv_ao_id' => !empty($_POST['cv_ao_id']) ? (int)$_POST['cv_ao_id'] : null,
                    'cv_od_id' => !empty($_POST['cv_od_id']) ? (int)$_POST['cv_od_id'] : null,
                    'cv_oi_id' => !empty($_POST['cv_oi_id']) ? (int)$_POST['cv_oi_id'] : null,
                ];

                $sql = "UPDATE consultas SET 
                        cv_ao_id = :cv_ao_id,
                        cv_od_id = :cv_od_id,
                        cv_oi_id = :cv_oi_id
                        WHERE id = :consulta_id";
                
                $stmt = $pdo->prepare($sql);
                $cvData['consulta_id'] = $consultaId;
                
                if ($stmt->execute($cvData)) {
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Corrección Visual guardada'));
                } else {
                    header('Location: /index.php?page=av_live_create&consulta_id=' . $consultaId . '&patient_id=' . $patientId . '&mode=cv&error=' . urlencode('Error al guardar CV'));
                }
                exit();
            }
            break;
        
        /* ==========================================================
           CASO: UPDATE_DP - Actualizar Distancia Pupilar
           ========================================================== */
        case 'update_dp':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                
                if ($consultaId && $patientId) {
                    $consultaModel = new ConsultaModel($pdo);
                    
                    $updateData = [
                        'dp_lejos_total' => !empty($_POST['dp_lejos_total']) ? (float)$_POST['dp_lejos_total'] : null,
                        'dp_od' => !empty($_POST['dp_od']) ? (float)$_POST['dp_od'] : null,
                        'dp_oi' => !empty($_POST['dp_oi']) ? (float)$_POST['dp_oi'] : null,
                        'dp_cerca' => !empty($_POST['dp_cerca']) ? (float)$_POST['dp_cerca'] : null
                    ];
                    
                    $result = $consultaModel->updateConsultaPartial($consultaId, $updateData);
                    
                    if ($result) {
                        header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Distancia Pupilar guardada') . '#card-dp');
                    } else {
                        header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode('Error al guardar DP') . '#card-dp');
                    }
                    exit();
                }
            }
            
            header('Location: /index.php?page=patients&error=missing_patient_id');
            exit();
        
        /* ==========================================================
           CASO: DELETE_DP - Eliminar Distancia Pupilar
           ========================================================== */
        case 'delete_dp':
            $consultaId = $_GET['consulta_id'] ?? null;
            $patientId = $_GET['patient_id'] ?? null;
            
            if ($consultaId && $patientId) {
                $consultaModel = new ConsultaModel($pdo);
                
                $updateData = [
                    'dp_lejos_total' => null,
                    'dp_od' => null,
                    'dp_oi' => null,
                    'dp_cerca' => null
                ];
                
                $consultaModel->updateConsultaPartial($consultaId, $updateData);
                
                header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Distancia Pupilar eliminada') . '#card-dp');
                exit();
            }
            
            header('Location: /index.php?page=patients&error=missing_patient_id');
            exit();
        
        /* ==========================================================
           CASO: MARK_FINAL - Marcar graduación como final manualmente
           ========================================================== */
        case 'mark_final':
            $tipo = $_GET['tipo'] ?? null;
            $consultaId = $_GET['consulta_id'] ?? null;
            $patientId = $_GET['patient_id'] ?? null;
            
            if ($tipo && $consultaId) {
                $tipoDb = match($tipo) {
                    'lensometro' => 'lensometro',
                    'externa' => 'externa',
                    default => null
                };
                
                if ($tipoDb) {
                    // Quitar flag de todas las demás graduaciones
                    $pdo->prepare("UPDATE graduaciones SET es_graduacion_final = 0 WHERE consulta_id = ?")->execute([$consultaId]);
                    
                    // Marcar esta como final
                    $pdo->prepare("UPDATE graduaciones SET es_graduacion_final = 1 WHERE consulta_id = ? AND tipo = ?")->execute([$consultaId, $tipoDb]);
                    
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Graduación marcada como FINAL') . '#card-' . $tipo);
                    exit();
                }
            }
            
            header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode('Error al marcar graduación'));
            exit();

        /* ==========================================================
           CASO: STORE_GRADUACIONES - Guardar múltiples graduaciones
           ========================================================== */
        case 'store_graduaciones':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                try {
                    if (!isset($_POST['consulta_id']) || !isset($_POST['patient_id'])) {
                        throw new Exception('Datos incompletos');
                    }

                    $consultaId = (int)$_POST['consulta_id'];
                    $patientId = (int)$_POST['patient_id'];

                    $pdo->beginTransaction();

                    // --- 1. ACTUALIZACIÓN DINÁMICA DE CAMPOS DE CONSULTA (AV, DP, CV) ---
                    $fieldsToUpdate = [];
                    $params = ['consulta_id' => $consultaId];

                    // Mapeo de campos posibles a actualizar en la tabla 'consultas'
                    $possibleFields = [
                        'av_od_id', 'av_oi_id', 'av_ao_id', // Agudeza Visual
                        'dp_lejos_total', 'dp_od', 'dp_oi', 'dp_cerca', // Distancia Pupilar
                        'cv_od_id', 'cv_oi_id', 'cv_ao_id'  // Corrección Visual
                    ];

                    foreach ($possibleFields as $field) {
                        if (isset($_POST[$field])) {
                            // Si el campo existe en POST, lo agregamos al UPDATE.
                            // Si viene vacío (''), lo guardamos como NULL.
                            $fieldsToUpdate[] = "$field = :$field";
                            $val = $_POST[$field];
                            $params[$field] = ($val === '') ? null : $val;
                        }
                    }

                    if (!empty($fieldsToUpdate)) {
                        $sql = "UPDATE consultas SET " . implode(', ', $fieldsToUpdate) . " WHERE id = :consulta_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                    }

                    // --- 2. PROCESAMIENTOS DE GRADUACIONES ---
                    // En lugar de borrar TODO, procesamos por tipo si los datos del tipo están presentes.
                    
                    $tiposGraduacion = ['auto', 'foro', 'ambu', 'lens', 'ext', 'final'];
                    $ojos = ['od', 'oi'];

                    foreach ($tiposGraduacion as $tipo) {
                        // Verificamos si se enviaron datos para este tipo (mirando si existe la esfera de algún ojo)
                        $hasDataForType = false;
                        foreach ($ojos as $ojo) {
                            if (isset($_POST["{$tipo}_{$ojo}_esfera"])) {
                                $hasDataForType = true;
                                break;
                            }
                        }
                        
                        // DEBUG TEMPORAL
                        if ($tipo === 'auto') {
                            error_log("DEBUG AUTO HANDLER - Tipo: $tipo");
                            error_log("DEBUG AUTO HANDLER - hasDataForType: " . ($hasDataForType ? 'true' : 'false'));
                            error_log("DEBUG AUTO HANDLER - POST auto_od_esfera: " . ($_POST['auto_od_esfera'] ?? 'NO EXISTE'));
                            error_log("DEBUG AUTO HANDLER - POST auto_oi_esfera: " . ($_POST['auto_oi_esfera'] ?? 'NO EXISTE'));
                        }

                        if ($hasDataForType) {
                            $tipoDb = match ($tipo) {
                                'auto' => 'autorrefractometro',
                                'foro' => 'foroptor',
                                'ambu' => 'ambulatorio',
                                'lens' => 'lensometro',
                                'ext' => 'externa',
                                'final' => 'final',
                                default => 'otra'
                            };

                            // Borramos solo las graduaciones de este tipo específico para recargarlas
                            $deleteStmt = $pdo->prepare("DELETE FROM graduaciones WHERE consulta_id = ? AND tipo = ?");
                            $deleteStmt->execute([$consultaId, $tipoDb]);

                            // Insertamos las nuevas
                            foreach ($ojos as $ojo) {
                                $prefix = "{$tipo}_{$ojo}";
                                if (isset($_POST["{$prefix}_esfera"]) && $_POST["{$prefix}_esfera"] !== '') {
                                    
                                    // Determinar si esta graduación debe marcarse como final
                                    // Lógica: Ambulatoria > Foroptor > Auto > Lensómetro > Externa
                                    $esFinal = 0;
                                    if ($tipo === 'ambu') {
                                        $esFinal = 1; // Ambulatoria siempre es final si existe
                                    } elseif ($tipo === 'foro') {
                                        // Foroptor es final solo si NO hay ambulatoria
                                        $checkAmbu = $pdo->prepare("SELECT COUNT(*) FROM graduaciones WHERE consulta_id = ? AND tipo = 'ambulatorio'");
                                        $checkAmbu->execute([$consultaId]);
                                        $esFinal = ($checkAmbu->fetchColumn() == 0) ? 1 : 0;
                                    } elseif ($tipo === 'auto') {
                                        // Auto es final solo si NO hay foroptor ni ambulatoria
                                        $checkOthers = $pdo->prepare("SELECT COUNT(*) FROM graduaciones WHERE consulta_id = ? AND tipo IN ('foroptor', 'ambulatorio')");
                                        $checkOthers->execute([$consultaId]);
                                        $esFinal = ($checkOthers->fetchColumn() == 0) ? 1 : 0;
                                    }
                                    // lens y ext NO se marcan automáticamente como final (esFinal = 0)
                                    
                                    $graduacionData = [
                                        'tipo' => $tipoDb,
                                        'ojo' => strtoupper($ojo),
                                        'esfera' => (float)$_POST["{$prefix}_esfera"],
                                        'cilindro' => !empty($_POST["{$prefix}_cilindro"]) ? (float)$_POST["{$prefix}_cilindro"] : 0.00,
                                        'eje' => !empty($_POST["{$prefix}_eje"]) ? (int)$_POST["{$prefix}_eje"] : 0,
                                        'adicion' => !empty($_POST["{$prefix}_adicion"]) ? (float)$_POST["{$prefix}_adicion"] : 0.00,
                                        'observaciones' => null,
                                        'es_graduacion_final' => $esFinal
                                    ];
                                    $graduacionModel->create($consultaId, $graduacionData);
                                }
                            }
                            
                            // Actualizar flags de graduación final para mantener jerarquía
                            // Si guardamos ambulatoria, quitamos el flag de foroptor y auto
                            if ($tipo === 'ambu') {
                                $pdo->prepare("UPDATE graduaciones SET es_graduacion_final = 0 WHERE consulta_id = ? AND tipo IN ('foroptor', 'autorrefractometro')")->execute([$consultaId]);
                            } elseif ($tipo === 'foro') {
                                // Si guardamos foroptor, quitamos el flag de auto
                                $pdo->prepare("UPDATE graduaciones SET es_graduacion_final = 0 WHERE consulta_id = ? AND tipo = 'autorrefractometro'")->execute([$consultaId]);
                            }
                        }
                    }

                    $pdo->commit();
                    
                    // Determinar el anchor según el tipo de graduación capturada
                    $stepSource = $_POST['step_source'] ?? 'auto';
                    $anchor = match($stepSource) {
                        'auto' => '#card-auto',
                        'foro' => '#card-foro',
                        'ambulatorio' => '#card-ambulatoria',
                        'lensometro' => '#card-lensometro',
                        'externa' => '#card-externa',
                        'final' => '#card-final',
                        default => ''
                    };
                    
                    // Éxito - redirigir con anchor
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=' . urlencode('Datos guardados correctamente') . $anchor);
                    exit();

                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    header('Location: /index.php?page=graduaciones_live_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode('Error: ' . $e->getMessage()));
                    exit();
                }
            }
            break;

        default:
            return ['consultas' => []];
    }
}
