<?php
/* ==========================================================================
   Configuración Centralizada de Sesiones
   ========================================================================== */

// 1. Definimos la ruta a nuestra carpeta de sesiones de forma segura
// __DIR__ nos da la ruta del directorio actual (config), '..' sube un nivel.
$sessionPath = __DIR__ . '/../sessions';

// 2. Verificamos si la carpeta existe y se puede escribir en ella
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true); // Si no existe, la creamos
}

// 3. Asignamos la ruta a PHP
session_save_path($sessionPath);

// 4. Configuramos la sesión para que sea más segura
ini_set('session.cookie_httponly', 1);  // Previene acceso a cookies vía JavaScript (XSS)
ini_set('session.use_only_cookies', 1); // Solo cookies, no URL
ini_set('session.cookie_secure', 0);     // Cambiar a 1 en producción con HTTPS
ini_set('session.cookie_samesite', 'Strict'); // Protección CSRF adicional
ini_set('session.use_strict_mode', 1);  // Rechaza IDs de sesión no inicializados

// Configurar tiempo de vida de la sesión (2 horas)
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);

// 5. Finalmente, iniciamos la sesión
session_start();

// 6. Regenerar ID de sesión periódicamente para prevenir session fixation
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Cada 30 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}