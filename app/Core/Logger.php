<?php
/* ==========================================================================
   Logger Centralizado usando Monolog
   ========================================================================== */

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger
{
    /**
     * @var MonologLogger|null Instancia del logger
     */
    private static ?MonologLogger $logger = null;

    /**
     * Inicializa el logger si no existe.
     * 
     * @return MonologLogger
     */
    private static function getLogger(): MonologLogger
    {
        if (self::$logger === null) {
            self::$logger = new MonologLogger('optic-suite');
            
            // Directorio de logs
            $logDir = __DIR__ . '/../../logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Handler con rotación diaria (mantiene 30 días de logs)
            $handler = new RotatingFileHandler(
                $logDir . '/app.log',
                30, // Días a mantener
                MonologLogger::DEBUG
            );

            // Formato personalizado: [fecha] [nivel] mensaje contexto
            $formatter = new LineFormatter(
                "[%datetime%] [%level_name%] %message% %context%\n",
                "Y-m-d H:i:s"
            );
            
            $handler->setFormatter($formatter);
            self::$logger->pushHandler($handler);
        }

        return self::$logger;
    }

    /**
     * Log de nivel DEBUG - Información detallada para depuración.
     * 
     * @param string $message Mensaje del log
     * @param array $context Contexto adicional (variables, IDs, etc.)
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    /**
     * Log de nivel INFO - Eventos informativos del sistema.
     * 
     * @param string $message Mensaje del log
     * @param array $context Contexto adicional
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    /**
     * Log de nivel WARNING - Situaciones anormales que no son errores.
     * 
     * @param string $message Mensaje del log
     * @param array $context Contexto adicional
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    /**
     * Log de nivel ERROR - Errores en runtime que permiten continuar.
     * 
     * @param string $message Mensaje del log
     * @param array $context Contexto adicional (incluir Exception si existe)
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    /**
     * Log de nivel CRITICAL - Condiciones críticas del sistema.
     * 
     * @param string $message Mensaje del log
     * @param array $context Contexto adicional
     * @return void
     */
    public static function critical(string $message, array $context = []): void
    {
        self::getLogger()->critical($message, $context);
    }

    /**
     * Helper para loguear excepciones con stack trace completo.
     * 
     * @param Throwable $exception Excepción a loguear
     * @param string $customMessage Mensaje personalizado (opcional)
     * @return void
     */
    public static function exception(Throwable $exception, string $customMessage = ''): void
    {
        $message = $customMessage ?: 'Exception capturada: ' . $exception->getMessage();
        
        self::error($message, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Log de actividad de usuario (login, acciones importantes).
     * 
     * @param string $action Acción realizada
     * @param int|null $userId ID del usuario
     * @param array $details Detalles adicionales
     * @return void
     */
    public static function userActivity(string $action, ?int $userId = null, array $details = []): void
    {
        self::info('Actividad de usuario: ' . $action, [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ]);
    }

    /**
     * Log de consultas SQL problemáticas (opcional, para debugging).
     * 
     * @param string $query Query ejecutada
     * @param array $params Parámetros de la query
     * @param float|null $executionTime Tiempo de ejecución en segundos
     * @return void
     */
    public static function sqlQuery(string $query, array $params = [], ?float $executionTime = null): void
    {
        $context = [
            'query' => $query,
            'params' => $params
        ];

        if ($executionTime !== null) {
            $context['execution_time'] = round($executionTime, 4) . 's';
        }

        // Solo loguea queries lentas (>1 segundo)
        if ($executionTime === null || $executionTime > 1.0) {
            self::warning('Query SQL lenta o problemática', $context);
        }
    }
}
