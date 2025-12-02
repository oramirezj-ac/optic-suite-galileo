<?php
/* ==========================================================================
   Clase Response - Manejo Estandarizado de Respuestas HTTP
   ========================================================================== */

class Response
{
    /**
     * Realiza una redirección HTTP.
     * 
     * @param string $url URL completa o relativa
     * @param int $statusCode Código HTTP (301, 302, etc.)
     * @return void
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header('Location: ' . $url);
        exit();
    }

    /**
     * Redirecciona a una página del sistema con mensaje de éxito.
     * 
     * @param string $page Nombre de la página (ej: 'patients_details')
     * @param string $message Mensaje de éxito
     * @param array $params Parámetros adicionales (ej: ['id' => 5])
     * @return void
     */
    public static function redirectWithSuccess(string $page, string $message, array $params = []): void
    {
        // Guarda el mensaje en sesión para mostrarlo después
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['flash_success'] = $message;
        
        // Construye la URL
        $url = '/index.php?page=' . $page;
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode((string) $value);
        }
        
        self::redirect($url);
    }

    /**
     * Redirecciona a una página del sistema con mensaje de error.
     * 
     * @param string $page Nombre de la página
     * @param string $message Mensaje de error
     * @param array $params Parámetros adicionales
     * @return void
     */
    public static function redirectWithError(string $page, string $message, array $params = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['flash_error'] = $message;
        
        $url = '/index.php?page=' . $page;
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode((string) $value);
        }
        
        self::redirect($url);
    }

    /**
     * Redirecciona con mensaje informativo.
     * 
     * @param string $page Nombre de la página
     * @param string $message Mensaje informativo
     * @param array $params Parámetros adicionales
     * @return void
     */
    public static function redirectWithInfo(string $page, string $message, array $params = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION['flash_info'] = $message;
        
        $url = '/index.php?page=' . $page;
        foreach ($params as $key => $value) {
            $url .= '&' . urlencode($key) . '=' . urlencode((string) $value);
        }
        
        self::redirect($url);
    }

    /**
     * Obtiene y limpia un mensaje flash de la sesión.
     * 
     * @param string $type Tipo de mensaje ('success', 'error', 'info')
     * @return string|null El mensaje o null si no existe
     */
    public static function getFlashMessage(string $type): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $key = 'flash_' . $type;
        
        if (isset($_SESSION[$key])) {
            $message = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $message;
        }

        return null;
    }

    /**
     * Renderiza mensajes flash como HTML.
     * Útil para incluir en las vistas.
     * 
     * @return string HTML de los mensajes
     */
    public static function renderFlashMessages(): string
    {
        $html = '';
        
        $success = self::getFlashMessage('success');
        if ($success) {
            $html .= '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
        }
        
        $error = self::getFlashMessage('error');
        if ($error) {
            $html .= '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>';
        }
        
        $info = self::getFlashMessage('info');
        if ($info) {
            $html .= '<div class="alert alert-info">' . htmlspecialchars($info) . '</div>';
        }
        
        return $html;
    }

    /**
     * Envía una respuesta JSON (útil para APIs futuras).
     * 
     * @param mixed $data Datos a enviar
     * @param int $statusCode Código HTTP
     * @param array $headers Headers adicionales
     * @return void
     */
    public static function json($data, int $statusCode = 200, array $headers = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        foreach ($headers as $key => $value) {
            header($key . ': ' . $value);
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    }

    /**
     * Envía una respuesta JSON de éxito.
     * 
     * @param mixed $data Datos de respuesta
     * @param string $message Mensaje opcional
     * @param int $statusCode Código HTTP
     * @return void
     */
    public static function jsonSuccess($data = null, string $message = 'Success', int $statusCode = 200): void
    {
        $response = [
            'status' => 'success',
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        self::json($response, $statusCode);
    }

    /**
     * Envía una respuesta JSON de error.
     * 
     * @param string $message Mensaje de error
     * @param int $statusCode Código HTTP
     * @param array|null $errors Detalles de errores
     * @return void
     */
    public static function jsonError(string $message, int $statusCode = 400, ?array $errors = null): void
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        self::json($response, $statusCode);
    }

    /**
     * Envía código de estado HTTP y termina la ejecución.
     * 
     * @param int $statusCode Código HTTP
     * @param string|null $message Mensaje opcional
     * @return void
     */
    public static function abort(int $statusCode, ?string $message = null): void
    {
        http_response_code($statusCode);
        
        if ($message !== null) {
            echo '<h1>Error ' . $statusCode . '</h1>';
            echo '<p>' . htmlspecialchars($message) . '</p>';
        }
        
        exit();
    }

    /**
     * Envía headers para descargar un archivo.
     * 
     * @param string $filename Nombre del archivo
     * @param string $content Contenido del archivo
     * @param string $mimeType Tipo MIME
     * @return void
     */
    public static function download(string $filename, string $content, string $mimeType = 'application/octet-stream'): void
    {
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        echo $content;
        exit();
    }

    /**
     * Establece código de respuesta HTTP sin terminar la ejecución.
     * 
     * @param int $statusCode Código HTTP
     * @return void
     */
    public static function setStatusCode(int $statusCode): void
    {
        http_response_code($statusCode);
    }

    /**
     * Envía un header HTTP personalizado.
     * 
     * @param string $name Nombre del header
     * @param string $value Valor del header
     * @return void
     */
    public static function setHeader(string $name, string $value): void
    {
        header($name . ': ' . $value);
    }
}
