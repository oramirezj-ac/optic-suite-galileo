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
            $tab = $_GET['tab'] ?? 'recent'; 
            $ventas = [];

            // --- LÓGICA DE AUDITORÍA (NUEVO) ---
            if ($tab === 'audit') {
                $folioStart = $_GET['folio_start'] ?? '';
                $folioEnd = $_GET['folio_end'] ?? '';
                $auditResults = []; // Aquí guardaremos: OK, Faltantes y Duplicados
                
                // Solo procesamos si el usuario ya puso un rango
                if (!empty($folioStart) && !empty($folioEnd)) {
                    $start = (int)$folioStart;
                    $end = (int)$folioEnd;

                    // 1. Obtenemos lo que SÍ existe en la BD
                    $rawSales = $ventaModel->getByFolioRange($start, $end);

                    // 2. Indexamos para búsqueda rápida [Folio => [Venta1, Venta2...]]
                    $salesMap = [];
                    foreach ($rawSales as $row) {
                        $num = (int)$row['numero_nota'];
                        $salesMap[$num][] = $row;
                    }

                    // 3. COMPARACIÓN: Iteramos el rango IDEAL paso a paso
                    for ($i = $start; $i <= $end; $i++) {
                        if (isset($salesMap[$i])) {
                            // SI EXISTE EN BD
                            $rows = $salesMap[$i];
                            $isDuplicate = count($rows) > 1;

                            foreach ($rows as $row) {
                                $auditResults[] = [
                                    'folio' => $i,
                                    'status' => $isDuplicate ? 'duplicate' : 'ok', // Marcamos si es duplicado
                                    'data' => $row
                                ];
                            }
                        } else {
                            // NO EXISTE (HUECO)
                            $auditResults[] = [
                                'folio' => $i,
                                'status' => 'missing',
                                'data' => null
                            ];
                        }
                    }
                }
                
                // Pasamos los resultados procesados a la vista
                return [
                    'ventas' => [], // Vacío para no confundir la tabla normal
                    'auditResults' => $auditResults, // <--- Nueva variable para la vista
                    'activeTab' => 'audit',
                    'folioStart' => $folioStart,
                    'folioEnd' => $folioEnd
                ];
            }
            // --- FIN LÓGICA DE AUDITORÍA ---

            elseif ($tab === 'search') {
                $term = $_GET['q'] ?? '';
                if (!empty($term)) {
                    $ventas = $ventaModel->searchByTerm($term);
                }
            } 
            elseif ($tab === 'dates') {
                $start = $_GET['date_start'] ?? '';
                $end = $_GET['date_end'] ?? '';
                if (!empty($start) && !empty($end)) {
                    $ventas = $ventaModel->searchByDateRange($start, $end);
                }
            } 
            elseif ($tab === 'all') {
                $ventas = $ventaModel->getAll();
            }
            else {
                $ventas = $ventaModel->getAllWithPatient(); 
            }
            
            return [
                'ventas' => $ventas,
                'activeTab' => $tab
            ];
            break;

        /* ------------------------------------------------------
           CASE: CREATE (Mostrar el formulario de nueva venta)
           ------------------------------------------------------ */
        case 'create':
            $paciente = $patientModel->getById($patientId);
            return [ 'paciente' => $paciente ];
            break;

        /* ------------------------------------------------------
           CASE: STORE (Guardar la venta y el anticipo)
           ------------------------------------------------------ */
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // --- 1. Lógica Financiera (Estado Pagado/Pendiente) ---
                $costoTotal = $_POST['costo_total'] ?? 0.00;
                $anticipo = $_POST['monto_anticipo'] ?? 0.00;

                if ($anticipo >= ($costoTotal - 0.01)) {
                    $estadoInicial = 'pagado';
                } else {
                    $estadoInicial = 'pendiente';
                }

                // --- 2. Lógica de Fecha (Agregamos hora fija 11:00) ---
                $fechaVentaDB = ($_POST['fecha_venta'] ?? date('Y-m-d')) . ' 11:00:00';

                // --- 3. Lógica de Duplicados (La regla de la 'D') ---
                $numeroNota = $_POST['numero_nota'];
                $sufijo = $_POST['numero_nota_sufijo'] ?? null;

                // Verificamos si la nota ya existe
                if ($ventaModel->existsNumeroNota($numeroNota)) {
                    // Si existe y no escribiste un sufijo manual, ponemos 'D'
                    if (empty($sufijo)) {
                        $sufijo = 'D'; 
                    }
                }

                // --- 4. Preparamos el Array final para el Modelo ---
                $dataVenta = [
                    'id_paciente' => $patientId,
                    'numero_nota' => $numeroNota,
                    'numero_nota_sufijo' => $sufijo, // <-- Aquí va la 'D' si aplica
                    'vendedor_armazon' => !empty($_POST['vendedor_armazon']) ? $_POST['vendedor_armazon'] : null, // <-- CAPTURA CORRECTA
                    'fecha_venta' => $fechaVentaDB,  
                    'costo_total' => $costoTotal,
                    'estado_pago' => $estadoInicial,
                    'observaciones' => $_POST['observaciones'] ?? null
                ];

                try {
                    $pdo->beginTransaction();

                    // Guardamos la Venta
                    $newVentaId = $ventaModel->create($dataVenta);

                    if (!$newVentaId) {
                        throw new Exception("No se pudo crear la venta.");
                    }

                    // Guardamos el Anticipo (si existe)
                    if ($anticipo > 0) {
                        // Usamos la misma fecha de la venta para el anticipo
                        $fechaAnticipo = ($_POST['fecha_anticipo'] ?? $_POST['fecha_venta']) . ' 11:00:00';
                        
                        $dataAbono = [
                            'id_venta' => $newVentaId,
                            'monto' => $anticipo,
                            'metodo_pago' => $_POST['metodo_pago'] ?? 'Efectivo', // <-- CORREGIDO: metodo_pago coincide con el name del select en create.php
                            'fecha' => $fechaAnticipo
                        ];
                        $abonoModel->create($dataAbono);
                    }

                    $pdo->commit();

                    // Éxito: Redirigimos al Hub de Venta
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

            $paciente = $patientModel->getById($patientId);
            $venta = $ventaModel->getById($ventaId);
            $abonos = $abonoModel->getByVentaId($ventaId);
            $totalPagado = $abonoModel->getTotalPagado($ventaId);

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

                if (!$ventaId) {
                    header('Location: /index.php?page=patients&error=missing_ids');
                    exit();
                }

                // Preparamos datos
                $data = [
                    'numero_nota' => $_POST['numero_nota'],
                    'vendedor_armazon' => !empty($_POST['vendedor_armazon']) ? $_POST['vendedor_armazon'] : null, // <-- CAPTURA CORRECTA
                    'fecha_venta' => $_POST['fecha_venta'],
                    'costo_total' => $_POST['costo_total'],
                    'observaciones' => $_POST['observaciones']
                ];

                if ($ventaModel->update($ventaId, $data)) {
                    header('Location: /index.php?page=ventas_details&id=' . $ventaId . '&patient_id=' . $patientId . '&success=sale_updated');
                } else {
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
                    header('Location: /index.php?page=patients_details&id=' . $patientId . '&tab=ventas&success=sale_deleted');
                } else {
                    header('Location: /index.php?page=ventas_delete&id=' . $ventaId . '&patient_id=' . $patientId . '&error=delete_failed');
                }
                exit();
            }
            break;
    }
}