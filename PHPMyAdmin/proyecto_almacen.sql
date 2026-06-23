SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_almacen`
--

-- Desactivamos revisión de llaves foráneas para limpiar y reestructurar sin errores
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- LIMPIEZA DE TABLAS EXISTENTES
-- --------------------------------------------------------
DROP TABLE IF EXISTS `pagos_fiados`;
DROP TABLE IF EXISTS `detalle_ventas`;
DROP TABLE IF EXISTS `ventas`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `registro`;
DROP TABLE IF EXISTS `roles`;

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `roles`
-- --------------------------------------------------------
CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Usuario');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `registro`
-- --------------------------------------------------------
CREATE TABLE `registro` (
  `id` int(11) NOT NULL,
  `nombre_apellido` varchar(150) NOT NULL,
  `rut` varchar(12) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `registro` (`id`, `nombre_apellido`, `rut`, `correo`, `contrasena`, `rol_id`, `activo`, `creado_en`, `reset_token`, `token_expiracion`) VALUES
(1, 'Usuario de Prueba', '12.345.678-9', 'prueba@correo.com', 'clave123', 1, 1, '2026-06-10 23:27:49', NULL, NULL),
(2, 'Gaspar', '209815354', 'gaspar.ar.03@gmail.com', '$2y$10$zWzqv9zW9R.cmPk672yBie5TPZIaSJeSwETrnAl.1IFh1znw1Rjwm', 1, 1, '2026-06-10 23:28:39', '988b417d46b1237f25756047e19b5f5d472df8430d7e4e63cf1563535a93702e', '2026-06-11 02:49:54'),
(3, 'Nicolás Cortés Alfaro', '208263560', 'nicolas.15@live.cl', '$2y$10$2r9GZwb3XKr6P3N/zOIduurOQOVMejRy074DeC.5bhC66lo/bjbuW', 1, 1, '2026-06-23 15:37:46', NULL, NULL);

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `categorias`
-- --------------------------------------------------------
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(3, 'alimento', 'para comer :))', '2026-06-23 15:41:20'),
(4, 'agua', 'asdasda', '2026-06-23 16:30:56');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `productos`
-- --------------------------------------------------------
CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 5,
  `categoria_id` int(11) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `stock_minimo`, `categoria_id`, `fecha_vencimiento`, `fecha_creacion`) VALUES
(2, '123123', 'Semens', 'semen', 123.00, 121, 5, NULL, NULL, '2026-06-17 20:32:17'),
(4, '314112', 'Caquita', 'Caquilla', 12.00, 5, 5, 3, '2026-06-28', '2026-06-17 21:08:07'),
(7, '123asdd', 'asdsad', 'asda', 1.00, 0, 5, NULL, '2026-06-18', '2026-06-18 04:26:21'),
(8, '313213131513214562', 'cafe', 'cafeee', 10.00, 39, 5, 3, '2027-04-15', '2026-06-23 15:40:44'),
(9, '654651651616551', 'agua', 'aa', 1551.00, 93, 5, 3, '2026-06-28', '2026-06-23 16:20:52');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `clientes`
-- --------------------------------------------------------
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `deuda` decimal(10,2) DEFAULT 0.00,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `clientes` (`id`, `nombre`, `rut`, `telefono`, `deuda`, `fecha_registro`) VALUES
(1, 'Nicolas', '209815354', '9498723', 3104.00, '2026-06-22 21:03:10');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `pagos_fiados`
-- --------------------------------------------------------
CREATE TABLE `pagos_fiados` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pagos_fiados` (`id`, `cliente_id`, `monto`, `fecha`) VALUES
(1, 1, 5.00, '2026-06-22 21:39:53'),
(2, 1, 12.00, '2026-06-22 21:52:19'),
(3, 1, 12.00, '2026-06-22 21:52:22'),
(4, 1, 12.00, '2026-06-22 21:52:25'),
(5, 1, 6.00, '2026-06-22 21:57:11'),
(6, 1, 5.00, '2026-06-22 21:59:47'),
(7, 1, 4.00, '2026-06-22 22:04:16'),
(8, 1, 2.00, '2026-06-22 22:04:24'),
(9, 1, 3.00, '2026-06-22 22:37:37'),
(10, 1, 1.00, '2026-06-23 12:36:12'),
(11, 1, 1551.00, '2026-06-23 12:38:25');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `ventas`
-- --------------------------------------------------------
CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ventas` (`id`, `cliente_id`, `metodo_pago`, `total`, `fecha`) VALUES
(1, NULL, 'Efectivo', 1.00, '2026-06-22 20:53:36'),
(2, NULL, 'Efectivo', 1.00, '2026-06-22 20:53:39'),
(3, NULL, 'Efectivo', 12.00, '2026-06-22 20:53:55'),
(4, 1, 'Fiado', 12.00, '2026-06-22 21:03:24'),
(5, 1, 'Fiado', 12.00, '2026-06-22 21:06:29'),
(6, 1, 'Fiado', 12.00, '2026-06-22 21:06:56'),
(7, 1, 'Fiado', 123.00, '2026-06-22 21:07:22'),
(8, 1, 'Fiado', 123.00, '2026-06-22 21:10:11'),
(9, 1, 'Fiado', 12.00, '2026-06-22 21:10:19'),
(10, 1, 'Fiado', 12.00, '2026-06-22 21:12:34'),
(11, 1, 'Fiado', 12.00, '2026-06-22 21:12:37'),
(12, 1, 'Fiado', 12.00, '2026-06-22 21:12:46'),
(13, 1, 'Fiado', 12.00, '2026-06-22 21:13:02'),
(14, 1, 'Fiado', 12.00, '2026-06-22 21:34:38'),
(15, 1, 'Fiado', 12.00, '2026-06-22 21:39:53'),
(16, NULL, 'Efectivo', 1.00, '2026-06-22 21:51:36'),
(17, NULL, 'Efectivo', 12.00, '2026-06-22 21:51:53'),
(18, 1, 'Fiado', 12.00, '2026-06-22 21:57:11'),
(19, 1, 'Fiado', 12.00, '2026-06-22 21:59:47'),
(20, 1, 'Fiado', 12.00, '2026-06-22 22:04:16'),
(21, NULL, 'Efectivo', 10.00, '2026-06-23 11:43:05'),
(22, NULL, 'Efectivo', 3102.00, '2026-06-23 12:27:58'),
(23, NULL, 'Débito', 1551.00, '2026-06-23 12:37:04'),
(24, NULL, 'Débito', 1551.00, '2026-06-23 12:37:25'),
(25, 1, 'Fiado', 1551.00, '2026-06-23 12:38:02'),
(26, 1, 'Fiado', 1551.00, '2026-06-23 12:38:25'),
(27, 1, 'Fiado', 1551.00, '2026-06-23 12:39:20');

-- --------------------------------------------------------
-- ESTRUCTURA Y VOLCADO DE LA TABLA `detalle_ventas`
-- --------------------------------------------------------
CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 7, 1, 1.00, 1.00),
(2, 2, 7, 1, 1.00, 1.00),
(3, 3, 4, 1, 12.00, 12.00),
(4, 4, 4, 1, 12.00, 12.00),
(5, 5, 4, 1, 12.00, 12.00),
(6, 6, 4, 1, 12.00, 12.00),
(7, 7, 2, 1, 123.00, 123.00),
(8, 8, 2, 1, 123.00, 123.00),
(9, 9, 4, 1, 12.00, 12.00),
(10, 10, 4, 1, 12.00, 12.00),
(11, 11, 4, 1, 12.00, 12.00),
(12, 12, 4, 1, 12.00, 12.00),
(13, 13, 4, 1, 12.00, 12.00),
(14, 14, 4, 1, 12.00, 12.00),
(15, 15, 4, 1, 12.00, 12.00),
(16, 16, 7, 1, 1.00, 1.00),
(17, 17, 4, 1, 12.00, 12.00),
(18, 18, 4, 1, 12.00, 12.00),
(19, 19, 4, 1, 12.00, 12.00),
(20, 20, 4, 1, 12.00, 12.00),
(21, 21, 8, 1, 10.00, 10.00),
(22, 22, 9, 2, 1551.00, 3102.00),
(23, 23, 9, 1, 1551.00, 1551.00),
(24, 24, 9, 1, 1551.00, 1551.00),
(25, 25, 9, 1, 1551.00, 1551.00),
(26, 26, 9, 1, 1551.00, 1551.00),
(27, 27, 9, 1, 1551.00, 1551.00);

-- --------------------------------------------------------
-- ÍNDICES Y LLAVES PRIMARIAS
-- --------------------------------------------------------
ALTER TABLE `roles` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `nombre` (`nombre`);
ALTER TABLE `registro` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `rut` (`rut`), ADD UNIQUE KEY `correo` (`correo`), ADD KEY `idx_registro_rol_id` (`rol_id`);
ALTER TABLE `categorias` ADD PRIMARY KEY (`id`);
ALTER TABLE `productos` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `codigo` (`codigo`), ADD KEY `categoria_id` (`categoria_id`);
ALTER TABLE `clientes` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `rut` (`rut`);
ALTER TABLE `pagos_fiados` ADD PRIMARY KEY (`id`), ADD KEY `cliente_id` (`cliente_id`);
ALTER TABLE `ventas` ADD PRIMARY KEY (`id`), ADD KEY `cliente_id` (`cliente_id`);
ALTER TABLE `detalle_ventas` ADD PRIMARY KEY (`id`), ADD KEY `venta_id` (`venta_id`), ADD KEY `producto_id` (`producto_id`);

-- --------------------------------------------------------
-- AUTO_INCREMENTs
-- --------------------------------------------------------
ALTER TABLE `roles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `registro` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
ALTER TABLE `clientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `pagos_fiados` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `ventas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
ALTER TABLE `detalle_ventas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

-- --------------------------------------------------------
-- RESTRICCIONES (LLAVES FORÁNEAS)
-- --------------------------------------------------------
ALTER TABLE `registro` ADD CONSTRAINT `registro_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
ALTER TABLE `productos` ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;
ALTER TABLE `pagos_fiados` ADD CONSTRAINT `pagos_fiados_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);
ALTER TABLE `ventas` ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;
ALTER TABLE `detalle_ventas` ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

-- Reactivamos las restricciones en la base de datos
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
