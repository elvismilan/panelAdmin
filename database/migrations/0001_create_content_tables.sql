-- panelAdmin plantilla 0001: tablas de los modulos de contenido (Blog, Contenidos, Servicios,
-- Slider, Galerias y Redes sociales). Esquema de produccion de josePerales (dump del 24/11/2026),
-- solo estructura. Diferencias a proposito: utf8mb4 + utf8mb4_unicode_ci en todas, e ids INT
-- (en produccion eran TINYINT/SMALLINT: tope de 127 filas). Idempotente.

CREATE TABLE IF NOT EXISTS `categorias` (
    `cat_id` int NOT NULL AUTO_INCREMENT,
    `cat_name` varchar(150) NOT NULL,
    `cat_slug` varchar(150) NOT NULL,
    `cat_tipo` varchar(60) NOT NULL DEFAULT 'blog',
    `cat_parent` int NOT NULL DEFAULT '0',
    `cat_status` enum('H','D','E') NOT NULL,
    `cat_created` datetime DEFAULT NULL,
    PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categoria_relacion` (
    `id` int NOT NULL AUTO_INCREMENT,
    `categoria_id` int NOT NULL,
    `rel_tipo` varchar(60) NOT NULL,
    `rel_id` int NOT NULL,
    `created` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_categoria_relacion` (`categoria_id`,`rel_tipo`,`rel_id`),
    KEY `ix_categoria_rel_lookup` (`rel_tipo`,`rel_id`),
    KEY `ix_categoria_rel_categoria` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
    `id` int NOT NULL AUTO_INCREMENT,
    `titulo` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `contenido` text NOT NULL,
    `detalle` varchar(160) DEFAULT NULL,
    `imagen` varchar(225) DEFAULT NULL,
    `autor` varchar(32) DEFAULT NULL,
    `vistas` mediumint NOT NULL DEFAULT '1',
    `estado` enum('H','D') NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contenidos` (
    `id` int NOT NULL AUTO_INCREMENT,
    `titulo` varchar(128) DEFAULT NULL,
    `subtitle` varchar(300) DEFAULT NULL,
    `contenido` text NOT NULL,
    `imagen` varchar(128) DEFAULT NULL,
    `lugar` varchar(64) DEFAULT NULL,
    `link` varchar(255) DEFAULT NULL,
    `estado` enum('H','D') NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `servicios` (
    `id` int NOT NULL AUTO_INCREMENT,
    `titulo` varchar(255) NOT NULL,
    `subtitulo` varchar(255) DEFAULT NULL,
    `slug` varchar(255) NOT NULL,
    `contenido` text NOT NULL,
    `imagen` varchar(128) DEFAULT NULL,
    `vistas` mediumint DEFAULT '0',
    `estado` enum('H','D') NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    `icon` varchar(128) DEFAULT NULL,
    `detalle` varchar(160) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `slider` (
    `id` int NOT NULL AUTO_INCREMENT,
    `titulo` varchar(128) NOT NULL,
    `subtitle` varchar(200) NOT NULL,
    `description` text,
    `imagen` varchar(128) DEFAULT NULL,
    `overlay` float(2,2) DEFAULT NULL,
    `orden` tinyint UNSIGNED NOT NULL DEFAULT '0',
    `estado` enum('H','D') NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime DEFAULT NULL,
    `link_btn` varchar(128) DEFAULT NULL,
    `nombre_btn` varchar(128) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ux_slider_orden` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `galeria` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL,
    `resumen` text NOT NULL,
    `fecha` date DEFAULT NULL,
    `portada` varchar(100) DEFAULT NULL,
    `tabla_id` int NOT NULL,
    `tabla` varchar(20) NOT NULL,
    `vistas` mediumint NOT NULL DEFAULT '0',
    `created` datetime NOT NULL,
    `estado` enum('H','D','E') NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `galeria_foto` (
    `igf_id` int NOT NULL AUTO_INCREMENT,
    `igf_image` varchar(150) NOT NULL,
    `igf_gal_id` int NOT NULL,
    `igf_alt` varchar(100) DEFAULT NULL,
    `created` datetime NOT NULL,
    PRIMARY KEY (`igf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `redes_sociales` (
    `res_id` int NOT NULL AUTO_INCREMENT,
    `res_titulo` varchar(32) NOT NULL,
    `res_link` varchar(128) NOT NULL,
    `res_icon` varchar(32) NOT NULL,
    `res_estado` enum('H','D') NOT NULL,
    `created` datetime NOT NULL,
    `modified` datetime NOT NULL,
    PRIMARY KEY (`res_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
