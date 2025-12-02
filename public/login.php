<?php
/* ==========================================================================
   Punto de Entrada: Login
   ========================================================================== */

// 1. Cargamos el entorno (Igual que en index.php)
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// 2. Iniciamos sesión y conexión
require_once '../config/session.php';
require_once '../config/database.php';

// 3. Si ya está logueado, lo mandamos adentro
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

// 4. Procesar el Formulario (Lógica que faltaba)
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $pdo = getConnection();
        // Buscamos al usuario activo
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND activo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verificamos contraseña
        if ($user && password_verify($password, $user['password'])) {
            // --- LOGIN EXITOSO ---
            // Guardamos datos en sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_fullname'] = $user['nombre_completo'];
            $_SESSION['user_role'] = $user['rol'];
            
            // Redirigimos al Dashboard
            header('Location: /index.php');
            exit();
        } else {
            // --- ERROR DE LOGIN ---
            $error = "Usuario o contraseña incorrectos.";
        }

    } catch (Exception $e) {
        $error = "Error de sistema: No se pudo conectar a la base de datos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Optic Suite</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-layout">
    
    <div class="login-card">
        <h1>Iniciar Sesión</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>

</body>
</html>