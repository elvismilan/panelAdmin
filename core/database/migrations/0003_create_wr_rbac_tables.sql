-- Migration: 0003_create_wr_rbac_tables
-- Description: Tablas core de menu, tareas y permisos

CREATE TABLE IF NOT EXISTS `wr_elemento` (
    `ele_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ele_nombre`  VARCHAR(250) NOT NULL,
    `ele_estado`  VARCHAR(3) NULL DEFAULT 'H',
    `ele_icono`   VARCHAR(250) NULL DEFAULT NULL,
    `ele_titulo`  VARCHAR(100) NOT NULL,
    `ele_orden`   INT NULL DEFAULT NULL,
    `ele_tipo`    VARCHAR(1) NOT NULL DEFAULT 'M',
    `ele_padre`   INT NULL DEFAULT NULL,
    `ele_tarea`   VARCHAR(100) NOT NULL DEFAULT 'ACCEDER',
    `deleted_at`  DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`ele_id`),
    INDEX `idx_ele_estado` (`ele_estado`),
    INDEX `idx_ele_padre` (`ele_padre`),
    INDEX `idx_ele_orden` (`ele_orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_elemento_tarea` (
    `eta_id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `eta_ele_id`  INT NOT NULL,
    `eta_tar_id`  INT NOT NULL,
    PRIMARY KEY (`eta_id`),
    INDEX `idx_eta_ele_id` (`eta_ele_id`),
    INDEX `idx_eta_tar_id` (`eta_tar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_permiso` (
    `id`          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pmo_ele_id`  INT NOT NULL,
    `pmo_tar_id`  INT NULL,
    `pmo_gru_id`  VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_pmo_ele_id` (`pmo_ele_id`),
    INDEX `idx_pmo_tar_id` (`pmo_tar_id`),
    INDEX `idx_pmo_gru_id` (`pmo_gru_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
