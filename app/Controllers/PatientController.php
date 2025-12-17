<?php
/* ==========================================================================
   Controlador para la Gestión de Pacientes (Lógica de Aplicación)
   ========================================================================== */
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/VentaModel.php';

// VOLVEMOS A LA FUNCIÓN ORIGINAL (SIN PARÁMETROS)
function handlePatientAction()
{
    // El 'action' vendrá SIEMPRE de la URL
    $action = $_GET['action'] ?? 'list';
    
    $pdo = getConnection();
    $patientModel = new PatientModel($pdo);
    $consultaModel = new ConsultaModel($pdo);
    $ventaModel = new VentaModel($pdo);

    switch ($action) {
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // 1. Validamos y preparamos los datos (como antes)
                $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
                if ($nombre === null) {
                    header('Location: /index.php?page=patients_create&error=' . urlencode('El campo Nombre es obligatorio.'));
                    exit();
                }

                // Preparamos los datos para el modelo (Store)
                $data = [
                    'nombre' => $nombre,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    // NUEVO: Recibimos las fechas, ignoramos 'edad'
                    'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                    'fecha_primera_visita' => !empty($_POST['fecha_primera_visita']) ? $_POST['fecha_primera_visita'] : date('Y-m-d'),
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'antecedentes_medicos' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
                ];

                // --- INICIO DE LA NUEVA LÓGICA DE CONTROL ---
                
                // 2. Llamamos a nuestra nueva función del Modelo
                $duplicates = $patientModel->findSimilar($data);

                // 3. Decidimos qué hacer
                if (!empty($duplicates)) {
                    // ¡DUPLICADOS ENCONTRADOS!
                    
                    // Guardamos los datos del NUEVO paciente en la sesión
                    // para que la siguiente página pueda usarlos.
                    $_SESSION['new_patient_data'] = $data; 
                    
                    // Redirigimos a la nueva página de revisión (que aún no existe)
                    header('Location: /index.php?page=patients_review');
                    exit();

                } else {
                    // NO HAY DUPLICADOS. Creamos al paciente (flujo normal)
                    
                    $newPatientId = $patientModel->create($data);
                
                    if ($newPatientId) {
                        header('Location: /index.php?page=patients_details&id=' . $newPatientId . '&success=created');
                    } else {
                        header('Location: /index.php?page=patients_create&error=' . urlencode('Error al crear el paciente.'));
                    }
                    exit();
                }
                // --- FIN DE LA NUEVA LÓGICA DE CONTROL ---
            }
            break; // Fin de 'case store'

        case 'update':
            // ... (Esta lógica ya funcionaba, la dejamos como estaba)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? null;
                $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;

                if ($id === null || $nombre === null) {
                    header('Location: /index.php?page=patients&error=invalid_data');
                    exit();
                }
                
               // Preparamos los datos para el modelo (Update)
                $data = [
                    'nombre' => $nombre,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    // NUEVO: Recibimos las fechas
                    'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                    'fecha_primera_visita' => !empty($_POST['fecha_primera_visita']) ? $_POST['fecha_primera_visita'] : null,
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'antecedentes_medicos' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
                ];

                if ($patientModel->update($id, $data)) {
                    // Redirigimos a DETALLES del paciente, pasando el ID
                    header('Location: /index.php?page=patients_details&id=' . $id . '&success=updated');
                } else {
                    $error_message = "Error al actualizar al paciente.";
                    header('Location: /index.php?page=patients_edit&id=' . $id . '&error=' . urlencode($error_message));
                }
                exit();
            }
            break;

        case 'delete':
            // ... (Esta lógica ya funcionaba)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    header('Location: /index.php?page=patients&error=invalid_id');
                    exit();
                }
                
                if ($patientModel->delete($id)) {
                    header('Location: /index.php?page=patients&success=deleted');
                } else {
                    header('Location: /index.php?page=patients&error=delete_failed');
                }
                exit();
            }
            break;
        
       
        // CASO: VER DETALLES DE UN PACIENTE
        case 'details':
            $id = $_GET['id'] ?? null;
            if (!$id) { 
                return false; 
            }

            // 1. Buscamos al paciente
            $patient = $patientModel->getById($id);
            if (!$patient) { return false; }

            // 2. Buscamos resumen de consultas
            $resumenConsultas = $consultaModel->getResumenConsultasPorPaciente($id);

            // 3. Buscamos historial de ventas (NUEVO)
            $ventas = $ventaModel->getAllByPaciente($id);

            // 4. Devolvemos TODO el paquete
            return [
                'patient' => $patient,
                'resumen' => $resumenConsultas,
                'ventas' => $ventas // <-- Dato nuevo
            ];
            break;
        
        /* ==========================================================
           CASO: FORZAR CREACIÓN (Botón "Crear paciente nuevo")
           ========================================================== */
        case 'force_create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos los datos del formulario (de los campos ocultos)
                $data = [
                    'nombre' => !empty($_POST['nombre']) ? $_POST['nombre'] : null,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'antecedentes_medicos' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : '',
                    'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                    'fecha_primera_visita' => !empty($_POST['fecha_primera_visita']) ? $_POST['fecha_primera_visita'] : date('Y-m-d')
                ];

                // 2. Creamos el paciente
                $newPatientId = $patientModel->create($data);
                
                // 3. (MUY IMPORTANTE) Limpiamos la sesión
                unset($_SESSION['new_patient_data']);

                if ($newPatientId) {
                    header('Location: /index.php?page=patients_details&id=' . $newPatientId . '&success=created_forced');
                } else {
                    header('Location: /index.php?page=patients_create&error=' . urlencode('Error al forzar la creación.'));
                }
                exit();
            }
            break;

        /* ==========================================================
           CASO: FORZAR ACTUALIZACIÓN (Botón "Actualizar Paciente")
           ========================================================== */
        case 'force_update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Obtenemos el ID del paciente EXISTENTE a actualizar
                $id = $_POST['id'] ?? null;
                if (!$id) {
                    header('Location: /index.php?page=patients&error=invalid_id');
                    exit();
                }

                // 2. Obtenemos los datos NUEVOS del formulario
                $data = [
                    'nombre' => !empty($_POST['nombre']) ? $_POST['nombre'] : null,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'edad' => !empty($_POST['edad']) ? $_POST['edad'] : null,
                    'antecedentes_medicos' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : '',
                    'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
                    'fecha_primera_visita' => !empty($_POST['fecha_primera_visita']) ? $_POST['fecha_primera_visita'] : null
                ];

                // 3. Llamamos al modelo para actualizar
                if ($patientModel->update($id, $data)) {
                    
                    // 4. (MUY IMPORTANTE) Limpiamos la sesión
                    unset($_SESSION['new_patient_data']);
                    
                    header('Location: /index.php?page=patients_details&id=' . $id . '&success=updated_from_review');
                } else {
                    $error_message = "Error al actualizar el paciente.";
                    header('Location: /index.php?page=patients_review&error=' . urlencode($error_message));
                }
                exit();
            }
            break;

      default:
            // Lógica de Pestañas
            $tab = $_GET['tab'] ?? 'recent'; 
            $patients = [];
            $yearsAvailable = []; // Variable nueva para el selector de años

            // Obtenemos años disponibles siempre (para el selector)
            // Solo si estamos en la pestaña de auditoría para no cargar de más en otras pestañas
            if ($tab === 'audit') {
                $yearsAvailable = $patientModel->getYearsWithSales();
            }

            if ($tab === 'search') {
                // Pestaña Búsqueda
                $searchTerm = $_GET['q'] ?? '';
                if (!empty($searchTerm)) {
                    $patients = $patientModel->getAll($searchTerm);
                }
            } 
            elseif ($tab === 'dates') {
                // Pestaña Por Fechas
                $start = $_GET['date_start'] ?? '';
                $end = $_GET['date_end'] ?? '';
                if (!empty($start) && !empty($end)) {
                    $patients = $patientModel->searchByDateRange($start, $end);
                }
            }
            elseif ($tab === 'audit') {
                // NUEVA PESTAÑA: Auditoría Física
                $auditYear = $_GET['audit_year'] ?? '';
                $auditLetter = $_GET['audit_letter'] ?? '';
                
                if (!empty($auditYear) && !empty($auditLetter)) {
                    $patients = $patientModel->getAuditList($auditYear, $auditLetter);
                }
            }
            elseif ($tab === 'all') {
                // Pestaña Todos
                $patients = $patientModel->getAllPacientes();
            } 
            else {
                // Pestaña Recientes (Default)
                $patients = $patientModel->getRecientes();
            }

            // Retornamos el paquete de datos actualizado
            return [
                'patients' => $patients,
                'activeTab' => $tab,
                'yearsAvailable' => $yearsAvailable // Enviamos los años a la vista
            ];
    }
}