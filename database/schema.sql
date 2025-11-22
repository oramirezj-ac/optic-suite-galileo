-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         11.8.2-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla optica_galileo_db.abonos
CREATE TABLE IF NOT EXISTS `abonos` (
  `id_abono` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date DEFAULT NULL,
  PRIMARY KEY (`id_abono`),
  KEY `id_venta` (`id_venta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.abonos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.armazones
CREATE TABLE IF NOT EXISTS `armazones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `tipo` enum('Completo','Tres piezas','Medio aire') NOT NULL,
  `existencia` int(11) NOT NULL DEFAULT 0,
  `precio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marca` (`marca`,`modelo`,`color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.armazones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.catalogo_agudeza_visual
CREATE TABLE IF NOT EXISTS `catalogo_agudeza_visual` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `valor` (`valor`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.catalogo_agudeza_visual: ~9 rows (aproximadamente)
INSERT INTO `catalogo_agudeza_visual` (`id`, `valor`) VALUES
	(2, '20/100'),
	(9, '20/15'),
	(8, '20/20'),
	(1, '20/200'),
	(7, '20/25'),
	(6, '20/30'),
	(5, '20/40'),
	(4, '20/50'),
	(3, '20/70');

-- Volcando estructura para tabla optica_galileo_db.catalogo_materiales
CREATE TABLE IF NOT EXISTS `catalogo_materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.catalogo_materiales: ~5 rows (aproximadamente)
INSERT INTO `catalogo_materiales` (`id`, `nombre`) VALUES
	(4, 'Alto Índice 1.67'),
	(5, 'Alto Índice 1.74'),
	(1, 'CR-39'),
	(3, 'Cristal (Vidrio)'),
	(2, 'Policarbonato');

-- Volcando estructura para tabla optica_galileo_db.catalogo_tipos_lente
CREATE TABLE IF NOT EXISTS `catalogo_tipos_lente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.catalogo_tipos_lente: ~4 rows (aproximadamente)
INSERT INTO `catalogo_tipos_lente` (`id`, `nombre`) VALUES
	(2, 'Flap Top (Bifocal)'),
	(1, 'Monofocal'),
	(4, 'Ocupacional'),
	(3, 'Progresivo');

-- Volcando estructura para tabla optica_galileo_db.catalogo_tratamientos
CREATE TABLE IF NOT EXISTS `catalogo_tratamientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.catalogo_tratamientos: ~5 rows (aproximadamente)
INSERT INTO `catalogo_tratamientos` (`id`, `nombre`) VALUES
	(3, 'Antiblue (Filtro Azul)'),
	(1, 'Antireflejante'),
	(4, 'Antirrayas'),
	(2, 'Fotocromático'),
	(5, 'Polarizado');

-- Volcando estructura para tabla optica_galileo_db.consultas
CREATE TABLE IF NOT EXISTS `consultas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `motivo_consulta` enum('Primera vez - requiere lentes','Primera vez - malestar/infección','Reconsulta - requiere lentes','Reconsulta - recaída') NOT NULL,
  `detalle_motivo` varchar(255) DEFAULT NULL,
  `av_ao_id` int(11) DEFAULT NULL,
  `av_od_id` int(11) DEFAULT NULL,
  `av_oi_id` int(11) DEFAULT NULL,
  `cv_ao_id` int(11) DEFAULT NULL,
  `cv_od_id` int(11) DEFAULT NULL,
  `cv_oi_id` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `dp_lejos_total` int(11) DEFAULT NULL COMMENT 'Distancia Pupilar Total (mm)',
  `dp_od` decimal(4,1) DEFAULT NULL COMMENT 'Distancia Nasopupilar OD (mm)',
  `dp_oi` decimal(4,1) DEFAULT NULL COMMENT 'Distancia Nasopupilar OI (mm)',
  `altura_oblea` varchar(50) DEFAULT NULL COMMENT 'Altura o referencia de montaje',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `av_ao_id` (`av_ao_id`),
  KEY `av_od_id` (`av_od_id`),
  KEY `av_oi_id` (`av_oi_id`),
  KEY `cv_ao_id` (`cv_ao_id`),
  KEY `cv_od_id` (`cv_od_id`),
  KEY `cv_oi_id` (`cv_oi_id`),
  KEY `fk_consultas_pacientes` (`paciente_id`),
  CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  CONSTRAINT `consultas_ibfk_3` FOREIGN KEY (`av_ao_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `consultas_ibfk_4` FOREIGN KEY (`av_od_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `consultas_ibfk_5` FOREIGN KEY (`av_oi_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `consultas_ibfk_6` FOREIGN KEY (`cv_ao_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `consultas_ibfk_7` FOREIGN KEY (`cv_od_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `consultas_ibfk_8` FOREIGN KEY (`cv_oi_id`) REFERENCES `catalogo_agudeza_visual` (`id`),
  CONSTRAINT `fk_consultas_pacientes` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.consultas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.graduaciones
CREATE TABLE IF NOT EXISTS `graduaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consulta_id` int(11) NOT NULL,
  `tipo` enum('autorrefractometro','foroptor','ambulatorio','lensometro','final') NOT NULL,
  `ojo` enum('AO','OD','OI') NOT NULL,
  `esfera` decimal(5,2) NOT NULL,
  `cilindro` decimal(5,2) NOT NULL,
  `eje` int(11) NOT NULL,
  `adicion` decimal(5,2) DEFAULT 0.00,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `es_graduacion_final` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `consulta_id` (`consulta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.graduaciones: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.lentes_de_contacto
CREATE TABLE IF NOT EXISTS `lentes_de_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `marca` varchar(50) NOT NULL,
  `modelo` varchar(50) NOT NULL,
  `tipo` enum('Cosmético (Color)','Graduado Esférico','Graduado Tóric','Cosmético Graduado') NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `parametros` varchar(255) DEFAULT NULL,
  `existencia` int(11) NOT NULL DEFAULT 0,
  `precio` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marca` (`marca`,`modelo`,`descripcion`,`parametros`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.lentes_de_contacto: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.pacientes
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `domicilio` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL CHECK (`edad` between 1 and 110),
  `antecedentes_medicos` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.pacientes: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `existencia` int(11) NOT NULL DEFAULT 0,
  `tipo_producto` enum('Armazon','Mica','Lente de Contacto','Solucion','Accesorio','Otro') DEFAULT 'Otro',
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `codigo_unico` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.productos: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol` enum('admin','vendedor') NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.users: ~3 rows (aproximadamente)
INSERT INTO `users` (`id`, `username`, `password`, `nombre_completo`, `email`, `rol`, `activo`, `fecha_creacion`, `fecha_actualizacion`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Sistema', 'admin@galileo.local', 'admin', 1, '2025-08-31 01:56:04', '2025-08-31 01:56:04'),
	(2, 'Omar', '$2y$12$p4Re9fJr6QhzyRLIMpd6zOzxcOAoLegqSaHiPi2UnA8pN2vLbLz06', 'Omar Ramírez Juárez', 'lic.omar.r.j@gmail.com', 'admin', 1, '2025-09-07 00:23:50', '2025-10-29 19:10:06'),
	(3, 'Vendedora1', '$2y$12$2CaM8D9cJ0wJJHYp04yxUeFw.4rH.6npJReckSvVE7g08z65XTHb.', 'Vendedora 1', 'vendedora1@galileo.local', 'vendedor', 1, '2025-09-07 01:41:01', '2025-09-07 01:50:47');

-- Volcando estructura para tabla optica_galileo_db.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id_venta` int(11) NOT NULL AUTO_INCREMENT,
  `id_paciente` int(11) DEFAULT NULL,
  `numero_nota` varchar(6) NOT NULL,
  `fecha_venta` date NOT NULL,
  `costo_total` decimal(10,2) NOT NULL,
  `estado_pago` enum('pagado','pendiente','abonos') NOT NULL,
  `observaciones_venta` text DEFAULT NULL,
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `domicilio_cliente` varchar(255) DEFAULT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `numero_nota_sufijo` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id_venta`),
  KEY `fk_ventas_pacientes` (`id_paciente`),
  CONSTRAINT `fk_ventas_pacientes` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.ventas: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.venta_detalles
CREATE TABLE IF NOT EXISTS `venta_detalles` (
  `id_detalle` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `fk_detalle_venta` (`id_venta`),
  KEY `fk_detalle_producto` (`id_producto`),
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.venta_detalles: ~0 rows (aproximadamente)

-- Volcando estructura para tabla optica_galileo_db.venta_detalle_tratamientos
CREATE TABLE IF NOT EXISTS `venta_detalle_tratamientos` (
  `id_detalle` int(11) NOT NULL,
  `id_tratamiento` int(11) NOT NULL,
  PRIMARY KEY (`id_detalle`,`id_tratamiento`),
  KEY `id_tratamiento` (`id_tratamiento`),
  CONSTRAINT `venta_detalle_tratamientos_ibfk_1` FOREIGN KEY (`id_detalle`) REFERENCES `venta_detalles` (`id_detalle`) ON DELETE CASCADE,
  CONSTRAINT `venta_detalle_tratamientos_ibfk_2` FOREIGN KEY (`id_tratamiento`) REFERENCES `catalogo_tratamientos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla optica_galileo_db.venta_detalle_tratamientos: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
-- Dump completed on 2025-10-29 20:15:23