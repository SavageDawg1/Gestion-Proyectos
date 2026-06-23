-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-06-2026 a las 01:31:38
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `fecha_creacion`) VALUES
(2, 'Lacteo', 'Leche, etcas', '2026-06-23 01:49:51'),
(3, 'Enlatados', 'Conservas, atunes, etc', '2026-06-23 22:03:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `rut` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `deuda` decimal(10,2) DEFAULT 0.00,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `rut`, `telefono`, `deuda`, `fecha_registro`) VALUES
(1, 'Nicolas', '209815354', '9498723', 4.00, '2026-06-22 21:03:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

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
(21, 21, 4, 1, 12.00, 12.00),
(22, 22, 4, 1, 12.00, 12.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_fiados`
--

CREATE TABLE `pagos_fiados` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_fiados`
--

INSERT INTO `pagos_fiados` (`id`, `cliente_id`, `monto`, `fecha`) VALUES
(1, 1, 5.00, '2026-06-22 21:39:53'),
(2, 1, 12.00, '2026-06-22 21:52:19'),
(3, 1, 12.00, '2026-06-22 21:52:22'),
(4, 1, 12.00, '2026-06-22 21:52:25'),
(5, 1, 6.00, '2026-06-22 21:57:11'),
(6, 1, 5.00, '2026-06-22 21:59:47'),
(7, 1, 4.00, '2026-06-22 22:04:16'),
(8, 1, 2.00, '2026-06-22 22:04:24'),
(9, 1, 2.00, '2026-06-23 16:37:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `stock_minimo`, `categoria_id`, `fecha_vencimiento`, `fecha_creacion`) VALUES
(2, '123123', 'Semens', 'semen', 123.00, 121, 5, NULL, NULL, '2026-06-17 20:32:17'),
(4, '314112', 'Caquita', 'Caquilla', 12.00, 3, 5, NULL, NULL, '2026-06-17 21:08:07'),
(7, '123asdd', 'asdsad', 'asd', 1.00, 3, 6, NULL, '2026-06-18', '2026-06-18 04:26:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro`
--

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

--
-- Volcado de datos para la tabla `registro`
--

INSERT INTO `registro` (`id`, `nombre_apellido`, `rut`, `correo`, `contrasena`, `rol_id`, `activo`, `creado_en`, `reset_token`, `token_expiracion`) VALUES
(1, 'Usuario de Prueba', '12.345.678-9', 'prueba@correo.com', 'clave123', 1, 1, '2026-06-10 23:27:49', NULL, NULL),
(2, 'Gaspar', '209815354', 'gaspar.ar.03@gmail.com', '$2y$10$zWzqv9zW9R.cmPk672yBie5TPZIaSJeSwETrnAl.1IFh1znw1Rjwm', 1, 1, '2026-06-10 23:28:39', '988b417d46b1237f25756047e19b5f5d472df8430d7e4e63cf1563535a93702e', '2026-06-11 02:49:54'),
(3, 'Nicolás Cortés Alfaro', '208263560', 'nicolas.15@live.cl', '$2y$10$JBgPOc2GctGdQ.ku5Ze0/uGT32iKvz7D2ywe2jI3XHm4uAldmHnVC', 1, 1, '2026-06-23 23:29:40', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

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
(21, NULL, 'Débito', 12.00, '2026-06-23 16:51:47'),
(22, NULL, 'Efectivo', 12.00, '2026-06-23 16:52:00');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rut` (`rut`);

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `pagos_fiados`
--
ALTER TABLE `pagos_fiados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `registro`
--
ALTER TABLE `registro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rut` (`rut`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_registro_rol_id` (`rol_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `pagos_fiados`
--
ALTER TABLE `pagos_fiados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `registro`
--
ALTER TABLE `registro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pagos_fiados`
--
ALTER TABLE `pagos_fiados`
  ADD CONSTRAINT `pagos_fiados_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `registro`
--
ALTER TABLE `registro`
  ADD CONSTRAINT `registro_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
