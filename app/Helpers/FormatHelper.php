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
       NUEVAS FUNCIONES AGREGADAS (Para Ventas)
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
}