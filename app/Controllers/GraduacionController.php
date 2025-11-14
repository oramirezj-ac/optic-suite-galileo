<?php
/* ==========================================================================
   Controlador para la Gestión de Graduaciones
   ========================================================================== */

// 1. Cargamos las dependencias
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/GraduacionModel.php';

/**
 * Función principal para manejar las acciones del CRUD de Graduaciones.
 */
function handleGraduacionAction()
{
    $action = $_GET['action'] ?? 'store'; // Por ahora, solo maneja 'store'
    $pdo = getConnection();
    $graduacionModel = new GraduacionModel($pdo);

    switch ($action) {

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
                    // Redirigimos de vuelta a la página de detalles de la consulta
                    header('Location: /index.php?page=consultas_details&id=' . $consultaId . '&patient_id=' . $patientId . '&success=grad_created');

                } catch (Exception $e) {
                    // --- ERROR ---
                    header('Location: /index.php?page=consultas_details&id=' . $consultaId . '&patient_id=' . $patientId . '&error=' . urlencode($e->getMessage()));
                }
                exit();
            }
            break;

        // --- (Aquí irán 'delete', 'update'...) ---
    }
}