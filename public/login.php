<?php
require_once '../config/session.php';

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Optic Suite Galileo</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Estilos específicos para la página de login */
        body { display: flex; justify-content: center; align-items: center; height: 100vh; background-color: var(--bg-secondary); }
        .login-card { background-color: var(--bg-primary); padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .login-card h1 { text-align: center; margin-bottom: 1.5rem; color: var(--text-primary); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; }
        .error-message { color: #e53e3e; background-color: #fed7d7; border: 1px solid #fbb6b6; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; text-align: center; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Optic Suite Galileo</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">Usuario o contraseña incorrectos.</div>
        <?php endif; ?>

        <form action="auth.php" method="POST">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Ingresar</button>
        </form>
    </div>
</body>
</html>