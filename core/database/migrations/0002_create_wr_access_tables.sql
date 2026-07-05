-- Migration: 0002_create_wr_access_tables
-- Description: Tablas core de acceso, personas y usuarios

CREATE TABLE IF NOT EXISTS `wr_grupo` (
    `id`               TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `gru_id`           VARCHAR(100) NULL,
    `gru_descripcion`  TEXT NULL,
    `gru_estado`       VARCHAR(1) NULL,
    `deleted_at`       DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_gru_id` (`gru_id`),
    INDEX `idx_gru_estado` (`gru_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_persona` (
    `per_id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `per_nombre`           VARCHAR(250) NOT NULL,
    `per_apellido`         VARCHAR(250) NOT NULL,
    `per_email`            VARCHAR(250) NULL,
    `per_foto`             VARCHAR(250) NULL,
    `per_telefono`         VARCHAR(50) NULL,
    `per_direccion`        TEXT NULL,
    `per_ci`               VARCHAR(255) NULL,
    `per_fecha_nacimiento` DATE NULL,
    `per_sexo`             ENUM('M','F') NOT NULL,
    `per_estado`           VARCHAR(1) NOT NULL,
    `deleted_at`           DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`per_id`),
    INDEX `idx_per_email` (`per_email`),
    INDEX `idx_per_ci` (`per_ci`),
    INDEX `idx_per_estado` (`per_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_tarea` (
    `tar_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tar_nombre`  VARCHAR(255) NOT NULL,
    PRIMARY KEY (`tar_id`),
    UNIQUE KEY `uq_tar_nombre` (`tar_nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_usuario` (
    `usu_id`       CHAR(50) NOT NULL,
    `usu_password` VARCHAR(255) NULL,
    `usu_per_id`   INT UNSIGNED NULL,
    `usu_estado`   CHAR(1) NULL,
    `usu_gru_id`   VARCHAR(100) NOT NULL,
    `deleted_at`   DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`usu_id`),
    INDEX `idx_usu_per_id` (`usu_per_id`),
    INDEX `idx_usu_gru_id` (`usu_gru_id`),
    INDEX `idx_usu_estado` (`usu_estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
