<?php
/* ==========================================================================
   Controlador de Autenticación
   ========================================================================== */

// CORRECCIÓN: Usamos __DIR__ para construir una ruta absoluta y confiable
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
$pdo = getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND activo = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        // CORRECCIÓN: Había una errata aquí, es 'nombre_completo'
        $_SESSION['user_fullname'] = $user['nombre_completo']; 
        $_SESSION['user_role'] = $user['rol'];
        
        session_regenerate_id(true);
        session_write_close();
        
        header("Location: /index.php");
        exit();
    } else {
        header("Location: /login.php?error=1");
        exit();
    }
} else {
    header("Location: /login.php");
    exit();
}