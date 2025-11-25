<?php
/* ==========================================================================
   Validador Centralizado de Datos
   ========================================================================== */

class Validator
{
    /**
     * @var array Datos a validar
     */
    private array $data;

    /**
     * @var array Errores de validación acumulados
     */
    private array $errors = [];

    /**
     * @var array Datos validados y limpios
     */
    private array $validatedData = [];

    /**
     * Constructor.
     * 
     * @param array $data Datos a validar (típicamente $_POST o $_GET)
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Valida que campos sean obligatorios (no vacíos).
     * 
     * @param array $fields Lista de nombres de campos
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function required(array $fields, string $message = 'El campo {field} es obligatorio'): self
    {
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
                $this->errors[$field][] = str_replace('{field}', $field, $message);
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo tenga formato de email válido.
     * 
     * @param string $field Nombre del campo
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function email(string $field, string $message = 'El formato del email es inválido'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = filter_var($this->data[$field], FILTER_SANITIZE_EMAIL);
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo sea un teléfono válido (10 dígitos).
     * 
     * @param string $field Nombre del campo
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function phone(string $field, string $message = 'El teléfono debe tener 10 dígitos'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $cleaned = preg_replace('/[^0-9]/', '', $this->data[$field]);
            
            if (strlen($cleaned) !== 10) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = $cleaned;
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo sea un número entero.
     * 
     * @param string $field Nombre del campo
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function integer(string $field, string $message = 'El campo debe ser un número entero'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_INT)) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = (int) $this->data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo sea numérico (int o float).
     * 
     * @param string $field Nombre del campo
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function numeric(string $field, string $message = 'El campo debe ser numérico'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = $this->data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Valida longitud mínima de una cadena.
     * 
     * @param string $field Nombre del campo
     * @param int $min Longitud mínima
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function minLength(string $field, int $min, string $message = 'El campo debe tener al menos {min} caracteres'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $length = mb_strlen($this->data[$field], 'UTF-8');
            
            if ($length < $min) {
                $this->errors[$field][] = str_replace('{min}', (string) $min, $message);
            }
        }
        
        return $this;
    }

    /**
     * Valida longitud máxima de una cadena.
     * 
     * @param string $field Nombre del campo
     * @param int $max Longitud máxima
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function maxLength(string $field, int $max, string $message = 'El campo no debe exceder {max} caracteres'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $length = mb_strlen($this->data[$field], 'UTF-8');
            
            if ($length > $max) {
                $this->errors[$field][] = str_replace('{max}', (string) $max, $message);
            }
        }
        
        return $this;
    }

    /**
     * Valida que un número esté dentro de un rango.
     * 
     * @param string $field Nombre del campo
     * @param int|float $min Valor mínimo
     * @param int|float $max Valor máximo
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function between(string $field, $min, $max, string $message = 'El valor debe estar entre {min} y {max}'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $value = $this->data[$field];
            
            if (!is_numeric($value) || $value < $min || $value > $max) {
                $msg = str_replace(['{min}', '{max}'], [(string) $min, (string) $max], $message);
                $this->errors[$field][] = $msg;
            }
        }
        
        return $this;
    }

    /**
     * Valida usando una expresión regular.
     * 
     * @param string $field Nombre del campo
     * @param string $pattern Patrón regex
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function regex(string $field, string $pattern, string $message = 'El formato del campo es inválido'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!preg_match($pattern, $this->data[$field])) {
                $this->errors[$field][] = $message;
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo sea una fecha válida.
     * 
     * @param string $field Nombre del campo
     * @param string $format Formato de fecha (default: Y-m-d)
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function date(string $field, string $format = 'Y-m-d', string $message = 'La fecha no es válida'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = $this->data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Valida que un campo coincida con otro (ej: confirmar password).
     * 
     * @param string $field Campo a validar
     * @param string $matchField Campo con el que debe coincidir
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function matches(string $field, string $matchField, string $message = 'Los campos no coinciden'): self
    {
        if (isset($this->data[$field]) && isset($this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field][] = $message;
            }
        }
        
        return $this;
    }

    /**
     * Valida usando una función personalizada.
     * 
     * @param string $field Nombre del campo
     * @param callable $callback Función que retorna true si es válido
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function custom(string $field, callable $callback, string $message = 'El campo no es válido'): self
    {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $this->errors[$field][] = $message;
            }
        }
        
        return $this;
    }

    /**
     * Valida que un valor esté en una lista permitida.
     * 
     * @param string $field Nombre del campo
     * @param array $allowedValues Valores permitidos
     * @param string $message Mensaje de error personalizado
     * @return self Para encadenar métodos
     */
    public function in(string $field, array $allowedValues, string $message = 'El valor seleccionado no es válido'): self
    {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!in_array($this->data[$field], $allowedValues, true)) {
                $this->errors[$field][] = $message;
            } else {
                $this->validatedData[$field] = $this->data[$field];
            }
        }
        
        return $this;
    }

    /**
     * Verifica si los datos son válidos (no hay errores).
     * 
     * @return bool True si no hay errores
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Obtiene todos los errores de validación.
     * 
     * @return array Array asociativo [campo => [errores]]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene el primer error de cada campo.
     * 
     * @return array Array asociativo [campo => primer_error]
     */
    public function getFirstErrors(): array
    {
        $firstErrors = [];
        
        foreach ($this->errors as $field => $errors) {
            $firstErrors[$field] = $errors[0] ?? '';
        }
        
        return $firstErrors;
    }

    /**
     * Obtiene todos los errores como una lista plana.
     * 
     * @return array Lista de todos los mensajes de error
     */
    public function getAllErrors(): array
    {
        $allErrors = [];
        
        foreach ($this->errors as $field => $errors) {
            $allErrors = array_merge($allErrors, $errors);
        }
        
        return $allErrors;
    }

    /**
     * Obtiene los datos validados y limpios.
     * 
     * @return array Datos validados
     */
    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    /**
     * Obtiene un campo validado específico.
     * 
     * @param string $field Nombre del campo
     * @param mixed $default Valor por defecto si no existe
     * @return mixed Valor del campo o default
     */
    public function get(string $field, $default = null)
    {
        return $this->validatedData[$field] ?? $this->data[$field] ?? $default;
    }

    /**
     * Sanitiza todos los datos de entrada.
     * Útil para obtener datos limpios aunque no se hayan aplicado reglas.
     * 
     * @return array Datos sanitizados
     */
    public function getSanitizedData(): array
    {
        $sanitized = [];
        
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = strip_tags(trim($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
