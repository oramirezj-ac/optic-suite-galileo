<?php
/* ==========================================================================
   Helper para Configuraciones y Listas Estáticas (Selectores)
   ========================================================================== */

class ConfigHelper
{
    /**
     * Devuelve la lista de vendedores y combinaciones para comisiones.
     * Usado en formularios de Venta.
     * * @return array Lista de opciones.
     */
    public static function getVendedoresList()
    {
        return [
            // --- Individuales ---
            'Valeria',
            
            // --- Combinaciones (Comisión Compartida) ---
            
            'Valeria, Ana',
            
            // Agrega aquí cualquier otra combinación frecuente...
        ];
    }
}