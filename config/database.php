<?php

/* ==========================================================================
   Función para obtener la conexión a la Base de Datos (Patrón Singleton)
   ========================================================================== */

require_once __DIR__ . '/../vendor/autoload.php';

function getConnection(): PDO
{
    // Usamos una variable estática para que la conexión se cree una sola vez
    static $pdo = null;

    // Si la conexión aún no ha sido creada, la creamos
    if ($pdo === null) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $db_host = $_ENV['DB_HOST'];
        $db_name = $_ENV['DB_DATABASE'];
        $db_user = $_ENV['DB_USERNAME'];
        $db_pass = $_ENV['DB_PASSWORD'];
        $db_charset = $_ENV['DB_CHARSET'];

        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Creamos y asignamos la conexión a nuestra variable estática
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        } catch (\PDOException $e) {
            http_response_code(500);
            exit('Error de conexión a la base de datos: ' . $e->getMessage());
        }
    }

    // Devolvemos el objeto de conexión
    return $pdo;
}