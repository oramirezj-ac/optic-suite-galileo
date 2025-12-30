-- ========================================================================
-- SCRIPT SQL: Módulo de Consultas Nuevas
-- Versión: 1.0
-- Fecha: 2025-12-23
-- Base de Datos: optica_galileo_db
-- ========================================================================
-- IMPORTANTE: Ejecutar este script en las 4 bases de datos:
--   1. Base de datos de desarrollo (local)
--   2. Óptica 1 (producción)
--   3. Óptica 2 (producción)
--   4. Óptica 3 (producción)
-- ========================================================================

-- PASO 1: Hacer BACKUP de la base de datos antes de ejecutar
-- En HeidiSQL: Herramientas → Exportar base de datos como SQL

-- ========================================================================
-- 1. AGREGAR FOREIGN KEY FALTANTE (CRÍTICO)
-- ========================================================================
-- Tabla: abonos
-- Descripción: Agregar constraint de integridad referencial con ventas

ALTER TABLE `abonos`
ADD CONSTRAINT `fk_abonos_ventas` 
  FOREIGN KEY (`id_venta`) 
  REFERENCES `ventas` (`id_venta`) 
  ON DELETE CASCADE;

-- ========================================================================
-- 2. AGREGAR CAMPO PARA DISTANCIA PUPILAR DE CERCA
-- ========================================================================
-- Tabla: consultas
-- Descripción: DP de cerca = DP de lejos - 2mm (estándar común)

ALTER TABLE `consultas`
ADD COLUMN `dp_cerca` INT(11) NULL 
  COMMENT 'Distancia Pupilar de Cerca (DP Lejos - 2mm)' 
  AFTER `dp_oi`;

-- ========================================================================
-- 3. AGREGAR RELACIÓN CONSULTA-VENTA
-- ========================================================================
-- Tabla: ventas
-- Descripción: Vincular venta con la consulta que la originó

ALTER TABLE `ventas`
ADD COLUMN `consulta_id` INT(11) NULL 
  COMMENT 'ID de la consulta que generó esta venta' 
  AFTER `id_paciente`,
ADD KEY `fk_ventas_consultas` (`consulta_id`),
ADD CONSTRAINT `fk_ventas_consultas` 
  FOREIGN KEY (`consulta_id`) 
  REFERENCES `consultas` (`id`) 
  ON DELETE SET NULL;

-- ========================================================================
-- 4. CREAR CATÁLOGO DE PRODUCTOS MÉDICOS
-- ========================================================================
-- Descripción: Productos que se venden en consultas médicas
-- Ejemplos: Lubricantes oculares, medicamentos para ojo rojo, etc.

CREATE TABLE IF NOT EXISTS `catalogo_productos_medicos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `precio_sugerido` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio de referencia',
  `activo` TINYINT(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `fecha_creacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de productos médicos vendidos en consultas';

-- ========================================================================
-- 5. INSERTAR PRODUCTOS MÉDICOS INICIALES
-- ========================================================================

INSERT INTO `catalogo_productos_medicos` (`nombre`, `precio_sugerido`, `activo`) VALUES
('Splash (Lubricante Ocular)', 0.00, 1),
('Ocurelift (Ojo Rojo)', 0.00, 1),
('Hamamelis (Carnosidad)', 0.00, 1);

-- ========================================================================
-- 6. CREAR TABLA DE RELACIÓN CONSULTA-PRODUCTOS MÉDICOS
-- ========================================================================
-- Descripción: Productos vendidos en cada consulta médica

CREATE TABLE IF NOT EXISTS `consulta_productos_medicos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `consulta_id` INT(11) NOT NULL COMMENT 'ID de la consulta',
  `producto_id` INT(11) NOT NULL COMMENT 'ID del producto médico',
  `cantidad` INT(11) DEFAULT 1 COMMENT 'Cantidad vendida',
  `precio` DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Precio al que se vendió',
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_consulta_productos_consulta` (`consulta_id`),
  KEY `fk_consulta_productos_producto` (`producto_id`),
  CONSTRAINT `fk_consulta_productos_consulta` 
    FOREIGN KEY (`consulta_id`) 
    REFERENCES `consultas` (`id`) 
    ON DELETE CASCADE,
  CONSTRAINT `fk_consulta_productos_producto` 
    FOREIGN KEY (`producto_id`) 
    REFERENCES `catalogo_productos_medicos` (`id`)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Productos médicos vendidos en consultas';

-- ========================================================================
-- 7. VERIFICACIÓN DE CAMBIOS
-- ========================================================================
-- Ejecutar estas consultas para verificar que todo se aplicó correctamente

-- 7.1 Verificar foreign key en abonos
SELECT 
    CONSTRAINT_NAME, 
    TABLE_NAME, 
    REFERENCED_TABLE_NAME
FROM 
    information_schema.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = 'optica_galileo_db'
    AND TABLE_NAME = 'abonos'
    AND CONSTRAINT_NAME = 'fk_abonos_ventas';

-- 7.2 Verificar campo dp_cerca en consultas
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_COMMENT
FROM 
    information_schema.COLUMNS
WHERE 
    TABLE_SCHEMA = 'optica_galileo_db'
    AND TABLE_NAME = 'consultas'
    AND COLUMN_NAME = 'dp_cerca';

-- 7.3 Verificar campo consulta_id en ventas
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_COMMENT
FROM 
    information_schema.COLUMNS
WHERE 
    TABLE_SCHEMA = 'optica_galileo_db'
    AND TABLE_NAME = 'ventas'
    AND COLUMN_NAME = 'consulta_id';

-- 7.4 Verificar tabla catalogo_productos_medicos
SELECT COUNT(*) AS total_productos 
FROM catalogo_productos_medicos;

-- 7.5 Verificar tabla consulta_productos_medicos
SHOW CREATE TABLE consulta_productos_medicos;

-- ========================================================================
-- 8. ROLLBACK (EN CASO DE ERROR)
-- ========================================================================
-- Si algo sale mal, ejecutar estos comandos para revertir cambios:

/*
-- Revertir paso 6
DROP TABLE IF EXISTS `consulta_productos_medicos`;

-- Revertir paso 5 y 4
DROP TABLE IF EXISTS `catalogo_productos_medicos`;

-- Revertir paso 3
ALTER TABLE `ventas`
DROP FOREIGN KEY `fk_ventas_consultas`,
DROP KEY `fk_ventas_consultas`,
DROP COLUMN `consulta_id`;

-- Revertir paso 2
ALTER TABLE `consultas`
DROP COLUMN `dp_cerca`;

-- Revertir paso 1
ALTER TABLE `abonos`
DROP FOREIGN KEY `fk_abonos_ventas`;
*/

-- ========================================================================
-- FIN DEL SCRIPT
-- ========================================================================
-- Notas:
-- 1. Este script es IDEMPOTENTE (se puede ejecutar múltiples veces)
-- 2. Usar IF NOT EXISTS y IF EXISTS para evitar errores
-- 3. Hacer BACKUP antes de ejecutar en producción
-- 4. Verificar con las consultas del paso 7
-- ========================================================================
