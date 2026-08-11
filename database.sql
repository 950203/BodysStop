-- phpMyAdmin SQL Dump
-- Base de datos: `bodystop`
-- E-commerce BodyStop con autenticación por roles, catálogo, tallas/stock y pedidos

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

USE `bodystop`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `marca` varchar(120) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_plano` varchar(255) DEFAULT NULL,
  `rol` enum('usuario','vendedor','administrador') NOT NULL DEFAULT 'usuario',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  UNIQUE KEY `uq_usuarios_cedula` (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
-- Contraseñas seed: Admin123! (admin) / Vendedor123! (vendedores)
--

INSERT INTO `usuarios` (`nombre`, `marca`, `email`, `password_hash`, `password_plano`, `rol`) VALUES
('Administrador', NULL, 'admin@BodyStop.com', '$2y$10$2UOLBwBrZlfz6nYyeSMKQ.Sx2tSz4Tab3c6LgS6EdsvpYhaehJ1jS', 'Admin123!', 'administrador'),
('Vendedor Demo', 'BodyStop', 'vendedor@BodyStop.com', '$2y$10$Jqeqf2FcpgDqtR5DXsJXHe6cvu8yx7US.6brqKcDghArLt4n4ojwm', 'Vendedor123!', 'vendedor'),
('Laura Gomez', 'Cuerpo Fino', 'laura.gomez@bodystop.com', '$2y$10$Jqeqf2FcpgDqtR5DXsJXHe6cvu8yx7US.6brqKcDghArLt4n4ojwm', 'Vendedor123!', 'vendedor'),
('Mariana Ruiz', 'BodyTrend', 'mariana.ruiz@bodystop.com', '$2y$10$Jqeqf2FcpgDqtR5DXsJXHe6cvu8yx7US.6brqKcDghArLt4n4ojwm', 'Vendedor123!', 'vendedor'),
('Camila Torres', 'FitMotion', 'camila.torres@bodystop.com', '$2y$10$Jqeqf2FcpgDqtR5DXsJXHe6cvu8yx7US.6brqKcDghArLt4n4ojwm', 'Vendedor123!', 'vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auth_tokens`
-- Tokens para llamadas CRUD (se guardan solo hasheados con SHA-256)
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `tipo` enum('api','session') NOT NULL DEFAULT 'api',
  `expira_en` datetime DEFAULT NULL,
  `revocado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_auth_tokens_hash` (`token_hash`),
  KEY `fk_auth_tokens_usuario` (`usuario_id`),
  CONSTRAINT `fk_auth_tokens_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_intentos`
-- Control de intentos fallidos y bloqueo temporal (anti fuerza bruta)
--

CREATE TABLE `login_intentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(160) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `intentos` int(11) NOT NULL DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_login_email_ip` (`email`,`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
-- Tokens para recuperación de contraseña
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reset_token_hash` (`token_hash`),
  KEY `fk_reset_usuario` (`usuario_id`),
  CONSTRAINT `fk_reset_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categorias_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`nombre`) VALUES
('Clásicos'),
('Encaje'),
('Satín'),
('Deportivos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `marca` varchar(120) DEFAULT NULL,
  `vendedor_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_productos_categoria` (`categoria_id`),
  KEY `fk_productos_vendedor` (`vendedor_id`),
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productos_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `material`, `precio`, `imagen`, `categoria_id`, `marca`, `vendedor_id`, `activo`, `created_at`) VALUES
(1, 'Body Encaje Negro', 'Body de encaje delicado con diseño moderno.', 'Encaje elástico', 75000.00, 'https://media.gotrendier.com.co/media/p/2023/05/12/n_75dec24e-f132-11ed-a5d4-0aab023478bd.jpeg', 2, NULL, NULL, 1, '2026-02-06 21:40:40'),
(2, 'Body Satinado Rojo', 'Body satinado elegante, ideal para ocasiones especiales.', 'Satín brillante', 80000.00, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSbklQqXCjygT9CABggyWO5SIoodxoeEijKUQ&s', 3, NULL, NULL, 1, '2026-02-06 21:41:11'),
(3, 'Body Básico Blanco', 'Body clásico de algodón, cómodo para uso diario.', 'Algodón suave', 65000.00, 'https://http2.mlstatic.com/D_NQ_NP_2X_815923-MLC85115901822_052025-T.webp', 1, NULL, NULL, 1, '2026-02-06 21:41:33'),
(4, 'Body Deportivo Negro', 'Body deportivo de lycra con soporte, ideal para entrenar.', 'Lycra transpirable', 72000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_otsxlhc3w3ol6a.6870e0e0f03000.jpeg', 4, NULL, NULL, 1, '2026-08-09 10:00:00'),
(5, 'Body Control Negro', 'Body moldeador con control de abdomen, efecto segunda piel.', 'Microfibra con control', 78000.00, 'https://media.gotrendier.com.co/media/p/2026/08/02/i_ggruu3by7vzglb.10cccce068687030.jpeg', 1, NULL, NULL, 1, '2026-08-09 10:00:00'),
(6, 'Body Encaje Blanco', 'Body blanco de encaje delicado y elegante, efecto luminoso.', 'Encaje suave', 68000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_mrybdk5fb8eviq.173321130f170f0f.jpeg', 2, NULL, NULL, 1, '2026-08-09 10:00:00'),
(7, 'Body Acanalado Negro', 'Body acanalado de algodon elastico, comodo y versatil para el dia a dia.', 'Algodon acanalado', 62000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_k3qx5jc6snym5c.b78df8f9f9f1e3e7.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(8, 'Body Negro Clasico', 'Body basico negro con escote comodo, imprescindible en tu armario.', 'Microfibra suave', 59000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_e1vkzchh9cii1q.e3ece4e1f1f1f4f6.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(9, 'Body Blanco Manga Larga', 'Body blanco de manga larga, elegante y perfecto para cualquier temporada.', 'Algodon con lycra', 68000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_g0tu0zr4w6kp95.f070682830f4d28.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(10, 'Body Blanco Algodon', 'Body blanco clasico de algodon, suave y transpirable para uso diario.', 'Algodon suave', 56000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_kl0l3no6a7rttl.a933869e568ee8c8.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(11, 'Body Gris Perla', 'Body en tono gris perla con acabado liso y estilo minimalista.', 'Punto elastico', 65000.00, 'https://media.gotrendier.com.co/media/p/2026/08/05/i_z65yg4guzgequ0.eceed9cecac3e3d1.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(12, 'Body Animal Print Cebra', 'Body con estampado de cebra, atrevido y muy moderno.', 'Lycra estampada', 72000.00, 'https://media.gotrendier.com.co/media/p/2026/08/03/i_ebzbz48dvg4zuf.61c7ae48491829e.jpeg', 1, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(13, 'Body Satin Plateado', 'Body satinado plateado con brillo sutil, ideal para ocasiones especiales.', 'Satin brillante', 82000.00, 'https://media.gotrendier.com.co/media/p/2025/11/17/i_b3zdioqsbv40sp.cdcce8e879f13225.jpeg', 3, 'Cuerpo Fino', 3, 1, '2026-08-10 09:00:00'),
(14, 'Body Encaje Natural', 'Body de encaje en tono natural con varilla, delicado y sensual.', 'Encaje elastico', 85000.00, 'https://media.gotrendier.com.co/media/p/2026/04/02/i_a2p5upimx7sax9.444c686870703010.jpeg', 2, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(15, 'Body Encaje Blanco Elegante', 'Body de encaje blanco con detalles elegantes en el escote.', 'Encaje suave', 88000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_98hf8ulclg1twz.712b370f1f3f1b1f.jpeg', 2, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(16, 'Body Lenceria Blanco', 'Body de lenceria blanco con diseno romantico y femenino.', 'Encaje y lycra', 79000.00, 'https://media.gotrendier.com.co/media/p/2025/10/27/i_p6nsrb08hajc7x.73332b2727454b8e.jpeg', 2, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(17, 'Body Control Abdomen 360', 'Body moldeador con control de abdomen 360, efecto segunda piel.', 'Microfibra con control', 95000.00, 'https://media.gotrendier.com.co/media/p/2026/05/10/i_b2lwbwpqvvufl3.b48cc9c0e8e8e071.jpeg', 1, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(18, 'Body Faja Moldeador', 'Body faja que estiliza y moldea la silueta con sujecion media.', 'Powernet elastico', 89000.00, 'https://media.gotrendier.com.co/media/p/2026/07/24/i_sihwl948fb12t6.2b68706868d0a8d9.jpeg', 1, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(19, 'Body Blanco Clasico', 'Body blanco basico y suave, un imprescindible del armario.', 'Algodon suave', 58000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_inh6xkeijh4s3i.f52727078e8e5ecd.jpeg', 1, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(20, 'Body Beige Casual', 'Body en tono beige con corte comodo, ideal para combinar con todo.', 'Punto suave', 67000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_zmcoynodz1tom6.cce8cce8c8e87032.jpeg', 1, 'BodyTrend', 4, 1, '2026-08-10 09:00:00'),
(21, 'Body Deportivo Beige Nude', 'Body deportivo en tono nude con soporte, ideal para entrenar.', 'Lycra transpirable', 75000.00, 'https://media.gotrendier.com.co/media/p/2026/07/30/i_zuihhn5xgmfbh0.dcccccceaa96ae12.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(22, 'Body Deportivo Gris', 'Body deportivo gris con compresion y secado rapido.', 'Lycra compresiva', 72000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_xgy01br41ythz1.e8dc94ccc4c0f030.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(23, 'Body Deportivo Carbon', 'Body deportivo color carbon con espalda cruzada, maximo rendimiento.', 'Tejido tecnico', 70000.00, 'https://media.gotrendier.com.co/media/p/2026/08/09/i_5ho0po43i5oir7.d7d7e3f9f9f1b696.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(24, 'Body Deportivo Negro', 'Body deportivo negro con espalda destapada y gran libertad de movimiento.', 'Lycra elastica', 73000.00, 'https://media.gotrendier.com.co/media/p/2026/08/06/i_pomlsym85qtlco.6179e9e16060f1f.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(25, 'Body Deportivo Arena', 'Body deportivo tono arena, ligero y flexible para tu rutina.', 'Algodon con lycra', 69000.00, 'https://media.gotrendier.com.co/media/p/2023/05/10/i_cd8f3f62-efa4-11ed-8187-024081bb09e3.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(26, 'Body Deportivo Moldeador', 'Body deportivo moldeador que brinda soporte y realza la figura.', 'Powerflex', 77000.00, 'https://media.gotrendier.com.co/media/p/2026/08/01/i_b6l0jqnqft9i71.81f16330f0f0f2e.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00'),
(27, 'Body Deportivo Rib Pack x2', 'Pack de dos bodys deportivos acanalados en colores neutros.', 'Rib elastico', 98000.00, 'https://media.gotrendier.com.co/media/p/2026/08/08/i_gjjmf3xd4wpdxk.d8ecb888a8e2e3a2.jpeg', 4, 'FitMotion', 5, 1, '2026-08-10 09:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tallas`
-- Stock por talla de cada producto
--

CREATE TABLE `producto_tallas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_producto_talla` (`producto_id`,`talla`),
  CONSTRAINT `fk_pt_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_tallas`
--

INSERT INTO `producto_tallas` (`producto_id`, `talla`, `stock`) VALUES
(1, 'S', 12), (1, 'M', 15), (1, 'L', 13), (1, 'XL', 10),
(2, 'S', 10), (2, 'M', 12), (2, 'L', 8),  (2, 'XL', 5),
(3, 'S', 20), (3, 'M', 25), (3, 'L', 18), (3, 'XL', 12),
(4, 'S', 14), (4, 'M', 16), (4, 'L', 12), (4, 'XL', 8),
(5, 'S', 10), (5, 'M', 14), (5, 'L', 11), (5, 'XL', 6),
(6, 'S', 12), (6, 'M', 15), (6, 'L', 13), (6, 'XL', 9),
(7, 'S', 15), (7, 'M', 18), (7, 'L', 12), (7, 'XL', 8),
(8, 'S', 15), (8, 'M', 18), (8, 'L', 12), (8, 'XL', 8),
(9, 'S', 15), (9, 'M', 18), (9, 'L', 12), (9, 'XL', 8),
(10, 'S', 15), (10, 'M', 18), (10, 'L', 12), (10, 'XL', 8),
(11, 'S', 15), (11, 'M', 18), (11, 'L', 12), (11, 'XL', 8),
(12, 'S', 15), (12, 'M', 18), (12, 'L', 12), (12, 'XL', 8),
(13, 'S', 15), (13, 'M', 18), (13, 'L', 12), (13, 'XL', 8),
(14, 'S', 15), (14, 'M', 18), (14, 'L', 12), (14, 'XL', 8),
(15, 'S', 15), (15, 'M', 18), (15, 'L', 12), (15, 'XL', 8),
(16, 'S', 15), (16, 'M', 18), (16, 'L', 12), (16, 'XL', 8),
(17, 'S', 15), (17, 'M', 18), (17, 'L', 12), (17, 'XL', 8),
(18, 'S', 15), (18, 'M', 18), (18, 'L', 12), (18, 'XL', 8),
(19, 'S', 15), (19, 'M', 18), (19, 'L', 12), (19, 'XL', 8),
(20, 'S', 15), (20, 'M', 18), (20, 'L', 12), (20, 'XL', 8),
(21, 'S', 15), (21, 'M', 18), (21, 'L', 12), (21, 'XL', 8),
(22, 'S', 15), (22, 'M', 18), (22, 'L', 12), (22, 'XL', 8),
(23, 'S', 15), (23, 'M', 18), (23, 'L', 12), (23, 'XL', 8),
(24, 'S', 15), (24, 'M', 18), (24, 'L', 12), (24, 'XL', 8),
(25, 'S', 15), (25, 'M', 18), (25, 'L', 12), (25, 'XL', 8),
(26, 'S', 15), (26, 'M', 18), (26, 'L', 12), (26, 'XL', 8),
(27, 'S', 15), (27, 'M', 18), (27, 'L', 12), (27, 'XL', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
-- Carrito persistente del cliente (sobrevive al cierre de sesión)
--

CREATE TABLE `carrito_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_carrito_item` (`usuario_id`,`producto_id`,`talla`),
  KEY `fk_carrito_usuario` (`usuario_id`),
  KEY `fk_carrito_producto` (`producto_id`),
  CONSTRAINT `fk_carrito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_carrito_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre_cliente` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `fecha_estimada_entrega` datetime DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `estado` enum('pendiente','pagado','en_camino','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_pedidos_usuario` (`usuario_id`),
  CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento_pedidos`
-- Eventos del rastreo de envíos visibles para el cliente
--

CREATE TABLE `seguimiento_pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `ubicacion` varchar(160) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_seg_pedido` (`pedido_id`),
  CONSTRAINT `fk_seg_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `talla` varchar(10) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `pedido_detalle_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resenas`
-- Valoraciones y comentarios de clientes sobre productos
--

CREATE TABLE `resenas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(120) NOT NULL,
  `calificacion` tinyint(1) NOT NULL DEFAULT 5,
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_resenas_producto` (`producto_id`),
  KEY `fk_resenas_usuario` (`usuario_id`),
  CONSTRAINT `fk_resenas_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resenas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resenas`
--

INSERT INTO `resenas` (`producto_id`, `usuario_id`, `nombre`, `calificacion`, `comentario`) VALUES
(1, 1, 'Administrador', 5, 'El encaje es delicado y muy cómodo, la talla queda perfecta.'),
(1, 2, 'Vendedor Demo', 4, 'Buen material, me pareció un poco ajustado, pediría una talla más.'),
(2, 1, 'Administrador', 5, 'El satín se ve elegante, ideal para ocasiones especiales.');

COMMIT;
