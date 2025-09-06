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
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

// 5. Finalmente, iniciamos la sesión
session_start();