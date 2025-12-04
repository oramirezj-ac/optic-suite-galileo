<?php
require_once __DIR__ . '/../../config/database.php';

function handleDashboardAction() {
    $pdo = getConnection();
    
    // --- 1. KPIs GLOBALES (Total Pacientes y Notas) ---
    // Estos cuentan todo el histórico, tengan o no pagos.
    $stmt = $pdo->query("SELECT COUNT(*) FROM pacientes");
    $totalPacientes = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM ventas");
    $totalVentas = $stmt->fetchColumn();


    // --- 2. INGRESOS REALES POR AÑO Y MES (Dinero en caja) ---
    // Solo suma lo que realmente entró a la tabla 'abonos'.
    
    $sql = "SELECT 
                YEAR(fecha) as anio, 
                MONTH(fecha) as mes, 
                SUM(monto) as total 
            FROM abonos 
            GROUP BY anio, mes 
            ORDER BY anio DESC, mes DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll();

    // --- 3. ESTRUCTURAR DATOS PARA LA VISTA ---
    // Queremos un formato: $ingresos['2024']['totales'] y $ingresos['2024']['meses'][1]...
    
    $ingresosPorAno = [];

    foreach ($resultados as $fila) {
        $anio = $fila['anio'];
        $mes = $fila['mes'];
        $total = $fila['total'];

        // Si el año no existe en nuestro array, lo inicializamos
        if (!isset($ingresosPorAno[$anio])) {
            $ingresosPorAno[$anio] = [
                'total_anual' => 0,
                'meses' => []
            ];
        }

        // Sumamos al total anual
        $ingresosPorAno[$anio]['total_anual'] += $total;

        // Guardamos el desglose mensual
        // (Guardamos el nombre del mes en español para facilitar la vista)
        $nombreMes = getNombreMes($mes);
        $ingresosPorAno[$anio]['meses'][$mes] = [
            'nombre' => $nombreMes,
            'total' => $total
        ];
    }

    return [
        'totalPacientes' => $totalPacientes,
        'totalVentas' => $totalVentas,
        'ingresosPorAno' => $ingresosPorAno
    ];
}

/**
 * Helper interno para nombres de meses
 */
function getNombreMes($numeroMes) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $meses[$numeroMes] ?? 'Mes ' . $numeroMes;
}