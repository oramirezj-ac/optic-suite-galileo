<?php
/* ==========================================================================
   Controlador para la Gestión de Abonos (Pagos)
   ========================================================================== */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/VentaModel.php';
require_once __DIR__ . '/../Models/AbonoModel.php';
require_once __DIR__ . '/../Models/PatientModel.php';

/**
 * Función principal del Controlador
 */
function handleAbonoAction()
{
    $pdo = getConnection();
    $action = $_GET['action'] ?? 'create';

    // --- 1. Lógica Inteligente para IDs ---
    if ($action === 'store' || $action === 'update' || $action === 'delete') {
        $ventaId = $_POST['venta_id'] ?? null;
        $patientId = $_POST['patient_id'] ?? null;
    } else {
        $ventaId = $_GET['venta_id'] ?? null;
        $patientId = $_GET['patient_id'] ?? null;
    }

    // Validación de Seguridad
    if (!$patientId) {
        header('Location: /index.php?page=patients&error=missing_ids_abono');
        exit();
    }

    // Instancias
    $ventaModel = new VentaModel($pdo);
    $abonoModel = new AbonoModel($pdo);
    $patientModel = new PatientModel($pdo);

    switch ($action) {
        
        /* ------------------------------------------------------
           CASE: CREATE
           ------------------------------------------------------ */
        case 'create':
            $venta = $ventaModel->getById($ventaId);
            $paciente = $patientModel->getById($patientId);
            $pagado = $abonoModel->getTotalPagado($ventaId);
            $saldoPendiente = $venta['costo_total'] - $pagado;

            return [
                'venta' => $venta,
                'paciente' => $paciente,
                'saldoPendiente' => $saldoPendiente
            ];
            break;

        /* ------------------------------------------------------
           CASE: STORE
           ------------------------------------------------------ */
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $monto = $_POST['monto'];
                $fecha = $_POST['fecha'];

                $data = [
                    'id_venta' => $ventaId,
                    'monto' => $monto,
                    'fecha' => $fecha
                ];

                if ($abonoModel->create($data)) {
                    // ACTUALIZAR ESTADO AUTOMÁTICAMENTE
                    syncSaleStatus($ventaId, $ventaModel, $abonoModel);
                    
                    header('Location: /index.php?page=ventas_details&id=' . $ventaId . '&patient_id=' . $patientId . '&success=payment_registered');
                } else {
                    header('Location: /index.php?page=abonos_create&venta_id=' . $ventaId . '&patient_id=' . $patientId . '&error=payment_failed');
                }
                exit();
            }
            break;

        /* ------------------------------------------------------
           CASE: EDIT
           ------------------------------------------------------ */
        case 'edit':
            $abonoId = $_GET['id'] ?? null;
            if (!$abonoId) { header('Location: /index.php?page=patients'); exit(); }

            $abono = $abonoModel->getById($abonoId);
            $venta = $ventaModel->getById($ventaId);
            $paciente = $patientModel->getById($patientId);

            return [
                'abono' => $abono,
                'venta' => $venta,
                'paciente' => $paciente
            ];
            break;

        /* ------------------------------------------------------
           CASE: UPDATE
           ------------------------------------------------------ */
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $abonoId = $_POST['id_abono'];
                $monto = $_POST['monto'];
                $fecha = $_POST['fecha'];

                $data = ['monto' => $monto, 'fecha' => $fecha];

                if ($abonoModel->update($abonoId, $data)) {
                    // ACTUALIZAR ESTADO AUTOMÁTICAMENTE
                    syncSaleStatus($ventaId, $ventaModel, $abonoModel);

                    header('Location: /index.php?page=ventas_details&id=' . $ventaId . '&patient_id=' . $patientId . '&success=payment_updated');
                } else {
                    header('Location: /index.php?page=abonos_edit&id=' . $abonoId . '&venta_id=' . $ventaId . '&patient_id=' . $patientId . '&error=update_failed');
                }
                exit();
            }
            break;

        /* ------------------------------------------------------
           CASE: DELETE
           ------------------------------------------------------ */
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $abonoId = $_POST['id_abono'];

                if ($abonoModel->delete($abonoId)) {
                    // ACTUALIZAR ESTADO AUTOMÁTICAMENTE
                    syncSaleStatus($ventaId, $ventaModel, $abonoModel);

                    header('Location: /index.php?page=ventas_details&id=' . $ventaId . '&patient_id=' . $patientId . '&success=payment_deleted');
                } else {
                    header('Location: /index.php?page=abonos_delete&id=' . $abonoId . '&venta_id=' . $ventaId . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
    }
}

/* ==========================================================================
   FUNCIÓN AUXILIAR (HELPER)
   Esta función está FUERA de la función principal para ser visible globalmente
   dentro de este archivo.
   ========================================================================== */
function syncSaleStatus($ventaId, $ventaModel, $abonoModel) {
    // 1. Obtener total de la venta
    $venta = $ventaModel->getById($ventaId);
    $totalVenta = $venta['costo_total'];

    // 2. Obtener total pagado (suma de abonos)
    $totalPagado = $abonoModel->getTotalPagado($ventaId);

    // 3. Decidir el estado
    // (Usamos una pequeña tolerancia de 0.01 para errores de decimales)
    if ($totalPagado >= ($totalVenta - 0.01)) {
        $nuevoEstado = 'pagado';
    } else {
        $nuevoEstado = 'pendiente';
    }

    // 4. Actualizar en la BD
    $ventaModel->updateStatus($ventaId, $nuevoEstado);
}