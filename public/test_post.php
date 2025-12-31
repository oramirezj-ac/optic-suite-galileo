<?php
// Test file to debug POST issue
error_log("=== VENTA UPDATE TEST ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . print_r($_GET, true));
error_log("POST params: " . print_r($_POST, true));
error_log("=========================");

// Redirect back
header('Location: /index.php?page=ventas_index');
exit();
