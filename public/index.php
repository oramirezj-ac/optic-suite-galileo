<?php
// --- INICIO DE CARGA DE ENTORNO ---
// 1. Cargamos el Autoloader de Composer (necesario para leer .env)
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Cargamos las variables del archivo .env de forma segura
// (Usamos createImmutable para que busque en la carpeta raíz del proyecto)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // safeLoad evita error fatal si el archivo no existe
// --- FIN DE CARGA DE ENTORNO ---

require_once '../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

require_once '../config/database.php';

$page = $_GET['page'] ?? 'dashboard';
// Lista de páginas permitidas en la navegación
$allowedPages = [
                    'dashboard', 
                    'users', 
                    'users_create', 
                    'users_edit', 
                    'users_delete', 
                    'patients', 
                    'patients_create', 
                    'patients_edit', 
                    'patients_details', 
                    'patients_delete',
                    'patients_review',
                    'consultas_index',
                    'consultas_create',
                    'consultas_edit',
                    'consultas_delete',
                    'clinica_index',          // Hub de clínica
                    'clinica_review',         // Revisión de duplicados
                    'clinica_delete',         // Confirmar borrado paciente
                    // Consultas Médicas
                    'consultas_medicas_index',
                    'consultas_medicas_create',
                    'consultas_medicas_edit',
                    'consultas_medicas_delete',
                    'consultas_medicas_details',
                    // Consultas de Lentes
                    'consultas_lentes_index',
                    'consultas_lentes_create',
                    'consultas_lentes_edit',
                    'consultas_lentes_delete',
                    'consultas_lentes_details',
                    // AV Live
                    'av_live_index',
                    'av_live_create',
                    'av_live_edit',
                    'av_live_delete',
                    // Graduaciones
                    'graduaciones_index',
                    'graduaciones_create',
                    'graduaciones_edit',
                    'graduaciones_delete',
                    'graduaciones_live_index',
                    'graduaciones_live_create',
                    'ventas_index',
                    'ventas_create',
                    'ventas_details',
                    'ventas_edit',
                    'ventas_delete',
                    'abonos_create',
                    'abonos_edit',
                    'abonos_delete',
                ]; 

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}
    if (($page === 'users' || $page === 'users_create' || $page === 'users_edit' || $page === 'users_delete') && $_SESSION['user_role'] !== 'admin') {
        $page = 'dashboard'; 
    }

// Lógica mejorada para encontrar el archivo de la vista
$viewPath = '';
if ($page === 'dashboard') {
    $viewPath = "../app/Views/dashboard.php";
} elseif (strpos($page, 'patients') === 0) { // Nueva lógica para todas las páginas de pacientes
    $viewPath = "../app/Views/patients/" . str_replace('patients_', '', $page) . ".php";
    if ($page === 'patients') $viewPath = "../app/Views/patients/index.php";
} elseif (strpos($page, 'users') === 0) { // Lógica simplificada para todas las páginas de usuarios
    $viewPath = "../app/Views/users/" . str_replace('users_', '', $page) . ".php";
    if ($page === 'users') $viewPath = "../app/Views/users/index.php";
} elseif (strpos($page, 'consultas_medicas') === 0) {
    // Enrutamiento para consultas médicas (DEBE IR ANTES de 'consultas')
    $viewPath = "../app/Views/consultas_medicas/" . str_replace('consultas_medicas_', '', $page) . ".php";
    if ($page === 'consultas_medicas' || $page === 'consultas_medicas_index') {
        $viewPath = "../app/Views/consultas_medicas/index.php";
    }
} elseif (strpos($page, 'consultas_lentes') === 0) {
    // Enrutamiento para consultas de lentes (DEBE IR ANTES de 'consultas')
    $viewPath = "../app/Views/consultas_lentes/" . str_replace('consultas_lentes_', '', $page) . ".php";
    if ($page === 'consultas_lentes' || $page === 'consultas_lentes_index') {
        $viewPath = "../app/Views/consultas_lentes/index.php";
    }
} elseif (strpos($page, 'consultas') === 0) {
    // Si la página empieza con 'consultas_', busca en la carpeta /app/Views/consultas/
    $viewPath = "../app/Views/consultas/" . str_replace('consultas_', '', $page) . ".php";
    
    // Si la página es solo 'consultas' (o 'consultas_index'), usa index.php
    if ($page === 'consultas' || $page === 'consultas_index') {
        $viewPath = "../app/Views/consultas/index.php";
    }
} elseif (strpos($page, 'clinica') === 0) {
    // Si la página empieza con 'clinica_', busca en la carpeta /app/Views/clinica/
    $viewPath = "../app/Views/clinica/" . str_replace('clinica_', '', $page) . ".php";
    
    // Si la página es solo 'clinica' (o 'clinica_index'), usa index.php
    if ($page === 'clinica' || $page === 'clinica_index') {
        $viewPath = "../app/Views/clinica/index.php";
    }
} elseif (strpos($page, 'av_live') === 0) {
    // IMPORTANTE: Esto debe ir ANTES de otros módulos
    $viewFile = str_replace('av_live_', '', $page);
    $viewPath = "../app/Views/av_live/" . $viewFile . ".php";
    if ($page === 'av_live' || $page === 'av_live_index') {
        $viewPath = "../app/Views/av_live/index.php";
    }
} elseif (strpos($page, 'graduaciones_live') === 0) {
    // IMPORTANTE: Esto debe ir ANTES de 'graduaciones' para evitar conflictos
    $viewFile = str_replace('graduaciones_live_', '', $page);
    $viewPath = "../app/Views/graduaciones_live/" . $viewFile . ".php";
}
elseif (strpos($page, 'graduaciones') === 0) {
    // Si la página empieza con 'graduaciones_', busca en la carpeta /app/Views/graduaciones/
    $viewFile = str_replace('graduaciones_', '', $page);
    
    // Carga el archivo correspondiente (index.php, create.php, etc.)
    $viewPath = "../app/Views/graduaciones/" . $viewFile . ".php";
    
    // Caso especial para la página principal
    if ($page === 'graduaciones' || $page === 'graduaciones_index') {
        $viewPath = "../app/Views/graduaciones/index.php";
    }
}
elseif (strpos($page, 'ventas') === 0) {
    // Reemplaza 'ventas_' por nada para obtener el nombre del archivo
    $viewFile = str_replace('ventas_', '', $page);
    
    // Busca en la carpeta /app/Views/ventas/
    $viewPath = "../app/Views/ventas/" . $viewFile . ".php";
    
    // Caso especial para el índice
    if ($page === 'ventas' || $page === 'ventas_index') {
        $viewPath = "../app/Views/ventas/index.php";
    }
}
elseif (strpos($page, 'abonos') === 0) {
    // Reemplaza 'abonos_' por nada para obtener el nombre del archivo
    $viewFile = str_replace('abonos_', '', $page);
    
    // Busca en la carpeta /app/Views/abonos/
    $viewPath = "../app/Views/abonos/" . $viewFile . ".php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst(str_replace('_', ' ', $page)) ?> - Optic Suite Galileo</title>
    <link rel="stylesheet" href="assets/css/styles.css?v=<?= time() ?>">
</head>
<body>
    
    <div class="dashboard-container">
        <?php require_once '../app/Views/layout/sidebar.php'; ?>
        <?php require_once '../app/Views/layout/header.php'; ?>
        <main class="main-content">
            <?php
            // Si la página es de abonos, muéstrame qué estás buscando
            /*DEBUGG--------
            if (strpos($page, 'abonos') === 0) {
                echo "<div style='background:yellow; padding:10px; border:1px solid red;'>";
                echo "<strong>DEBUG INFO:</strong><br>";
                echo "Página solicitada: " . htmlspecialchars($page) . "<br>";
                echo "Ruta generada (\$viewPath): " . htmlspecialchars($viewPath) . "<br>";
                echo "¿Existe el archivo?: " . (file_exists($viewPath) ? 'SÍ' : 'NO') . "<br>";
                echo "Ruta absoluta intentada: " . realpath($viewPath); // Esto ayuda mucho
                echo "</div>";
            }
            DEBUGG--------*/
            if (file_exists($viewPath)) {
                require_once $viewPath;
            } else {
                echo "<h1>Error 404: Página no encontrada.</h1>";
            }
            ?>
        </main>
        <?php require_once '../app/Views/layout/footer.php'; ?>
    </div>
</body>
</html>