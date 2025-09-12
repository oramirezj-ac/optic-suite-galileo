<?php
require_once '../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

require_once '../config/database.php';

$page = $_GET['page'] ?? 'dashboard';
// CAMBIO: Añadimos la nueva página a la lista de permitidas
$allowedPages = ['dashboard', 'users', 'users_create', 'users_edit', 'users_delete', 'patients', 'patients_create', 'patients_edit', 'patients_details', 'patients_delete']; 

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

if (($page === 'users' || $page === 'users_create' || $page === 'users_edit' || $page === 'users_delete') && $_SESSION['user_role'] !== 'admin') {
    $page = 'dashboard'; 
}

// CAMBIO: Lógica mejorada para encontrar el archivo de la vista
$viewPath = '';
if ($page === 'dashboard') {
    $viewPath = "../app/Views/dashboard.php";
} elseif (strpos($page, 'patients') === 0) { // Nueva lógica para todas las páginas de pacientes
    $viewPath = "../app/Views/patients/" . str_replace('patients_', '', $page) . ".php";
    if ($page === 'patients') $viewPath = "../app/Views/patients/index.php";
} elseif (strpos($page, 'users') === 0) { // Lógica simplificada para todas las páginas de usuarios
    $viewPath = "../app/Views/users/" . str_replace('users_', '', $page) . ".php";
    if ($page === 'users') $viewPath = "../app/Views/users/index.php";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst(str_replace('_', ' ', $page)) ?> - Optic Suite Galileo</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/components/custom.css">
</head>
<body>
    
    <div class="dashboard-container">
        <?php require_once '../app/Views/layout/sidebar.php'; ?>
        <?php require_once '../app/Views/layout/header.php'; ?>
        <main class="main-content">
            <?php
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