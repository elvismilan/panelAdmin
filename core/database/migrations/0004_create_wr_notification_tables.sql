-- Migration: 0004_create_wr_notification_tables
-- Description: Tablas core de notificaciones

CREATE TABLE IF NOT EXISTS `wr_notificacion` (
    `noti_id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `noti_titulo`        VARCHAR(255)  NOT NULL,
    `noti_mensaje`       TEXT          NOT NULL,
    `noti_tipo`          VARCHAR(30)   NOT NULL DEFAULT 'info',
    `noti_modulo`        VARCHAR(100)  NOT NULL DEFAULT '',
    `noti_accion`        VARCHAR(30)   NOT NULL DEFAULT '',
    `noti_usu_origen`    VARCHAR(250)  NOT NULL DEFAULT '',
    `noti_fecha`         DATETIME      NOT NULL,
    `noti_leida`         TINYINT(1)    NOT NULL DEFAULT 0,
    `noti_leida_en`      DATETIME      NULL,
    `noti_referencia_id` VARCHAR(250)  NULL,
    PRIMARY KEY (`noti_id`),
    INDEX `idx_noti_leida` (`noti_leida`),
    INDEX `idx_noti_fecha` (`noti_fecha`),
    INDEX `idx_noti_modulo` (`noti_modulo`, `noti_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wr_notificacion_destino` (
    `nd_id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nd_noti_id`      INT UNSIGNED    NOT NULL,
    `nd_usu_id`       VARCHAR(250)    NOT NULL,
    `nd_estado`       VARCHAR(20)     NOT NULL DEFAULT 'unread',
    `nd_leida`        TINYINT(1)      NOT NULL DEFAULT 0,
    `nd_leida_en`     DATETIME        NULL,
    `nd_entregada_en` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `nd_archivada_en` DATETIME        NULL,
    PRIMARY KEY (`nd_id`),
    UNIQUE KEY `uq_nd_noti_usu` (`nd_noti_id`, `nd_usu_id`),
    INDEX `idx_nd_usu_leida` (`nd_usu_id`, `nd_leida`, `nd_estado`),
    INDEX `idx_nd_noti` (`nd_noti_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
