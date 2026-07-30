-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-07-2026 a las 00:08:36
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
-- Base de datos: `bodyshop`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`, `activo`, `created_at`) VALUES
(1, 'Body Encaje Negro', NULL, 75000.00, 50, 'https://media.gotrendier.com.co/media/p/2023/05/12/n_75dec24e-f132-11ed-a5d4-0aab023478bd.jpeg', 1, '2026-02-06 21:40:40'),
(2, 'Body Satinado Rojo', NULL, 80000.00, 50, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSbklQqXCjygT9CABggyWO5SIoodxoeEijKUQ&s', 1, '2026-02-06 21:41:11'),
(3, 'Body Básico Blanco', NULL, 65000.00, 50, 'https://http2.mlstatic.com/D_NQ_NP_2X_815923-MLC85115901822_052025-T.webp', 1, '2026-02-06 21:41:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_1`
--

CREATE TABLE `productos_1` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos_1`
--

INSERT INTO `productos_1` (`id`, `nombre`, `precio`, `imagen`) VALUES
(1, 'Body Encaje Negro', 75000.00, 'https://media.gotrendier.com.co/media/p/2023/05/12/n_75dec24e-f132-11ed-a5d4-0aab023478bd.jpeg'),
(2, 'Body Satinado Rojo', 82000.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSbklQqXCjygT9CABggyWO5SIoodxoeEijKUQ&s'),
(3, 'Body Básico Blanco', 65000.00, 'https://http2.mlstatic.com/D_NQ_NP_2X_815923-MLC85115901822_052025-T.webp');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_1`
--
ALTER TABLE `productos_1`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `productos_1`
--
ALTER TABLE `productos_1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD CONSTRAINT `pedido_detalle_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
