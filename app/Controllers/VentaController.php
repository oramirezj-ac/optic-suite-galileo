<?php
/* ==========================================================================
   Controlador para la Gestión de Ventas
   ========================================================================== */

// 1. Cargamos las dependencias
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Models/VentaModel.php';
require_once __DIR__ . '/../Models/AbonoModel.php';
require_once __DIR__ . '/../Models/PatientModel.php';

/**
 * Función principal para manejar las acciones del CRUD de Ventas.
 */
function handleVentaAction()
{
    $pdo = getConnection();
    
    // Determinamos la acción
    $action = $_GET['action'] ?? 'index'; 

    // Lógica de ID Inteligente para el Paciente
    // Agregamos 'update' a la lista de acciones que vienen por POST
    if ($action === 'store' || $action === 'delete' || $action === 'update') {
        $patientId = $_POST['patient_id'] ?? null;
    } else {
        // Para ver detalles, crear o editar (el formulario), viene por GET
        $patientId = $_GET['patient_id'] ?? null;
    }

    // Validamos que tengamos un paciente (excepto para el índice general de ventas)
    if (!$patientId && $action !== 'index') {
        header('Location: /index.php?page=patients&error=missing_patient_id_sale');
        exit();
    }

    // Instanciamos los modelos
    $ventaModel = new VentaModel($pdo);
    $abonoModel = new AbonoModel($pdo);
    $patientModel = new PatientModel($pdo);

    switch ($action) {
        
        /* ------------------------------------------------------
           CASE: INDEX (Listado General con Filtros)
           ------------------------------------------------------ */
        case 'index':
            // Detectamos qué pestaña está activa para saber qué buscar
            $tab = $_GET['tab'] ?? 'recent'; // 'recent' es el default
            $ventas = [];

            // Lógica según la pestaña
            if ($tab === 'search') {
                // Pestaña Búsqueda
                $term = $_GET['q'] ?? '';
                if (!empty($term)) {
                    $ventas = $ventaModel->searchByTerm($term);
                }
            } 
            elseif ($tab === 'dates') {
                // Pestaña Fechas
                $start = $_GET['date_start'] ?? '';
                $end = $_GET['date_end'] ?? '';
                if (!empty($start) && !empty($end)) {
                    $ventas = $ventaModel->searchByDateRange($start, $end);
                }
            } 
            elseif ($tab === 'all') {
                // Pestaña Histórico Completo
                $ventas = $ventaModel->getAll();
            }
            else {
                // Pestaña Recientes (Default)
                $ventas = $ventaModel->getAllWithPatient(); // La función de límite 50 que ya tenías
            }
            
            return [
                'ventas' => $ventas,
                'activeTab' => $tab // Pasamos la pestaña activa a la vista
            ];
            break;

        /* ------------------------------------------------------
           CASE: CREATE (Mostrar el formulario de nueva venta)
           ------------------------------------------------------ */
        case 'create':
            // Buscamos los datos del paciente para el título
            $paciente = $patientModel->getById($patientId);
            
            return [
                'paciente' => $paciente
            ];
            break;

        /* ------------------------------------------------------
           CASE: STORE (Guardar la venta y el anticipo)
           ------------------------------------------------------ */
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Preparamos los montos para decidir el estado
                $costoTotal = $_POST['costo_total'] ?? 0.00;
                $anticipo = $_POST['monto_anticipo'] ?? 0.00;

                // Lógica: Si el anticipo cubre el total, nace como 'pagado'
                // (Usamos una pequeña tolerancia de 0.01 para errores de decimales)
                if ($anticipo >= ($costoTotal - 0.01)) {
                    $estadoInicial = 'pagado';
                } else {
                    $estadoInicial = 'pendiente';
                }

                // 2. Recopilamos Datos de la Venta
                $dataVenta = [
                    'id_paciente' => $patientId,
                    'numero_nota' => $_POST['numero_nota'],
                    'numero_nota_sufijo' => $_POST['numero_nota_sufijo'] ?? null,
                    'fecha_venta' => $_POST['fecha_venta'] ?? date('Y-m-d'),
                    'costo_total' => $costoTotal,
                    'estado_pago' => $estadoInicial, // <-- USAMOS EL ESTADO CALCULADO
                    'observaciones' => $_POST['observaciones'] ?? null
                ];

                try {
                    $pdo->beginTransaction();

                    // 3. Guardamos la Venta
                    $newVentaId = $ventaModel->create($dataVenta);

                    if (!$newVentaId) {
                        throw new Exception("No se pudo crear la venta.");
                    }

                    // 4. Guardamos el Anticipo (si existe y es mayor a 0)
                    if ($anticipo > 0) {
                        $dataAbono = [
                            'id_venta' => $newVentaId,
                            'monto' => $anticipo,
                            'fecha' => $_POST['fecha_anticipo'] ?? $dataVenta['fecha_venta']
                        ];
                        $abonoModel->create($dataAbono);
                    }

                    $pdo->commit();

                    // 5. Éxito
                    header('Location: /index.php?page=ventas_details&id=' . $newVentaId . '&patient_id=' . $patientId . '&success=sale_created');
                    exit();

                } catch (Exception $e) {
                    $pdo->rollBack();
                    header('Location: /index.php?page=ventas_create&patient_id=' . $patientId . '&error=' . urlencode($e->getMessage()));
                    exit();
                }
            }
            break;

            /* ------------------------------------------------------
           CASE: DETAILS (El Hub de la Venta)
           ------------------------------------------------------ */
        case 'details':
            $ventaId = $_GET['id'] ?? null;
            
            if (!$ventaId || !$patientId) {
                header('Location: /index.php?page=patients&error=missing_ids');
                exit();
            }

            // 1. Buscamos datos principales
            $paciente = $patientModel->getById($patientId);
            $venta = $ventaModel->getById($ventaId);
            
            // 2. Buscamos historial financiero
            $abonos = $abonoModel->getByVentaId($ventaId);
            $totalPagado = $abonoModel->getTotalPagado($ventaId);

            // 3. Devolvemos todo el paquete
            return [
                'paciente' => $paciente,
                'venta' => $venta,
                'abonos' => $abonos,
                'totalPagado' => $totalPagado
            ];
            break;

        /* ------------------------------------------------------
           CASE: EDIT (Mostrar formulario de edición)
           ------------------------------------------------------ */
        case 'edit':
            $ventaId = $_GET['id'] ?? null;
            
            if (!$ventaId || !$patientId) {
                header('Location: /index.php?page=patients&error=missing_ids');
                exit();
            }

            // Buscamos los datos actuales para rellenar el formulario
            $venta = $ventaModel->getById($ventaId);
            $paciente = $patientModel->getById($patientId);

            return [
                'venta' => $venta,
                'paciente' => $paciente
            ];
            break;

        /* ------------------------------------------------------
           CASE: UPDATE (Procesar cambios)
           ------------------------------------------------------ */
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ventaId = $_POST['id_venta'] ?? null;
                // (Nota: $patientId ya se obtuvo automáticamente al inicio del controlador)

                if (!$ventaId) {
                    header('Location: /index.php?page=patients&error=missing_ids');
                    exit();
                }

                // 1. Preparamos los datos a actualizar
                $data = [
                    'numero_nota' => $_POST['numero_nota'],
                    'fecha_venta' => $_POST['fecha_venta'],
                    'costo_total' => $_POST['costo_total'],
                    'observaciones' => $_POST['observaciones']
                ];

                // 2. Llamamos al modelo
                if ($ventaModel->update($ventaId, $data)) {
                    // ÉXITO: Regresamos al Hub de la Venta
                    header('Location: /index.php?page=ventas_details&id=' . $ventaId . '&patient_id=' . $patientId . '&success=sale_updated');
                } else {
                    // ERROR: Regresamos al formulario de edición
                    header('Location: /index.php?page=ventas_edit&id=' . $ventaId . '&patient_id=' . $patientId . '&error=update_failed');
                }
                exit();
            }
            break;

        /* ------------------------------------------------------
           CASE: DELETE (Procesar el borrado)
           ------------------------------------------------------ */
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ventaId = $_POST['id_venta'] ?? null;
                $patientId = $_POST['patient_id'] ?? null;

                if (!$ventaId || !$patientId) {
                    header('Location: /index.php?page=patients&error=missing_ids');
                    exit();
                }

                if ($ventaModel->delete($ventaId)) {
                    // ÉXITO: Regresamos al expediente del paciente (Pestaña Ventas)
                    header('Location: /index.php?page=patients_details&id=' . $patientId . '&tab=ventas&success=sale_deleted');
                } else {
                    // ERROR: Regresamos a la confirmación
                    header('Location: /index.php?page=ventas_delete&id=' . $ventaId . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
        
    }
}