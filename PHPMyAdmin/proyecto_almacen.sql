-- phpMyAdmin SQL Dump
-- Base de datos: proyecto_almacen
-- Compatible con MariaDB/MySQL en XAMPP.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `proyecto_almacen`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `proyecto_almacen`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `registro`;
DROP TABLE IF EXISTS `roles`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Tabla: roles
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Vendedor');

-- --------------------------------------------------------
-- Tabla: registro
-- Usuarios del sistema. El login usa correo + contrasena.
-- --------------------------------------------------------

CREATE TABLE `registro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_apellido` varchar(150) NOT NULL,
  `rut` varchar(12) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiracion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_registro_rut` (`rut`),
  UNIQUE KEY `uk_registro_correo` (`correo`),
  KEY `idx_registro_rol_id` (`rol_id`),
  KEY `idx_registro_reset_token` (`reset_token`),
  CONSTRAINT `fk_registro_roles`
    FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuarios base preservados del respaldo anterior.
INSERT INTO `registro`
(`id`, `nombre_apellido`, `rut`, `correo`, `contrasena`, `rol_id`, `activo`, `creado_en`, `reset_token`, `token_expiracion`)
VALUES
(9, 'Nicolas', '208263560', 'nicolas.15@live.cl', '$2y$10$5KtnPGQPGYWXEkFhLaxIWubMmBMmvzULNULxIpDMYXTMfmwW.Fzr2', 1, 1, '2026-06-11 00:37:54', NULL, NULL),
(10, 'Alison Oro', '204105553', 'oroalison3@gmail.com', '$2y$10$T0tK30t0JH4nxHRoVRZd1uMXiWfOcBO5s5dmsYbUJrY3AbhWKtNU.', 2, 1, '2026-06-11 15:30:05', NULL, NULL),
(11, 'Gaspar', '213213243', 'gaspar.ar.03@gmail.com', '$2y$10$.71e2ByeD..7VBxn9w.zYOderAeZP9sSHNmEgGBhf7gd8.QFcAW/a', 2, 1, '2026-06-11 15:35:18', NULL, NULL),
(12, 'Carlos', '150284426', 'c.moller@nexlabs.cl', '$2y$10$q53g9UxAHhuuoC6sJeNErOhhQmxu74AfPg/rOJFk7hCgZNo13eCfm', 2, 1, '2026-06-11 16:04:14', NULL, NULL);

-- --------------------------------------------------------
-- Tabla: categorias
-- --------------------------------------------------------

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categorias_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, 'Abarrotes', 'Productos de despensa y consumo general.', current_timestamp(), current_timestamp()),
(2, 'Bebidas', 'Bebidas, jugos y líquidos envasados.', current_timestamp(), current_timestamp()),
(3, 'Lácteos', 'Leche, quesos, yogures y productos refrigerados.', current_timestamp(), current_timestamp()),
(4, 'Limpieza', 'Artículos de limpieza y aseo.', current_timestamp(), current_timestamp()),
(5, 'Otros', 'Productos sin categoría específica.', current_timestamp(), current_timestamp());

-- --------------------------------------------------------
-- Tabla: productos
-- --------------------------------------------------------

CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(80) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_productos_codigo` (`codigo`),
  KEY `idx_productos_categoria_id` (`categoria_id`),
  KEY `idx_productos_stock` (`stock`),
  KEY `idx_productos_fecha_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `fk_productos_categorias`
    FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `chk_productos_precio_no_negativo` CHECK (`precio` >= 0),
  CONSTRAINT `chk_productos_stock_no_negativo` CHECK (`stock` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos de ejemplo. Puedes borrarlos desde la app si no los necesitas.
INSERT INTO `productos`
(`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `categoria_id`, `fecha_vencimiento`, `creado_en`, `actualizado_en`)
VALUES
(1, 'AB-001', 'Arroz 1 kg', 'Arroz grano largo.', 1590.00, 20, 1, NULL, current_timestamp(), current_timestamp()),
(2, 'AB-002', 'Azúcar 1 kg', 'Azúcar granulada.', 1290.00, 4, 1, NULL, current_timestamp(), current_timestamp()),
(3, 'BE-001', 'Agua mineral 1.5 L', 'Agua mineral sin gas.', 990.00, 35, 2, NULL, current_timestamp(), current_timestamp()),
(4, 'LA-001', 'Leche entera 1 L', 'Leche entera en caja.', 1190.00, 8, 3, DATE_ADD(CURDATE(), INTERVAL 20 DAY), current_timestamp(), current_timestamp()),
(5, 'LI-001', 'Detergente 1 L', 'Detergente líquido multiuso.', 2990.00, 12, 4, NULL, current_timestamp(), current_timestamp());

ALTER TABLE `registro` AUTO_INCREMENT = 13;
ALTER TABLE `categorias` AUTO_INCREMENT = 6;
ALTER TABLE `productos` AUTO_INCREMENT = 6;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
