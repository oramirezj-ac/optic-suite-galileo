-- =============================================================================
-- MIGRACIÓN: Vinculación Automática de Ventas con Consultas (HeidiSQL)
-- =============================================================================
-- Instrucciones:
-- 1. Selecciona la base de datos de la óptica.
-- 2. Ejecuta este script.
-- 3. Repite para las otras 3 ópticas.
-- =============================================================================

UPDATE ventas v
SET consulta_id = (
    SELECT c.id
    FROM consultas c
    WHERE c.paciente_id = v.id_paciente
      AND c.motivo_consulta = 'Refractiva'
    -- Ordenamos por la diferencia absoluta de tiempo (La más cercana, sea antes o después)
    ORDER BY ABS(TIMESTAMPDIFF(SECOND, c.fecha, v.fecha_venta)) ASC
    LIMIT 1
)
WHERE 
    v.consulta_id IS NULL  -- Solo ventas que no tienen vínculo
    AND v.id_paciente IS NOT NULL;

-- Verificación (Opcional)
-- SELECT * FROM ventas WHERE consulta_id IS NOT NULL LIMIT 10;
