<?php
/* ==========================================================================
   Controlador para la Gestión de Graduaciones
   ========================================================================== */

// 1. Cargamos las dependencias
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';

/**
 * Función principal para manejar las acciones del CRUD de Graduaciones.
 */
function handleGraduacionAction()
{
    $action = $_GET['action'] ?? 'store'; // Por ahora, solo maneja 'store'
    $pdo = getConnection();
    $graduacionModel = new GraduacionModel($pdo);

    switch ($action) {

        case 'edit':
            // 1. Obtenemos los IDs de la URL
            $consultaId = $_GET['consulta_id'] ?? null;
            $tipo = $_GET['tipo'] ?? null;
            $patientId = $_GET['patient_id'] ?? null; // Para navegación

            if (!$consultaId || !$tipo || !$patientId) {
                header('Location: /index.php?page=patients&error=missing_ids');
                exit();
            }

            // 2. Instanciamos todos los modelos necesarios
            $patientModel = new PatientModel($pdo);
            $consultaModel = new ConsultaModel($pdo);
            // $graduacionModel ya está instanciado

            // 3. Buscamos todos los datos que la vista necesitará
            $paciente = $patientModel->getById($patientId);
            $consulta = $consultaModel->getConsultaById($consultaId);
            $graduacion = $graduacionModel->getByConsultaAndType($consultaId, $tipo);

            // 4. Devolvemos el paquete de datos a la vista
            return [
                'paciente' => $paciente,
                'consulta' => $consulta,
                'graduacion' => $graduacion, // Contiene ['OD'] y ['OI']
                'tipo' => $tipo // Pasamos el 'tipo' para el formulario
            ];
            break;
        
        case 'delete':
            // Esta es la acción para 'action=delete'
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos los IDs del formulario oculto
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null; // Para la redirección
                $tipo = $_POST['tipo'] ?? null;

                if (!$consultaId || !$patientId || !$tipo) {
                    // Si faltan datos, volver al paciente
                    header('Location: /index.php?page=patients&error=delete_failed');
                    exit();
                }

                // 2. Llamamos al modelo para borrar
                if ($graduacionModel->deleteGraduacionByType($consultaId, $tipo)) {
                    // 3. Éxito: Redirigimos al "Taller de Graduaciones"
                    header('Location: /index.php?page=graduaciones_index&consulta_id=' . $consultaId . '&patient_id=' . $patientId . '&success=grad_deleted');
                } else {
                    // 4. Error: Redirigimos de vuelta a la confirmación
                    header('Location: /index.php?page=graduaciones_delete&consulta_id=' . $consultaId . '&tipo=' . $tipo . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
        
        case 'update':
            // Esta es la acción para 'action=update'
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos los IDs del formulario
                $consultaId = $_POST['consulta_id'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;
                $tipo = $_POST['tipo'] ?? null;
                $id_od = $_POST['id_od'] ?? null; // ID de la fila OD (puede estar vacío)
                $id_oi = $_POST['id_oi'] ?? null; // ID de la fila OI (puede estar vacío)

                if (!$consultaId || !$patientId || !$tipo) {
                    header('Location: /index.php?page=patients&error=update_failed');
                    exit();
                }

                try {
                    // --- PREPARAMOS DATOS DE OJO DERECHO (OD) ---
                    $dataOD = [
                        'esfera' => $_POST['od_esfera'],
                        'cilindro' => $_POST['od_cilindro'] ?: 0.00,
                        'eje' => $_POST['od_eje'] ?: 0,
                        'adicion' => $_POST['od_adicion'] ?: 0.00,
                        'tipo' => $tipo, // Para el 'create'
                        'ojo' => 'OD',   // Para el 'create'
                        'es_graduacion_final' => ($tipo === 'final') ? 1 : 0 // Para el 'create'
                    ];

                    // Si ya tiene un ID, actualiza. Si no, crea.
                    if (!empty($id_od)) {
                        $graduacionModel->updateGraduacion($id_od, $dataOD);
                    } else {
                        $graduacionModel->create($consultaId, $dataOD);
                    }

                    // --- PREPARAMOS DATOS DE OJO IZQUIERDO (OI) ---
                    $dataOI = [
                        'esfera' => $_POST['oi_esfera'],
                        'cilindro' => $_POST['oi_cilindro'] ?: 0.00,
                        'eje' => $_POST['oi_eje'] ?: 0,
                        'adicion' => $_POST['oi_adicion'] ?: 0.00,
                        'tipo' => $tipo,
                        'ojo' => 'OI',
                        'es_graduacion_final' => ($tipo === 'final') ? 1 : 0
                    ];

                    // Si ya tiene un ID, actualiza. Si no, crea.
                    if (!empty($id_oi)) {
                        $graduacionModel->updateGraduacion($id_oi, $dataOI);
                    } else {
                        $graduacionModel->create($consultaId, $dataOI);
                    }
                    
                    // Usamos 'id' porque eso es lo que espera la página graduaciones_index
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=grad_updated');

                } catch (Exception $e) {
                    // 4. Error: Redirigimos de vuelta a la edición
                    header('Location: /index.php?page=graduaciones_edit&consulta_id=' . $consultaId . '&tipo=' . $tipo . '&patient_id=' . $patientId . '&error=' . urlencode($e->getMessage()));
                }
                exit();
            }
            break;
        
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // IDs para la consulta y para redirigir
                $consultaId = $_POST['consulta_id'];
                $patientId = $_POST['patient_id'];

                // Datos comunes para ambos ojos
                $tipo = $_POST['tipo'];
                $es_final = ($tipo === 'final') ? 1 : 0;;

                try {
                    // --- PREPARAMOS Y GUARDAMOS OJO DERECHO (OD) ---
                    $dataOD = [
                        'tipo' => $tipo,
                        'ojo' => 'OD',
                        'esfera' => $_POST['od_esfera'],
                        'cilindro' => $_POST['od_cilindro'] ?: 0.00,
                        'eje' => $_POST['od_eje'] ?: 0,
                        'adicion' => $_POST['od_adicion'] ?: 0.00,
                        'es_graduacion_final' => $es_final,
                        'observaciones' => null // (Aún no tenemos este campo en el form)
                    ];
                    // Llamamos al modelo para guardar OD
                    $graduacionModel->create($consultaId, $dataOD);

                    // --- PREPARAMOS Y GUARDAMOS OJO IZQUIERDO (OI) ---
                    $dataOI = [
                        'tipo' => $tipo,
                        'ojo' => 'OI',
                        'esfera' => $_POST['oi_esfera'],
                        'cilindro' => $_POST['oi_cilindro'] ?: 0.00,
                        'eje' => $_POST['oi_eje'] ?: 0,
                        'adicion' => $_POST['oi_adicion'] ?: 0.00,
                        'es_graduacion_final' => $es_final,
                        'observaciones' => null
                    ];
                    // Llamamos al modelo para guardar OI
                    $graduacionModel->create($consultaId, $dataOI);

                    // --- ÉXITO ---
                    // Redirigimos de vuelta a la página de graduaciones
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&success=grad_created');

                } catch (Exception $e) {
                    // --- ERROR ---
                    header('Location: /index.php?page=graduaciones_index&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode($e->getMessage()));
                }
                exit();
            }
            break;

        // --- (Aquí irán 'delete', 'update'...) ---
    }
}