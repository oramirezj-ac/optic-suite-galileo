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

                $data = [
                    'nombre' => $nombre,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'edad' => !empty($_POST['edad']) ? $_POST['edad'] : null,
                    'antecedentes' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
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
                
                $data = [
                    'nombre' => $nombre,
                    'apellido_paterno' => !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null,
                    'apellido_materno' => !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null,
                    'domicilio' => !empty($_POST['domicilio']) ? $_POST['domicilio'] : null,
                    'telefono' => !empty($_POST['telefono']) ? $_POST['telefono'] : null,
                    'edad' => !empty($_POST['edad']) ? $_POST['edad'] : null,
                    'antecedentes' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
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
                    'edad' => !empty($_POST['edad']) ? $_POST['edad'] : null,
                    'antecedentes' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
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
                    'antecedentes' => !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : ''
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
            // 'list' (default)
            $searchTerm = $_GET['search'] ?? '';
            return $patientModel->getAll($searchTerm);
    }
}