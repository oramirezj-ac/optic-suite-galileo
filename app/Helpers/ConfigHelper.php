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
            'Samantha',
            'Michelle',
            'Liz',
            'Ale',
            
            // --- Combinaciones (Comisión Compartida) ---
            
            'Samantha, Michelle',
            'Samantha, Liz',
            'Samantha, Ale',
            'Michelle, Liz',
            'Michelle, Ale',
            
            // Agrega aquí cualquier otra combinación frecuente...
        ];
    }

    /**
     * Lista de Métodos de Pago disponibles.
     */
    public static function getMetodosPago()
    {
        return [
            'Efectivo',
            'Tarjeta de Débito',
            'Tarjeta de Crédito',
            'Transferencia'
        ];
    }
}