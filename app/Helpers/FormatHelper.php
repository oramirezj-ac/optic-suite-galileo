<?php
/* ==========================================================================
   Helper para Formateo de Vistas
   ========================================================================== */

class FormatHelper
{
    /**
     * Convierte una fecha (ej. '2025-11-14 18:00') al formato 
     * "largo" en español (ej. "viernes, 14 de noviembre de 2025").
     *
     * @param string $dateString La fecha de la base de datos.
     * @return string La fecha formateada.
     */
    public static function dateFull($dateString)
    {
        // Si la fecha está vacía, no intentes formatear
        if (empty($dateString)) {
            return 'N/A';
        }

        // Creamos un formateador para Español (es_ES), con fecha COMPLETA
        $formatter = new IntlDateFormatter(
            'es_ES', 
            IntlDateFormatter::FULL, // Esto da "viernes, 14 de noviembre de 2025"
            IntlDateFormatter::NONE
        );
        
        return htmlspecialchars($formatter->format(strtotime($dateString)));
    }

    /**
     * (Aquí podremos añadir más funciones en el futuro, 
     * ej. formatCurrency(), formatPhone(), etc.)
     */
}