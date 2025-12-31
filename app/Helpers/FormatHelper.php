<?php
/* ==========================================================================
   Helper para Formateo de Vistas
   ========================================================================== */

class FormatHelper
{
    /**
     * Convierte una fecha al formato "largo" en español.
     * Ej: "viernes, 14 de noviembre de 2025"
     */
    public static function dateFull($dateString)
    {
        if (empty($dateString)) {
            return 'N/A';
        }

        $formatter = new IntlDateFormatter(
            'es_ES', 
            IntlDateFormatter::FULL, 
            IntlDateFormatter::NONE
        );
        
        return htmlspecialchars($formatter->format(strtotime($dateString)));
    }

    /**
     * Calcula la edad actual.
     */
    public static function calculateAge($fechaNacimiento)
    {
        if (empty($fechaNacimiento)) {
            return 'Edad desconocida';
        }
        
        try {
            $nacimiento = new DateTime($fechaNacimiento);
            $hoy = new DateTime();
            $diferencia = $hoy->diff($nacimiento);
            
            return $diferencia->y . ' años';
        } catch (Exception $e) {
            return 'Error en fecha';
        }
    }

    /**
     * Genera inputs ocultos (Para persistencia de datos).
     */
    public static function renderNewPatientHiddenFields($data)
    {
        $fields = [
            'nombre', 'apellido_paterno', 'apellido_materno', 
            'telefono', 'domicilio', 'antecedentes_medicos', 
            'edad', 'fecha_nacimiento', 'fecha_primera_visita'
        ];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                echo '<input type="hidden" name="' . htmlspecialchars($field) . '" value="' . htmlspecialchars($data[$field]) . '">' . PHP_EOL;
            }
        }
    }

    /* =========================================================
       FUNCIONES PARA VENTAS
       ========================================================= */

    /**
     * Formatea una cantidad monetaria.
     * Ej: 1500.5 -> "$ 1,500.50"
     */
    public static function money($amount)
    {
        if (!is_numeric($amount)) {
            return '$ 0.00';
        }
        return '$ ' . number_format((float)$amount, 2);
    }

    /**
     * Formatea una fecha en formato corto para tablas.
     * Ej: "23/12/2025"
     */
    public static function dateShort($dateString)
    {
        if (empty($dateString)) {
            return '-';
        }
        return date('d/m/Y', strtotime($dateString));
    }

    /* =========================================================
       FUNCIONES PARA ESTANDARIZACIÓN DE FORMATO
       ========================================================= */

    /**
     * Formatea el nombre completo de un paciente de forma estandarizada.
     * Acepta un array con las claves 'nombre', 'apellido_paterno', 'apellido_materno'
     * o parámetros individuales.
     * 
     * @param mixed $patient Array con datos del paciente o string con nombre
     * @param string|null $apellidoPaterno Apellido paterno (opcional si $patient es array)
     * @param string|null $apellidoMaterno Apellido materno (opcional)
     * @return string Nombre formateado: "Nombre Apellido Paterno Apellido Materno"
     */
    public static function patientName($patient, $apellidoPaterno = null, $apellidoMaterno = null)
    {
        // Si es un array, extraemos los valores
        if (is_array($patient)) {
            $nombre = $patient['nombre'] ?? '';
            $apellidoPaterno = $patient['apellido_paterno'] ?? '';
            $apellidoMaterno = $patient['apellido_materno'] ?? '';
        } else {
            // Si es string, usamos los parámetros
            $nombre = $patient ?? '';
        }

        // Filtramos valores vacíos y unimos con espacio
        $parts = array_filter([
            trim($nombre),
            trim($apellidoPaterno ?? ''),
            trim($apellidoMaterno ?? '')
        ]);

        $fullName = implode(' ', $parts);
        
        return !empty($fullName) ? htmlspecialchars($fullName) : 'Sin nombre';
    }

    /**
     * Formatea el número de nota de venta de forma estandarizada.
     * 
     * @param string|int $numeroNota Número de nota
     * @param string|null $sufijo Sufijo opcional (ej: 'D' para duplicado)
     * @param bool $withLabel Si debe incluir "Nota de Venta" o solo el número
     * @return string Nota formateada: "Nota de Venta 0787" o "0787 (D)"
     */
    public static function saleNote($numeroNota, $sufijo = null, $withLabel = false)
    {
        if (empty($numeroNota)) {
            return $withLabel ? 'Nota de Venta S/N' : 'S/N';
        }

        // Formateamos el número (aseguramos 4 dígitos con ceros a la izquierda)
        $formattedNumber = str_pad($numeroNota, 4, '0', STR_PAD_LEFT);
        
        // Agregamos sufijo si existe
        if (!empty($sufijo)) {
            $formattedNumber .= ' <small>(' . htmlspecialchars($sufijo) . ')</small>';
        }

        // Agregamos label si se solicita
        if ($withLabel) {
            return 'Nota de Venta ' . $formattedNumber;
        }

        return $formattedNumber;
    }

    /**
     * Formatea el número de nota de venta para títulos/headers.
     * Siempre incluye el label "Nota de Venta".
     * 
     * @param string|int $numeroNota Número de nota
     * @param string|null $sufijo Sufijo opcional
     * @return string "Nota de Venta 0787"
     */
    public static function saleNoteTitle($numeroNota, $sufijo = null)
    {
        return self::saleNote($numeroNota, $sufijo, true);
    }
}