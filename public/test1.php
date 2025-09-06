<?php
require_once '../config/session.php'; // Usamos nuestro manejador de sesión

// Creamos una variable de sesión simple
$_SESSION['test_variable'] = '¡La sesión funciona!';

echo '<h1>Página de Prueba 1</h1>';
echo '<p>Se ha establecido una variable en la sesión.</p>';
echo '<p><strong>Valor:</strong> ' . $_SESSION['test_variable'] . '</p>';
echo '<p><a href="test2.php" style="font-size: 20px;">Haz clic aquí para ir a la Página 2 y verificar</a></p>';

// Muestra la ruta donde se deberían guardar las sesiones
echo '<hr>';
echo '<p>PHP intentará guardar la sesión en esta ruta: <strong>' . session_save_path() . '</strong></p>';
echo '<p>Verifica manualmente que esta carpeta exista y que el servidor Apache/PHP pueda escribir en ella.</p>';