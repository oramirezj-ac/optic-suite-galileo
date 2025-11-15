<?php
/* ==========================================================================
   Helper para Generación de Elementos de Formulario Reutilizables
   ========================================================================== */

class FormHelper
{
    /**
     * Genera el bloque HTML completo para capturar la graduación de un ojo.
     * Reutiliza las clases CSS que ya definimos en 'graduaciones.css'.
     *
     * @param string $ojo 'OD' o 'OI', para las etiquetas y nombres de campo.
     * @return string El bloque HTML del formulario.
     */
    public static function renderGraduationRow($ojo)
    {
        // Convertimos 'OD' en 'od' para los atributos 'name'
        $ojoLower = strtolower($ojo);

        // Usamos HEREDOC (<<<HTML ... HTML;) para escribir el bloque
        // de HTML de forma limpia, sin concatenar strings.
        
        $html = <<<HTML
        
            <span class="graduacion-ojo-label">{$ojo}</span>
            <div class="graduacion-formula">
                
                <input type="number" 
                       name="{$ojoLower}_esfera" 
                       placeholder="Esfera" 
                       class="valor" 
                       step="0.25" 
                       min="-20.00" 
                       max="20.00"
                       required>
                
                <span class="simbolo">=</span>

                <input type="number" 
                       name="{$ojoLower}_cilindro" 
                       placeholder="Cilindro" 
                       class="valor"
                       step="0.25"
                       max="0.00"
                       min="-10.00"
                       list="valores_cilindro">

                <span class="simbolo">x</span>

                <input type="number" 
                       name="{$ojoLower}_eje" 
                       placeholder="Eje" 
                       class="valor"
                       min="0"
                       max="180"
                       step="1">

                <span class="simbolo">°</span>

                <select name="{$ojoLower}_adicion" class="valor valor-add">
                    <option value="0.00" selected>0.00</option>
                    <option value="0.25">0.25</option>
                    <option value="0.50">0.50</option>
                    <option value="0.75">0.75</option>
                    <option value="1.00">1.00</option>
                    <option value="1.25">1.25</option>
                    <option value="1.50">1.50</option>
                    <option value="1.75">1.75</option>
                    <option value="2.00">2.00</option>
                    <option value="2.25">2.25</option>
                    <option value="2.50">2.50</option>
                    <option value="2.75">2.75</option>
                    <option value="3.00">3.00</option>
                    <option value="3.25">3.25</option>
                    <option value="3.50">3.50</option>
                    <option value="3.75">3.75</option>
                    <option value="4.00">4.00</option>
                </select>
                
            </div>
HTML;

        return $html;
    }
}