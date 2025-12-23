<?php
/* ==========================================================================
   Helper de Seguridad - Protección XSS, CSRF y Sanitización
   ========================================================================== */

class SecurityHelper
{
    /**
     * Escapa datos para prevenir XSS (Cross-Site Scripting).
     * 
     * @param string|null $data Dato a escapar
     * @param int $flags Flags de htmlspecialchars (default: ENT_QUOTES | ENT_HTML5)
     * @return string Dato escapado y seguro para mostrar en HTML
     */
    public static function escape($data, int $flags = ENT_QUOTES | ENT_HTML5): string
    {
        if ($data === null || $data === '') {
            return '';
        }
        
        return htmlspecialchars((string) $data, $flags, 'UTF-8');
    }

    /**
     * Escapa un array completo de datos recursivamente.
     * 
     * @param array $data Array a escapar
     * @return array Array con valores escapados
     */
    public static function escapeArray(array $data): array
    {
        $escaped = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $escaped[$key] = self::escapeArray($value);
            } else {
                $escaped[$key] = self::escape($value);
            }
        }
        return $escaped;
    }

    /**
     * Genera un token CSRF y lo almacena en la sesión.
     * 
     * @return string El token generado
     * @throws RuntimeException Si las sesiones no están iniciadas
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Las sesiones deben estar iniciadas para generar tokens CSRF');
        }

        // Genera un token único usando bytes aleatorios
        $token = bin2hex(random_bytes(32));
        
        // Almacena en sesión con timestamp para expiración
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }

    /**
     * Obtiene el token CSRF actual o genera uno nuevo si no existe.
     * 
     * @return string El token CSRF
     */
    public static function getCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Si no existe token o expiró (más de 2 horas), genera uno nuevo
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > 7200) {
            return self::generateCsrfToken();
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica que un token CSRF sea válido.
     * 
     * @param string $token Token a verificar
     * @return bool True si es válido, false si no
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        // Verifica que exista el token en sesión
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Verifica que no haya expirado (2 horas)
        if (!isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > 7200) {
            return false;
        }

        // Compara usando hash_equals para prevenir timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Genera un campo hidden con el token CSRF para incluir en formularios.
     * 
     * @return string HTML del campo hidden
     */
    public static function csrfField(): string
    {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Valida y sanitiza un email.
     * 
     * @param string $email Email a validar
     * @return string|false Email sanitizado o false si es inválido
     */
    public static function sanitizeEmail(string $email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        
        return false;
    }

    /**
     * Sanitiza una cadena removiendo tags HTML y caracteres especiales.
     * 
     * @param string $string Cadena a sanitizar
     * @return string Cadena sanitizada
     */
    public static function sanitizeString(string $string): string
    {
        // Remueve tags HTML
        $string = strip_tags($string);
        
        // Remueve espacios múltiples
        $string = preg_replace('/\s+/', ' ', $string);
        
        // Trim
        return trim($string);
    }

    /**
     * Valida que una cadena sea un número de teléfono válido (10 dígitos).
     * 
     * @param string $phone Teléfono a validar
     * @return bool True si es válido
     */
    public static function isValidPhone(string $phone): bool
    {
        // Remueve caracteres no numéricos
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Valida que tenga exactamente 10 dígitos
        return strlen($cleaned) === 10;
    }

    /**
     * Sanitiza un número de teléfono dejando solo dígitos.
     * 
     * @param string $phone Teléfono a sanitizar
     * @return string Teléfono con solo dígitos
     */
    public static function sanitizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Establece headers HTTP de seguridad recomendados.
     * 
     * @return void
     */
    public static function setSecurityHeaders(): void
    {
        // Previene clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Previene MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Habilita protección XSS del navegador
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy básico
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Feature Policy
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    }

    /**
     * Valida que una cadena tenga una longitud permitida.
     * 
     * @param string $string Cadena a validar
     * @param int $min Longitud mínima
     * @param int $max Longitud máxima
     * @return bool True si está en el rango
     */
    public static function validateLength(string $string, int $min, int $max): bool
    {
        $length = mb_strlen($string, 'UTF-8');
        return $length >= $min && $length <= $max;
    }

    /**
     * Genera un hash seguro de una contraseña.
     * 
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verifica una contraseña contra su hash.
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool True si coinciden
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Valida que una cadena contenga solo caracteres alfanuméricos.
     * 
     * @param string $string Cadena a validar
     * @return bool True si es alfanumérica
     */
    public static function isAlphanumeric(string $string): bool
    {
        return ctype_alnum($string);
    }

    /**
     * Limpia datos de entrada de forma genérica.
     * 
     * @param mixed $data Dato a limpiar
     * @param string $type Tipo de limpieza ('string', 'email', 'phone', 'int', 'float')
     * @return mixed Dato limpio
     */
    public static function sanitize($data, string $type = 'string')
    {
        if ($data === null) {
            return null;
        }

        switch ($type) {
            case 'email':
                return self::sanitizeEmail((string) $data);
            
            case 'phone':
                return self::sanitizePhone((string) $data);
            
            case 'int':
                return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            
            case 'float':
                return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            case 'string':
            default:
                return self::sanitizeString((string) $data);
        }
    }
}
