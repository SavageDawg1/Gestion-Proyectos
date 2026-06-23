-- --------------------------------------------------------
-- CONFIGURACIONES INICIALES
-- --------------------------------------------------------
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Desactivamos la revisión de llaves foráneas para evitar errores de restricción al borrar/crear
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- LIMPIEZA DE TABLAS EXISTENTES
-- --------------------------------------------------------
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `registro`;
DROP TABLE IF EXISTS `categorias`;
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
-- ESTRUCTURA Y VOLCADO DE LA TABLA `categorias`
-- --------------------------------------------------------
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Abarrotes', 'Productos básicos no perecederos'),
(2, 'Bebestibles', 'Bebidas, jugos y aguas');

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
  `categoria_id` int(11) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `productos` (`id`, `codigo`, `nombre`, `descripcion`, `precio`, `stock`, `categoria_id`, `fecha_vencimiento`) VALUES
(1, '7801234567890', 'Arroz Grado 1 1kg', 'Arroz largo ancho', 1490.00, 50, 1, NULL),
(2, '7809876543210', 'Bebida Cola 3L', 'Botella desechable', 2700.00, 24, 2, '2027-01-15');

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

INSERT INTO `registro` (`id`, `nombre_apellido`, `rut`, `correo`, `contrasena`, `rol_id`, `activo`) VALUES
(1, 'Administrador Almacen', '12.345.678-9', 'admin@almacen.com', '$2y$10$zWzqv9zW9R.cmPk672yBie5TPZIaSJeSwETrnAl.1IFh1znw1Rjwm', 1, 1);

-- --------------------------------------------------------
-- DEFINICIÓN DE ÍNDICES Y CLAVES PRIMARIAS
-- --------------------------------------------------------
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `categoria_id` (`categoria_id`);

ALTER TABLE `registro`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rut` (`rut`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_registro_rol_id` (`rol_id`);

-- --------------------------------------------------------
-- CONFIGURACIÓN DE AUTO_INCREMENTs
-- --------------------------------------------------------
ALTER TABLE `roles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `registro` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------
-- CONFIGURACIÓN DE LLAVES FORÁNEAS (RESTRICCIONES)
-- --------------------------------------------------------
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;

ALTER TABLE `registro`
  ADD CONSTRAINT `registro_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

-- Reactivamos las restricciones de llaves foráneas para que la base de datos vuelva a estar protegida
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;