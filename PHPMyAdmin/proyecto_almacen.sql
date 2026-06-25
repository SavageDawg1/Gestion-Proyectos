-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2026 a las 04:57:30
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
(3, 'Enlatados', 'Conservas, atunes, etc', '2026-06-23 22:03:32'),
(6, 'Fiambre', 'jamón, salame, etc.', '2026-06-24 16:05:34'),
(7, 'asdasd', 'asdad', '2026-06-24 16:06:08'),
(9, 'asdasdas', '', '2026-06-24 16:06:59'),
(10, 'carniceria', 'carneee', '2026-06-25 00:55:28');

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
(2, 'Nico el Deudor', '2011321321313', '', 0.00, '2026-06-24 12:52:45'),
(3, 'Ali Devuelve La Plata', '20.222.333-0', '9899455454545544', 0.00, '2026-06-24 12:54:35'),
(4, 'Johnny Test', '20.564.563-3', '+56998908844', 0.00, '2026-06-24 19:16:49'),
(5, 'ClienteDeudor', '16.456.161-6', '+56165432132', 0.00, '2026-06-24 19:20:02');

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
(22, 22, 4, 1, 12.00, 12.00),
(23, 23, 4, 1, 12.00, 12.00),
(24, 24, 4, 1, 12.00, 12.00),
(29, 27, 13, 1, 1000.00, 1000.00),
(30, 28, 13, 3, 1000.00, 3000.00),
(31, 28, 12, 1, 1000.00, 1000.00),
(32, 29, 13, 10, 1000.00, 10000.00),
(33, 29, 12, 4, 1000.00, 4000.00),
(34, 30, 13, 3, 1000.00, 3000.00),
(35, 30, 12, 5, 1000.00, 5000.00),
(36, 31, 13, 1, 1000.00, 1000.00),
(37, 32, 15, 1, 10.00, 10.00),
(38, 33, 13, 1, 1000.00, 1000.00),
(39, 34, 13, 1, 1000.00, 1000.00),
(40, 35, 13, 1, 1000.00, 1000.00),
(41, 36, 13, 1, 1000.00, 1000.00),
(42, 37, 13, 1, 1000.00, 1000.00);

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
(13, 3, 4000.00, '2026-06-24 12:59:24'),
(14, 2, 500.00, '2026-06-24 13:09:13'),
(15, 2, 500.00, '2026-06-24 13:09:37'),
(16, 3, 4000.00, '2026-06-24 13:09:45'),
(17, 2, 800.00, '2026-06-24 20:22:19'),
(18, 2, 100.00, '2026-06-24 20:22:55'),
(19, 2, 100.00, '2026-06-24 20:23:03');

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
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `stock_minimo`, `categoria_id`, `fecha_vencimiento`, `fecha_creacion`, `estado`) VALUES
(2, '123123', 'Semen', 'a', 1000.00, 1, 5, NULL, NULL, '2026-06-17 20:32:17', 'activo'),
(4, '314112', 'Caquita', 'Caquilla', 12.00, 0, 5, NULL, NULL, '2026-06-17 21:08:07', 'activo'),
(7, '123asdd', 'asdsad', 'asd', 1.00, 2, 6, NULL, '2026-06-18', '2026-06-18 04:26:21', 'activo'),
(12, '123456789', 'Fideos', 'a', 1000.00, 1987, 5, NULL, NULL, '2026-06-24 15:53:18', 'activo'),
(13, '1321355', 'agua', 'hola', 1000.00, 122, 1, NULL, '2026-06-25', '2026-06-24 15:56:11', 'activo'),
(15, 'asdxdsxz', 'confort 4u', 'asdas', 10.00, 0, 5, NULL, NULL, '2026-06-24 16:10:02', 'activo'),
(16, '1321343421325432', 'algo', '', 1500.00, 24, 5, NULL, NULL, '2026-06-24 23:53:00', 'activo');

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
(2, 'Gaspar', '209815354', 'gaspar.ar.03@gmail.com', '$2y$10$zWzqv9zW9R.cmPk672yBie5TPZIaSJeSwETrnAl.1IFh1znw1Rjwm', 1, 1, '2026-06-10 23:28:39', '988b417d46b1237f25756047e19b5f5d472df8430d7e4e63cf1563535a93702e', '2026-06-11 02:49:54'),
(3, 'Nicolás Cortés Alfaro', '208263560', 'nicolas.15@live.cl', '$2y$10$JBgPOc2GctGdQ.ku5Ze0/uGT32iKvz7D2ywe2jI3XHm4uAldmHnVC', 1, 1, '2026-06-23 23:29:40', NULL, NULL),
(4, 'Alison Oro', '204105553', 'oroalison3@gmail.com', '$2y$10$Dc7ioOrjsycvb6RaYc2ZieqXzVzYUBJS2F4HMUCgsDUA76ZXIrEdW', 1, 1, '2026-06-24 00:27:11', NULL, NULL),
(6, 'test', '165461616', 'test@test.com', '$2y$10$HZwe7cK2deu8NX1wvDYvx.O5QiyuxZyZtBEGH4S1s9EP2bx6vY2GO', 2, 1, '2026-06-24 17:17:55', NULL, NULL),
(7, 'usuario test jaksd', '208263554', 'correo@correo.com', '$2y$10$lH/UK0rbjR3hSyAr//0sLu1ATYjXfcnquaZUENtoqD/093XlIRuvm', 2, 1, '2026-06-25 00:54:17', NULL, NULL),
(11, 'Juanito Gasass', '256464555', 'admin@admin.com', '$2y$10$PtloRZ1B1eEEaIyGFho2eOGlnKBZA2K/1gBHY6n3UQvc4qUefUkRS', 2, 1, '2026-06-25 02:36:46', NULL, NULL),
(15, 'Juanito Akajhakd', '123213213', 'correo@correooo.com', '$2y$10$zPtwxUjiI2J1/.q73oBeluWepn8bds1dFsPfXjdhzNC3EocZ6XddC', 2, 1, '2026-06-25 02:38:07', NULL, NULL);

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
(4, NULL, 'Fiado', 12.00, '2026-06-22 21:03:24'),
(5, NULL, 'Fiado', 12.00, '2026-06-22 21:06:29'),
(6, NULL, 'Fiado', 12.00, '2026-06-22 21:06:56'),
(7, NULL, 'Fiado', 123.00, '2026-06-22 21:07:22'),
(8, NULL, 'Fiado', 123.00, '2026-06-22 21:10:11'),
(9, NULL, 'Fiado', 12.00, '2026-06-22 21:10:19'),
(10, NULL, 'Fiado', 12.00, '2026-06-22 21:12:34'),
(11, NULL, 'Fiado', 12.00, '2026-06-22 21:12:37'),
(12, NULL, 'Fiado', 12.00, '2026-06-22 21:12:46'),
(13, NULL, 'Fiado', 12.00, '2026-06-22 21:13:02'),
(14, NULL, 'Fiado', 12.00, '2026-06-22 21:34:38'),
(15, NULL, 'Fiado', 12.00, '2026-06-22 21:39:53'),
(16, NULL, 'Efectivo', 1.00, '2026-06-22 21:51:36'),
(17, NULL, 'Efectivo', 12.00, '2026-06-22 21:51:53'),
(18, NULL, 'Fiado', 12.00, '2026-06-22 21:57:11'),
(19, NULL, 'Fiado', 12.00, '2026-06-22 21:59:47'),
(20, NULL, 'Fiado', 12.00, '2026-06-22 22:04:16'),
(21, NULL, 'Débito', 12.00, '2026-06-23 16:51:47'),
(22, NULL, 'Efectivo', 12.00, '2026-06-23 16:52:00'),
(23, NULL, 'Fiado', 12.00, '2026-06-23 20:27:58'),
(24, NULL, 'Fiado', 12.00, '2026-06-23 20:30:54'),
(27, NULL, 'Efectivo', 1000.00, '2026-06-24 12:38:57'),
(28, NULL, 'Debito', 4000.00, '2026-06-24 12:45:58'),
(29, NULL, 'Efectivo', 14000.00, '2026-06-24 12:46:27'),
(30, 3, 'Fiado', 8000.00, '2026-06-24 12:59:24'),
(31, NULL, 'Debito', 1000.00, '2026-06-24 13:03:30'),
(32, NULL, 'Debito', 10.00, '2026-06-24 13:04:32'),
(33, NULL, 'Debito', 1000.00, '2026-06-24 13:05:51'),
(34, 2, 'Fiado', 1000.00, '2026-06-24 13:09:13'),
(35, NULL, 'Efectivo', 1000.00, '2026-06-24 19:57:08'),
(36, NULL, 'Efectivo', 1000.00, '2026-06-24 20:05:59'),
(37, 2, 'Fiado', 1000.00, '2026-06-24 20:22:19');

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `pagos_fiados`
--
ALTER TABLE `pagos_fiados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `registro`
--
ALTER TABLE `registro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
