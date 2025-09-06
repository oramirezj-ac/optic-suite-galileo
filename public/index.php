<?php
require_once '../config/session.php';

// 1. Guardia de seguridad: si no está logueado, lo mandamos al login.
if (!isset($_SESSION['user_id'])) {
    // CORRECCIÓN: Añadimos la barra inicial para que sea una ruta absoluta
    header('Location: /login.php');
    exit();
}

// 2. Incluimos la función de conexión a la BD para que esté disponible en las vistas
require_once '../config/database.php';

// 3. Lógica del Router Simple
$page = $_GET['page'] ?? 'dashboard'; // Por defecto, cargamos el dashboard
$allowedPages = ['dashboard', 'users']; // Lista de páginas permitidas

// Verificamos si la página solicitada es válida
if (!in_array($page, $allowedPages)) {
    $page = 'dashboard'; // Si no, cargamos el dashboard por seguridad
}

// Guardia de rol para la página de usuarios
if ($page === 'users' && $_SESSION['user_role'] !== 'admin') {
    // Si un no-admin intenta acceder a la página de usuarios, lo mandamos al dashboard
    $page = 'dashboard'; 
}

// Construimos la ruta al archivo de la vista
$viewPath = "../app/Views/{$page}/index.php";

// Si la página es 'dashboard', creamos una vista simple para ella
if ($page === 'dashboard') {
    $viewPath = "../app/Views/dashboard.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($page) ?> - Optic Suite Galileo</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/components/custom.css"> </head>
<body>
    
    <div class="dashboard-container">
        <?php require_once '../app/Views/layout/sidebar.php'; ?>
        
        <?php require_once '../app/Views/layout/header.php'; ?>

        <main class="main-content">
            <?php
            // Incluimos la vista correspondiente
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