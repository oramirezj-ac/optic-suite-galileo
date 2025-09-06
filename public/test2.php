<?php
require_once '../config/session.php'; // Nos unimos a la sesión

echo '<h1>Página de Prueba 2</h1>';
echo '<p>Verificando el valor de la variable de sesión...</p>';

if (isset($_SESSION['test_variable'])) {
    echo '<h2 style="color: green;">✅ ÉXITO: El valor es "' . $_SESSION['test_variable'] . '"</h2>';
    echo '<p>Esto confirma que las sesiones SÍ están funcionando correctamente en tu servidor.</p>';
} else {
    echo '<h2 style="color: red;">❌ FALLO: La variable de sesión se ha perdido.</h2>';
    echo '<p>Esto confirma que hay un problema con la configuración de tu servidor que impide que las sesiones persistan.</p>';
}