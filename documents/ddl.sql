-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql213.infinityfree.com
-- Tiempo de generación: 25-09-2025 a las 22:14:09
-- Versión del servidor: 11.4.7-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_40011443_easyapp`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `usuario`, `password_hash`, `nombre`, `created_at`) VALUES
(1, 'admin', '$2y$10$uNkjw6yCI4Mxh2g9QXx/COkiQN3FN3qlLpRz4FY0s6gcMRmHix3Qi', 'admin', '2025-09-23 20:18:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `preferencia_contacto` enum('email','telefono') DEFAULT 'email',
  `estado` enum('nuevo','contactado','baja') DEFAULT 'nuevo',
  `consentimiento` tinyint(1) DEFAULT 0,
  `baja_token` varchar(128) DEFAULT NULL,
  `update_token` varchar(128) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `contactos`
--

INSERT INTO `contactos` (`id`, `nombre`, `correo`, `telefono`, `preferencia_contacto`, `estado`, `consentimiento`, `baja_token`, `update_token`, `created_at`, `updated_at`) VALUES
(1, 'KT RPO', 'tesjjjt@test.comgr', '55446663', '', 'nuevo', 1, 'f926f5a29e2470066e576474bc2ab0bd0cb1ffd6467eb9c4fdcfa66b823840a2', 'a32a763f604d6a32f4037dfb6e568c951995154af89dfcbb251a5aab2bebfcb9', '2025-09-23 20:47:36', '2025-09-23 20:51:09'),
(2, 'pepito perez', 'pepito@q.com', '23455', '', 'nuevo', 1, '3d58b756fcc1c3b0629d50ae44083e0f760cc22e87337e8a45c387b163d139bd', '5397ee9045e4c27671856a40af0ad97a77500e0902ecb713b191f37f01f4031e', '2025-09-23 20:54:53', '2025-09-25 18:45:54'),
(3, 'juanita lopez', 'juanitalopez@gmail.com', '23456', '', 'nuevo', 1, 'c648a2faff7e11aeb5c29b80ad876ab62ac6aef03263ae9255719637a7c4b891', '9197ae4478ec76b730722bbce03e08890eacd90fb81bd97b1c6de469e1fd5412', '2025-09-25 18:47:16', '2025-09-25 18:47:16'),
(4, 'Jhon Doe', 'jhon.doe@correoficticio.com', '', '', 'nuevo', 1, '2552a8f150ba4b6edf45f4a245dd3f6f4eea202d41390d901040ff631e04a844', '8ff68edd840b2975c73daca6d27dc8530f98b5b78236479e8d0b9b4641946df1', '2025-09-25 18:57:06', '2025-09-25 18:58:55'),
(5, 'rrettr', 'test@test.com', '5544', '', 'nuevo', 1, '9894d3662fc6ef08105f06d2764bc479bbcb4b194b39286e97238af65cab63a2', '89e894550df45c2a74d34afaae4776b532fd358bbee2eca0f601f458f7b2836f', '2025-09-25 19:10:05', '2025-09-25 19:10:05');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo_unico` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
