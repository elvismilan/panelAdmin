-- Migration: 0005_create_wr_parametro_table
-- Description: Tabla core de parametros del sistema

CREATE TABLE IF NOT EXISTS `wr_parametro` (
    `par_id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `par_clave`      VARCHAR(100) NOT NULL,
    `par_valor`      TEXT NULL,
    `par_tipo`       VARCHAR(50)  NOT NULL DEFAULT 'string',
    `par_grupo`      VARCHAR(100) NOT NULL,
    `par_label`      VARCHAR(250) NOT NULL,
    `par_created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `par_updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`par_id`),
    UNIQUE KEY `uq_par_clave` (`par_clave`),
    INDEX `idx_par_grupo` (`par_grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
