-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 28, 2026 at 02:59 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admin_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `elemento`
--

CREATE TABLE `elemento` (
  `ele_id` int(11) NOT NULL,
  `ele_nombre` varchar(250) CHARACTER SET utf8mb4 NOT NULL,
  `ele_estado` varchar(3) CHARACTER SET utf8mb4 DEFAULT 'H',
  `ele_icono` varchar(250) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ele_titulo` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `ele_orden` int(11) DEFAULT NULL,
  `ele_tipo` varchar(1) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'M',
  `ele_padre` int(11) DEFAULT NULL,
  `ele_tarea` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'ACCEDER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `elemento`
--

INSERT INTO `elemento` (`ele_id`, `ele_nombre`, `ele_estado`, `ele_icono`, `ele_titulo`, `ele_orden`, `ele_tipo`, `ele_padre`, `ele_tarea`) VALUES
(11, 'dashboard', 'H', 'fa fa-home', 'Inicio', 0, 'C', 0, 'ACCEDER'),
(21, 'adm', 'H', 'fa fa-cogs', 'Administracion', 1, 'C', 0, 'ACCEDER'),
(22, 'usuarios', 'H', NULL, 'Usuario', 3, 'M', 21, 'ACCEDER'),
(23, 'personas', 'H', NULL, 'Persona', 2, 'M', 21, 'ACCEDER'),
(24, 'grupos', 'H', NULL, 'Grupos', 4, 'M', 21, 'ACCEDER'),
(41, 'backup', 'I', NULL, 'Copia de Seguridad (Backup)', 6, 'M', 127, 'ACCEDER'),
(53, 'logs', 'H', NULL, 'Log', 9, 'M', 21, 'ACCEDER'),
(55, 'modulos', 'H', '', 'Modulos', 5, 'M', 21, 'ACCEDER'),
(127, 'confi', 'I', 'fa fa-stack-exchange', 'Configuracion', 15, 'M', NULL, 'ACCEDER'),
(133, 'inbox', 'H', '', 'Inbox', 2, 'M', 134, 'ACCEDER'),
(134, 'msn', 'H', 'fa fa-envelope', 'Mensajes', 9, 'C', 0, 'ACCEDER'),
(137, 'tareas', 'H', NULL, 'Tareas', 6, 'M', 21, 'ACCEDER'),
(138, 'flujo', 'H', 'fa fa-money', 'Flujo de Caja', 5, 'C', 0, 'ACCEDER'),
(139, 'tipocambio', 'H', '', 'Tipo de Cambio', 2, 'M', 138, 'ACCEDER'),
(140, 'cuentas', 'H', '', 'Plan de Cuentas', 4, 'M', 138, 'ACCEDER'),
(141, 'cajeros', 'H', '', 'Cajeros', 6, 'M', 138, 'ACCEDER'),
(142, 'comprobantes', 'H', '', 'Comprobantes', 8, 'M', 138, 'ACCEDER'),
(143, 'traspasos', 'H', '', 'Traspasos', 10, 'M', 138, 'ACCEDER'),
(144, 'terrenos', 'H', 'fa fa-thumbs-o-up', 'Terrenos', 7, 'C', 0, 'ACCEDER'),
(145, 'urbanizaciones', 'H', '', 'Urbanizacion', 2, 'M', 144, 'ACCEDER'),
(146, 'ventas', 'H', '', 'Venta de Terrenos', 4, 'M', 144, 'ACCEDER'),
(147, 'venta', 'H', '', 'Cobro de Cuotas', 6, 'M', 144, 'ACCEDER'),
(148, 'reporte', 'H', ' fa fa-bar-chart-o', 'Reportes', 9, 'C', 0, 'ACCEDER'),
(149, 'repcuenta', 'H', '', 'Plan de Cuentas', 2, 'M', 148, 'ACCEDER'),
(150, 'repestadoresultado', 'H', '', 'Estado de Ingresos/Egresos', 3, 'M', 148, 'ACCEDER'),
(151, 'repdetalleestadoresultado', 'H', '', 'Detalle de Cuentas', 4, 'M', 148, 'ACCEDER'),
(152, 'repbalance', 'H', '', 'Balance General', 5, 'M', 148, 'ACCEDER'),
(153, 'repdetallebalance', 'H', '', 'Detalle de Balance General', 6, 'M', 148, 'ACCEDER'),
(154, 'arqueo', 'H', '', 'Arqueo de Caja', 7, 'M', 148, 'ACCEDER'),
(156, 'planpagos', 'H', '', 'Plan de Pagos', 1, 'M', 144, 'ACCEDER'),
(157, 'comisiones', 'H', '', 'Comisiones', 0, 'M', 144, 'ACCEDER');

-- --------------------------------------------------------

--
-- Table structure for table `elemento_tarea`
--

CREATE TABLE `elemento_tarea` (
  `eta_id` int(11) NOT NULL,
  `eta_ele_id` int(11) NOT NULL,
  `eta_tar_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `elemento_tarea`
--

INSERT INTO `elemento_tarea` (`eta_id`, `eta_ele_id`, `eta_tar_id`) VALUES
(1, 21, 1),
(2, 22, 1),
(3, 22, 2),
(4, 22, 3),
(5, 22, 4),
(6, 22, 5),
(7, 23, 1),
(8, 23, 2),
(9, 23, 3),
(10, 23, 4),
(11, 23, 5),
(184, 112, 1),
(185, 113, 1),
(186, 113, 2),
(187, 113, 3),
(188, 113, 4),
(189, 113, 5),
(190, 114, 1),
(191, 114, 2),
(192, 114, 3),
(193, 114, 4),
(194, 114, 5),
(195, 115, 1),
(196, 115, 2),
(197, 115, 3),
(198, 115, 4),
(199, 115, 5),
(200, 116, 1),
(201, 116, 2),
(202, 116, 3),
(203, 116, 4),
(204, 116, 5),
(205, 117, 1),
(206, 117, 2),
(207, 117, 3),
(208, 117, 4),
(209, 117, 5),
(210, 118, 1),
(211, 118, 2),
(212, 118, 3),
(213, 118, 4),
(214, 118, 5),
(215, 119, 1),
(216, 119, 2),
(217, 119, 3),
(218, 119, 4),
(219, 120, 1),
(220, 120, 2),
(221, 120, 3),
(222, 120, 4),
(223, 120, 5),
(224, 121, 1),
(225, 121, 2),
(226, 121, 3),
(227, 121, 4),
(228, 121, 5),
(229, 122, 1),
(230, 122, 2),
(231, 122, 3),
(232, 122, 4),
(233, 122, 5),
(241, 124, 1),
(242, 124, 2),
(243, 124, 3),
(244, 124, 4),
(245, 124, 5),
(246, 124, 7),
(247, 124, 8),
(248, 124, 9),
(249, 124, 10),
(250, 124, 11),
(251, 125, 1),
(252, 126, 1),
(253, 126, 2),
(254, 126, 3),
(255, 126, 4),
(256, 126, 5),
(258, 128, 1),
(259, 128, 2),
(260, 128, 3),
(261, 128, 4),
(262, 128, 5),
(263, 132, 12),
(268, 130, 1),
(269, 130, 2),
(270, 130, 3),
(271, 130, 4),
(272, 130, 5),
(273, 130, 13),
(274, 114, 14),
(282, 131, 1),
(283, 131, 2),
(284, 131, 3),
(285, 131, 4),
(286, 131, 5),
(287, 132, 1),
(288, 132, 2),
(289, 132, 3),
(290, 132, 4),
(291, 132, 5),
(293, 134, 1),
(311, 55, 1),
(312, 55, 3),
(313, 55, 5),
(314, 55, 4),
(315, 55, 2),
(345, 133, 1),
(346, 133, 3),
(347, 133, 5),
(348, 133, 14),
(349, 133, 16),
(350, 133, 15),
(351, 133, 2),
(356, 135, 1),
(364, 143, 1),
(365, 143, 3),
(366, 143, 5),
(367, 143, 18),
(368, 143, 2),
(376, 136, 1),
(377, 136, 17),
(378, 136, 5),
(379, 136, 18),
(380, 136, 4),
(381, 136, 2),
(412, 123, 1),
(413, 123, 3),
(414, 123, 5),
(415, 123, 4),
(416, 123, 2),
(460, 138, 1),
(470, 139, 1),
(471, 139, 2),
(472, 139, 3),
(473, 139, 4),
(486, 141, 1),
(487, 141, 2),
(488, 141, 3),
(489, 141, 4),
(490, 143, 1),
(491, 143, 2),
(492, 143, 3),
(493, 143, 18),
(494, 143, 21),
(515, 148, 1),
(535, 151, 1),
(536, 152, 1),
(537, 153, 1),
(539, 150, 1),
(554, 154, 1),
(555, 140, 1),
(556, 140, 2),
(557, 140, 3),
(558, 140, 4),
(559, 140, 5),
(570, 155, 1),
(571, 155, 2),
(572, 155, 3),
(573, 155, 4),
(574, 155, 5),
(582, 149, 1),
(612, 144, 1),
(628, 145, 1),
(629, 145, 2),
(630, 145, 3),
(631, 145, 4),
(632, 145, 5),
(633, 145, 24),
(634, 145, 25),
(673, 147, 1),
(674, 147, 2),
(675, 147, 21),
(676, 147, 22),
(677, 147, 26),
(678, 147, 27),
(679, 156, 1),
(689, 142, 1),
(690, 142, 2),
(691, 142, 3),
(692, 142, 4),
(693, 142, 21),
(694, 146, 1),
(695, 146, 2),
(696, 146, 3),
(697, 146, 18),
(698, 146, 21),
(699, 146, 22),
(700, 146, 26),
(701, 146, 27),
(702, 157, 1),
(703, 157, 3),
(704, 157, 18),
(705, 157, 21),
(706, 157, 22),
(707, 157, 26),
(709, 41, 1),
(711, 127, 1),
(712, 11, 1),
(713, 137, 1),
(714, 137, 2),
(715, 137, 4),
(716, 137, 5),
(717, 137, 3),
(719, 24, 1),
(720, 24, 2),
(721, 24, 4),
(722, 24, 5),
(723, 24, 3),
(725, 53, 1),
(726, 53, 3);

-- --------------------------------------------------------

--
-- Table structure for table `grupo`
--

CREATE TABLE `grupo` (
  `id` tinyint(4) NOT NULL,
  `gru_id` varchar(100) DEFAULT NULL,
  `gru_descripcion` text,
  `gru_estado` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grupo`
--

INSERT INTO `grupo` (`id`, `gru_id`, `gru_descripcion`, `gru_estado`) VALUES
(1, 'Administrador', 'Administrador', 'H'),
(2, 'Asistente', 'Accesos necesarios', 'H');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `log_accion` text,
  `log_usu_id` varchar(100) DEFAULT NULL,
  `log_fecha` date DEFAULT NULL,
  `log_hora` time NOT NULL,
  `log_tipo_accion` varchar(300) NOT NULL,
  `log_ip` varchar(255) NOT NULL,
  `log_pc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `run_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notificacion`
--

CREATE TABLE `notificacion` (
  `noti_id` int(10) UNSIGNED NOT NULL,
  `noti_titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `noti_mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `noti_tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `noti_modulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `noti_accion` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `noti_usu_origen` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `noti_fecha` datetime NOT NULL,
  `noti_leida` tinyint(1) NOT NULL DEFAULT '0',
  `noti_leida_en` datetime DEFAULT NULL,
  `noti_referencia_id` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permiso`
--

CREATE TABLE `permiso` (
  `id` smallint(6) NOT NULL,
  `pmo_ele_id` int(11) NOT NULL,
  `pmo_tar_id` int(11) NOT NULL,
  `pmo_gru_id` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permiso`
--

INSERT INTO `permiso` (`id`, `pmo_ele_id`, `pmo_tar_id`, `pmo_gru_id`) VALUES
(40, 11, 1, 'Asistente'),
(41, 21, 1, 'Asistente'),
(42, 23, 1, 'Asistente'),
(43, 23, 2, 'Asistente'),
(44, 23, 4, 'Asistente'),
(45, 23, 5, 'Asistente'),
(46, 22, 1, 'Asistente'),
(47, 22, 2, 'Asistente'),
(48, 22, 4, 'Asistente'),
(49, 22, 5, 'Asistente'),
(50, 24, 1, 'Asistente'),
(51, 24, 3, 'Asistente'),
(75, 11, 1, 'Administrador'),
(76, 21, 1, 'Administrador'),
(77, 23, 1, 'Administrador'),
(78, 23, 2, 'Administrador'),
(79, 23, 4, 'Administrador'),
(80, 23, 5, 'Administrador'),
(81, 22, 1, 'Administrador'),
(82, 22, 2, 'Administrador'),
(83, 22, 4, 'Administrador'),
(84, 22, 5, 'Administrador'),
(85, 24, 1, 'Administrador'),
(86, 24, 2, 'Administrador'),
(87, 24, 4, 'Administrador'),
(88, 24, 5, 'Administrador'),
(89, 55, 1, 'Administrador'),
(90, 55, 2, 'Administrador'),
(91, 55, 4, 'Administrador'),
(92, 55, 5, 'Administrador'),
(93, 137, 1, 'Administrador'),
(94, 137, 2, 'Administrador'),
(95, 137, 4, 'Administrador'),
(96, 137, 5, 'Administrador'),
(97, 53, 1, 'Administrador'),
(98, 53, 3, 'Administrador');

-- --------------------------------------------------------

--
-- Table structure for table `persona`
--

CREATE TABLE `persona` (
  `per_id` int(11) NOT NULL,
  `per_nombre` varchar(250) NOT NULL,
  `per_apellido` varchar(250) NOT NULL,
  `per_email` varchar(250) DEFAULT NULL,
  `per_foto` varchar(250) DEFAULT NULL,
  `per_telefono` varchar(50) DEFAULT NULL,
  `per_direccion` text,
  `per_ci` varchar(255) DEFAULT NULL,
  `per_fecha_nacimiento` date DEFAULT NULL,
  `per_sexo` enum('M','F') NOT NULL,
  `per_estado` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `persona`
--

INSERT INTO `persona` (`per_id`, `per_nombre`, `per_apellido`, `per_email`, `per_foto`, `per_telefono`, `per_direccion`, `per_ci`, `per_fecha_nacimiento`, `per_sexo`, `per_estado`) VALUES
(1, 'Elvis', 'Milan', 'milan.elvis@gmail.com', 'uploads/personas/f27e88c47dc50b45_1776203331.jpg', '73196467', 'Av. Bolivia', '5859310', '1992-12-02', 'M', 'H'),
(3, 'Maite', 'Milan', 'maitemilan@gmail.com', 'uploads/personas/6411bc4fc3bbe104_1776203304.jpeg', NULL, NULL, NULL, '2013-01-16', 'F', 'H');

-- --------------------------------------------------------

--
-- Table structure for table `tarea`
--

CREATE TABLE `tarea` (
  `tar_id` int(11) NOT NULL,
  `tar_nombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tarea`
--

INSERT INTO `tarea` (`tar_id`, `tar_nombre`) VALUES
(1, 'ACCEDER'),
(2, 'AGREGAR'),
(3, 'VER'),
(4, 'EDITAR'),
(5, 'ELIMINAR'),
(6, 'FOTOS'),
(7, 'IMPRIMIR'),
(8, 'DETALLE');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `usu_id` char(50) NOT NULL,
  `usu_password` varchar(255) DEFAULT NULL,
  `usu_per_id` tinyint(4) DEFAULT NULL,
  `usu_estado` char(1) DEFAULT NULL,
  `usu_gru_id` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`usu_id`, `usu_password`, `usu_per_id`, `usu_estado`, `usu_gru_id`) VALUES
('awesome', '$2y$10$Ul/hIySKmRAPzfq9B3jAfuNDHIdHDYhm649MHcXagcBDKpTmrO3X2', 1, 'H', 'Administrador'),
('mmaite', '$2y$10$QKLZW/kJYKMFr5kreHPyDeA9DDdpQGHxhFaj9leOITPSSZtPENcYO', 3, 'H', 'Asistente');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `elemento`
--
ALTER TABLE `elemento`
  ADD PRIMARY KEY (`ele_id`);

--
-- Indexes for table `elemento_tarea`
--
ALTER TABLE `elemento_tarea`
  ADD PRIMARY KEY (`eta_id`);

--
-- Indexes for table `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_attempted` (`ip`,`attempted_at`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD UNIQUE KEY `log_id` (`log_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_migration` (`migration`);

--
-- Indexes for table `notificacion`
--
ALTER TABLE `notificacion`
  ADD PRIMARY KEY (`noti_id`),
  ADD KEY `idx_noti_leida` (`noti_leida`),
  ADD KEY `idx_noti_fecha` (`noti_fecha`),
  ADD KEY `idx_noti_modulo` (`noti_modulo`,`noti_accion`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`per_id`);

--
-- Indexes for table `tarea`
--
ALTER TABLE `tarea`
  ADD PRIMARY KEY (`tar_id`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `elemento`
--
ALTER TABLE `elemento`
  MODIFY `ele_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `elemento_tarea`
--
ALTER TABLE `elemento_tarea`
  MODIFY `eta_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=727;

--
-- AUTO_INCREMENT for table `grupo`
--
ALTER TABLE `grupo`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notificacion`
--
ALTER TABLE `notificacion`
  MODIFY `noti_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permiso`
--
ALTER TABLE `permiso`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `persona`
--
ALTER TABLE `persona`
  MODIFY `per_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tarea`
--
ALTER TABLE `tarea`
  MODIFY `tar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
