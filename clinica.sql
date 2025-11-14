-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-11-2025 a las 13:13:51
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `clinica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `cita_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `hora_solicitud` time NOT NULL,
  `motivo` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'Pendiente',
  `dentista_id` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `metodo_pago` enum('efectivo','tarjeta') DEFAULT 'efectivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`cita_id`, `cliente_id`, `fecha_solicitud`, `hora_solicitud`, `motivo`, `estado`, `dentista_id`, `fecha_creacion`, `metodo_pago`) VALUES
(1, 0, '2025-11-11', '15:10:00', 'anasheee', 'Pendiente', NULL, '2025-11-10 14:09:20', 'efectivo'),
(5, 3, '2025-11-13', '00:07:00', 'dwfsdfsdf', 'Finalizada', 1, '2025-11-12 23:07:28', 'tarjeta'),
(6, 4, '2025-11-13', '00:19:00', 'sfsfasf', 'Rechazada', NULL, '2025-11-12 23:18:38', 'efectivo'),
(7, 5, '2025-11-13', '00:23:00', 'holaaa', 'Finalizada', 1, '2025-11-12 23:23:16', 'tarjeta'),
(8, 6, '2025-11-13', '23:34:00', 'asdasdasdadasd', 'Finalizada', 1, '2025-11-12 23:34:46', 'efectivo'),
(9, 7, '2025-11-12', '23:38:00', 'anasheeee', 'Finalizada', 1, '2025-11-12 23:38:30', 'tarjeta'),
(10, 8, '2025-11-13', '10:30:00', 'dolor de muela ', 'Confirmada', 1, '2025-11-12 23:50:32', 'efectivo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`cliente_id`, `nombre`, `email`, `telefono`, `fecha_registro`) VALUES
(1, 'juan perez', 'ejemplo@gmail.com', '434252352341', '2025-11-10 14:43:32'),
(2, 'joaquin leguizamon', 'joaquin@gmail.com', '213128376182461', '2025-11-12 22:31:06'),
(3, 'joaquin leguizamon', '1424124@gmail.com', '325235235235', '2025-11-12 23:07:27'),
(4, 'lucas birra', '23423423@gmail.com', '32234234', '2025-11-12 23:18:38'),
(5, 'lucas birra', 'lucas@gmail.com', '8712318236613', '2025-11-12 23:23:16'),
(6, 'Joaquin', 'askdjahksdjha@gmail.com', '235235234234', '2025-11-12 23:34:46'),
(7, 'brian caceres', 'brian@gmail.com', '73737373773', '2025-11-12 23:38:29'),
(8, 'ricardo lopez', 'ricardolopez@gmail.com', '1123456789', '2025-11-12 23:50:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dentistas`
--

CREATE TABLE `dentistas` (
  `dentista_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `especialidad` varchar(50) DEFAULT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dentistas`
--

INSERT INTO `dentistas` (`dentista_id`, `nombre`, `especialidad`, `usuario_id`) VALUES
(0, 'S.diana', 'Endodoncia', 0),
(1, 'Dr. Carlos Gómez', 'Odontología General', 2),
(2, 'Dra. Belén Ruiz Díaz', 'Odontóloga', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjeta`
--

CREATE TABLE `tarjeta` (
  `id` int(11) NOT NULL,
  `cita_id` int(11) NOT NULL,
  `nombre_titular` varchar(100) NOT NULL,
  `numero_tarjeta` varchar(20) NOT NULL,
  `vencimiento` varchar(7) NOT NULL,
  `cvv` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tarjeta`
--

INSERT INTO `tarjeta` (`id`, `cita_id`, `nombre_titular`, `numero_tarjeta`, `vencimiento`, `cvv`) VALUES
(1, 5, 'joaquin leguimzamon', '23423326754723', '20/28', '123'),
(2, 7, 'lucas birra', '1234556789', '24/28', '123'),
(3, 9, 'brian caceres', '1234567890', '10/28', '123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario_id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario_id`, `usuario`, `password`, `rol`) VALUES
(1, 'recepcionista@docraul.com', '$2y$10$dZ/996aIMA2WxpDTpNBVau5/X/Ck3gvwQUSf8SjjqOOSgxucUrFha', 'recepcionista'),
(2, 'carlos.gomez@docraul.com', '$2y$10$GYS0QwZYpT2z.bJyTvHit.KE9nwvmQiBkFSl3O.0iq3YHd5Vqkg0G', 'dentista'),
(3, 'belen.ruiz@docraul.com', '$2y$10$Q7YyH5Zg8h5p6T1r4v5O4e2M9q1E0A8F4l2V2b4N7u7x1K2Z3J6I5B', 'dentista'),
(0, 'diana@docraul.com', '$2y$10$Maka0BpAfqgnbD8nmJf4quWR9MFWP/Lo.pdzTZj1i9cp9ATBAb/9O', 'dentista');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`cita_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cliente_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `dentistas`
--
ALTER TABLE `dentistas`
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cita_id` (`cita_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `cita_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cliente_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tarjeta`
--
ALTER TABLE `tarjeta`
  ADD CONSTRAINT `tarjeta_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`cita_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
