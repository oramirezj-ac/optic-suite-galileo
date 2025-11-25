<?php
/* ==========================================================================
   Controlador para la Gestión de Pacientes (MEJORADO CON BEST PRACTICES)
   
   Este controlador demuestra la implementación de:
   - Validación centralizada con Validator
   - Protección CSRF con SecurityHelper
   - Logging con Logger
   - Respuestas estandarizadas con Response
   ========================================================================== */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/PatientModel.php';
require_once __DIR__ . '/../Models/ConsultaModel.php';
require_once __DIR__ . '/../Models/VentaModel.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Helpers/SecurityHelper.php';

function handlePatientActionImproved()
{
    $action = $_GET['action'] ?? 'list';
    
    $pdo = getConnection();
    $patientModel = new PatientModel($pdo);
    $consultaModel = new ConsultaModel($pdo);
    $ventaModel = new VentaModel($pdo);

    switch ($action) {
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. VALIDACIÓN DE TOKEN CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Logger::warning('Intento de CSRF en creación de paciente', [
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                    ]);
                    Response::redirectWithError('patients_create', 'Token de seguridad inválido');
                    return;
                }
                
                // 2. VALIDACIÓN DE DATOS CON VALIDATOR
                $validator = new Validator($_POST);
                $validator
                    ->required(['nombre'], 'El nombre es obligatorio')
                    ->maxLength('nombre', 100, 'El nombre no debe exceder 100 caracteres')
                    ->maxLength('apellido_paterno', 100)
                    ->maxLength('apellido_materno', 100)
                    ->maxLength('domicilio', 255);
                
                // Validar teléfono solo si se proporciona
                if (!empty($_POST['telefono'])) {
                    $validator->phone('telefono', 'El teléfono debe tener 10 dígitos');
                }
                
                // Validar edad solo si se proporciona
                if (!empty($_POST['edad'])) {
                    $validator
                        ->integer('edad', 'La edad debe ser un número entero')
                        ->between('edad', 1, 150, 'La edad debe estar entre 1 y 150 años');
                }
                
                // 3. VERIFICAR SI HAY ERRORES DE VALIDACIÓN
                if (!$validator->isValid()) {
                    $errors = $validator->getFirstErrors();
                    $errorMessage = implode(', ', $errors);
                    
                    Logger::info('Validación fallida al crear paciente', [
                        'errors' => $errors,
                        'user_id' => $_SESSION['user_id'] ?? null
                    ]);
                    
                    Response::redirectWithError('patients_create', $errorMessage);
                    return;
                }
                
                // 4. PREPARAR DATOS VALIDADOS Y LIMPIOS
                $data = [
                    'nombre' => SecurityHelper::sanitizeString($validator->get('nombre')),
                    'apellido_paterno' => SecurityHelper::sanitizeString($validator->get('apellido_paterno') ?? ''),
                    'apellido_materno' => SecurityHelper::sanitizeString($validator->get('apellido_materno') ?? ''),
                    'domicilio' => SecurityHelper::sanitizeString($validator->get('domicilio') ?? ''),
                    'telefono' => $validator->get('telefono') ? SecurityHelper::sanitizePhone($validator->get('telefono')) : null,
                    'edad' => $validator->get('edad') ?? null,
                    'antecedentes' => SecurityHelper::sanitizeString($validator->get('antecedentes_medicos') ?? '')
                ];
                
                // 5. VERIFICACIÓN DE DUPLICADOS
                try {
                    $duplicates = $patientModel->findSimilar($data);
                    
                    if (!empty($duplicates)) {
                        // Guardar datos en sesión para revisión
                        $_SESSION['new_patient_data'] = $data;
                        
                        Logger::info('Posibles duplicados encontrados', [
                            'patient_name' => $data['nombre'] . ' ' . $data['apellido_paterno'],
                            'duplicates_count' => count($duplicates)
                        ]);
                        
                        Response::redirect('/index.php?page=patients_review');
                        return;
                    }
                    
                    // 6. CREAR PACIENTE (NO HAY DUPLICADOS)
                    $newPatientId = $patientModel->create($data);
                    
                    if ($newPatientId) {
                        // Logging de actividad exitosa
                        Logger::userActivity('Paciente creado', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $newPatientId,
                            'patient_name' => $data['nombre'] . ' ' . $data['apellido_paterno']
                        ]);
                        
                        Response::redirectWithSuccess(
                            'patients_details',
                            'Paciente creado exitosamente',
                            ['id' => $newPatientId]
                        );
                    } else {
                        throw new Exception('El modelo retornó false al crear el paciente');
                    }
                    
                } catch (Exception $e) {
                    Logger::exception($e, 'Error crítico al crear paciente');
                    Response::redirectWithError('patients_create', 'Error al crear el paciente. Por favor, intente nuevamente.');
                }
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. VALIDACIÓN CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Logger::warning('Intento de CSRF en actualización de paciente');
                    Response::redirectWithError('patients', 'Token de seguridad inválido');
                    return;
                }
                
                // 2. VALIDACIÓN DE DATOS
                $validator = new Validator($_POST);
                $validator
                    ->required(['id', 'nombre'], 'El ID y nombre son obligatorios')
                    ->integer('id')
                    ->maxLength('nombre', 100)
                    ->maxLength('apellido_paterno', 100)
                    ->maxLength('apellido_materno', 100);
                
                if (!empty($_POST['telefono'])) {
                    $validator->phone('telefono');
                }
                
                if (!empty($_POST['edad'])) {
                    $validator->integer('edad')->between('edad', 1, 150);
                }
                
                if (!$validator->isValid()) {
                    $errorMessage = implode(', ', $validator->getFirstErrors());
                    Logger::info('Validación fallida al actualizar paciente', [
                        'patient_id' => $_POST['id'] ?? null,
                        'errors' => $validator->getFirstErrors()
                    ]);
                    Response::redirectWithError('patients_edit', $errorMessage, ['id' => $_POST['id'] ?? null]);
                    return;
                }
                
                // 3. PREPARAR DATOS
                $id = (int) $validator->get('id');
                $data = [
                    'nombre' => SecurityHelper::sanitizeString($validator->get('nombre')),
                    'apellido_paterno' => SecurityHelper::sanitizeString($validator->get('apellido_paterno') ?? ''),
                    'apellido_materno' => SecurityHelper::sanitizeString($validator->get('apellido_materno') ?? ''),
                    'domicilio' => SecurityHelper::sanitizeString($validator->get('domicilio') ?? ''),
                    'telefono' => $validator->get('telefono') ? SecurityHelper::sanitizePhone($validator->get('telefono')) : null,
                    'edad' => $validator->get('edad') ?? null,
                    'antecedentes' => SecurityHelper::sanitizeString($validator->get('antecedentes_medicos') ?? '')
                ];
                
                // 4. ACTUALIZAR
                try {
                    if ($patientModel->update($id, $data)) {
                        Logger::userActivity('Paciente actualizado', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $id,
                            'patient_name' => $data['nombre'] . ' ' . $data['apellido_paterno']
                        ]);
                        Response::redirectWithSuccess('patients', 'Paciente actualizado correctamente');
                    } else {
                        throw new Exception('El modelo retornó false');
                    }
                } catch (Exception $e) {
                    Logger::exception($e, 'Error al actualizar paciente');
                    Response::redirectWithError('patients_edit', 'Error al actualizar el paciente', ['id' => $id]);
                }
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. VALIDACIÓN CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Logger::warning('Intento de CSRF en eliminación de paciente');
                    Response::redirectWithError('patients', 'Token de seguridad inválido');
                    return;
                }
                
                // 2. VALIDACIÓN
                $validator = new Validator($_POST);
                $validator->required(['id'])->integer('id');
                
                if (!$validator->isValid()) {
                    Response::redirectWithError('patients', 'ID de paciente inválido');
                    return;
                }
                
                $id = (int) $validator->get('id');
                
                // 3. ELIMINAR
                try {
                    // Obtener nombre antes de eliminar para el log
                    $patient = $patientModel->getById($id);
                    
                    if ($patientModel->delete($id)) {
                        Logger::userActivity('Paciente eliminado', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $id,
                            'patient_name' => $patient ? ($patient['nombre'] . ' ' . $patient['apellido_paterno']) : 'Desconocido'
                        ]);
                        Response::redirectWithSuccess('patients', 'Paciente eliminado correctamente');
                    } else {
                        throw new Exception('El modelo retornó false');
                    }
                } catch (Exception $e) {
                    Logger::exception($e, 'Error al eliminar paciente');
                    Response::redirectWithError('patients', 'Error al eliminar el paciente');
                }
            }
            break;
        
        case 'details':
            $id = $_GET['id'] ?? null;
            if (!$id) { 
                Logger::warning('Intento de acceder a detalles sin ID de paciente');
                Response::redirectWithError('patients', 'ID de paciente no proporcionado');
                return false; 
            }

            try {
                $patient = $patientModel->getById($id);
                if (!$patient) { 
                    Logger::info('Paciente no encontrado', ['patient_id' => $id]);
                    Response::redirectWithError('patients', 'Paciente no encontrado');
                    return false; 
                }

                $resumenConsultas = $consultaModel->getResumenConsultasPorPaciente($id);
                $ventas = $ventaModel->getAllByPaciente($id);

                return [
                    'patient' => $patient,
                    'resumen' => $resumenConsultas,
                    'ventas' => $ventas
                ];
            } catch (Exception $e) {
                Logger::exception($e, 'Error al obtener detalles de paciente');
                Response::redirectWithError('patients', 'Error al cargar los detalles del paciente');
                return false;
            }
            break;
        
        case 'force_create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Response::redirectWithError('patients_create', 'Token de seguridad inválido');
                    return;
                }
                
                // Preparar datos
                $data = [
                    'nombre' => SecurityHelper::sanitizeString($_POST['nombre'] ?? ''),
                    'apellido_paterno' => SecurityHelper::sanitizeString($_POST['apellido_paterno'] ?? ''),
                    'apellido_materno' => SecurityHelper::sanitizeString($_POST['apellido_materno'] ?? ''),
                    'domicilio' => SecurityHelper::sanitizeString($_POST['domicilio'] ?? ''),
                    'telefono' => !empty($_POST['telefono']) ? SecurityHelper::sanitizePhone($_POST['telefono']) : null,
                    'edad' => !empty($_POST['edad']) ? (int) $_POST['edad'] : null,
                    'antecedentes' => SecurityHelper::sanitizeString($_POST['antecedentes_medicos'] ?? '')
                ];

                try {
                    $newPatientId = $patientModel->create($data);
                    
                    unset($_SESSION['new_patient_data']);

                    if ($newPatientId) {
                        Logger::userActivity('Paciente creado (forzado)', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $newPatientId,
                            'patient_name' => $data['nombre'] . ' ' . $data['apellido_paterno']
                        ]);
                        Response::redirectWithSuccess(
                            'patients_details',
                            'Paciente creado exitosamente (se ignoraron duplicados)',
                            ['id' => $newPatientId]
                        );
                    }
                } catch (Exception $e) {
                    Logger::exception($e, 'Error al forzar creación de paciente');
                    Response::redirectWithError('patients_create', 'Error al crear el paciente');
                }
            }
            break;

        case 'force_update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Response::redirectWithError('patients', 'Token de seguridad inválido');
                    return;
                }
                
                $id = (int) ($_POST['id'] ?? 0);
                if (!$id) {
                    Response::redirectWithError('patients', 'ID inválido');
                    return;
                }

                $data = [
                    'nombre' => SecurityHelper::sanitizeString($_POST['nombre'] ?? ''),
                    'apellido_paterno' => SecurityHelper::sanitizeString($_POST['apellido_paterno'] ?? ''),
                    'apellido_materno' => SecurityHelper::sanitizeString($_POST['apellido_materno'] ?? ''),
                    'domicilio' => SecurityHelper::sanitizeString($_POST['domicilio'] ?? ''),
                    'telefono' => !empty($_POST['telefono']) ? SecurityHelper::sanitizePhone($_POST['telefono']) : null,
                    'edad' => !empty($_POST['edad']) ? (int) $_POST['edad'] : null,
                    'antecedentes' => SecurityHelper::sanitizeString($_POST['antecedentes_medicos'] ?? '')
                ];

                try {
                    if ($patientModel->update($id, $data)) {
                        unset($_SESSION['new_patient_data']);
                        
                        Logger::userActivity('Paciente actualizado desde revisión', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $id
                        ]);
                        
                        Response::redirectWithSuccess('patients_details', 'Paciente actualizado', ['id' => $id]);
                    }
                } catch (Exception $e) {
                    Logger::exception($e, 'Error al forzar actualización');
                    Response::redirectWithError('patients_review', 'Error al actualizar el paciente');
                }
            }
            break;

        default:
            // 'list' (default)
            try {
                $searchTerm = $_GET['search'] ?? '';
                return $patientModel->getAll($searchTerm);
            } catch (Exception $e) {
                Logger::exception($e, 'Error al listar pacientes');
                Response::redirectWithError('dashboard', 'Error al cargar la lista de pacientes');
                return [];
            }
    }
}
