<?php
require_once '../config/session.php'; // Es buena práctica incluirlo aquí también

session_unset();
session_destroy();

// CORRECCIÓN: Añadimos la barra inicial
header("Location: /login.php");
exit();