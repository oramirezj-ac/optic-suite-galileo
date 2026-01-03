-- =============================================================================
-- MIGRACIÓN CORREGIDA: Vinculación de Ventas (Segura para DBs desactualizadas)
-- =============================================================================

-- 1. Primero, aseguramos que el campo 'consulta_id' exista en la tabla 'ventas'.
--    (Si tu base de datos es vieja, este comando lo creará automáticamente).
ALTER TABLE ventas 
ADD COLUMN IF NOT EXISTS consulta_id INT(11) DEFAULT NULL COMMENT 'ID de la consulta vinculada';

-- 2. Ahora sí ejecutamos la vinculación automática.
UPDATE ventas v
SET v.consulta_id = (
    SELECT c.id
    FROM consultas c
    WHERE c.paciente_id = v.id_paciente
      AND c.motivo_consulta = 'Refractiva'
    ORDER BY ABS(TIMESTAMPDIFF(SECOND, c.fecha, v.fecha_venta)) ASC
    LIMIT 1
)
WHERE 
    v.consulta_id IS NULL 
    AND v.id_paciente IS NOT NULL;

-- 3. Verificamos los resultados
SELECT count(*) as ventas_vinculadas FROM ventas WHERE consulta_id IS NOT NULL;
