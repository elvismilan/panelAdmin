-- Migration: 0006_create_notificacion_lectura
-- Description: Tabla de lectura por usuario para notificaciones
CREATE TABLE IF NOT EXISTS `wr_notificacion_lectura` (
    `nrl_id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nrl_noti_id`  INT UNSIGNED    NOT NULL,
    `nrl_usu_id`   VARCHAR(250)    NOT NULL,
    `nrl_leida_en` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`nrl_id`),
    UNIQUE KEY `uq_nrl_noti_usu` (`nrl_noti_id`, `nrl_usu_id`),
    INDEX `idx_nrl_usu` (`nrl_usu_id`),
    INDEX `idx_nrl_noti` (`nrl_noti_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
