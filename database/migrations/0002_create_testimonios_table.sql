-- panelAdmin plantilla 0002 (josePerales 0007 + 0016): testimonios, con `contexto`.
-- Tabla de testimonios (modulo CMS).
-- Convencion: tabla de negocio sin prefijo. Soft delete via columna `deleted`.
CREATE TABLE IF NOT EXISTS `testimonios` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `contexto` VARCHAR(60) NULL DEFAULT NULL,
    `imagen` VARCHAR(128) NULL DEFAULT NULL,
    `detalle` TEXT NOT NULL,
    `orden` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `estado` ENUM('H','D') NOT NULL DEFAULT 'H',
    `created` DATETIME NOT NULL,
    `modified` DATETIME NOT NULL,
    `deleted` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_testimonios_estado` (`estado`),
    KEY `idx_testimonios_orden` (`orden`),
    KEY `idx_testimonios_deleted` (`deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
