-- Migration: 0004_create_notificacion
-- Description: Tabla de notificaciones del sistema (solo lectura, generadas automaticamente)
CREATE TABLE IF NOT EXISTS `notificacion` (
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
    INDEX `idx_noti_leida`   (`noti_leida`),
    INDEX `idx_noti_fecha`   (`noti_fecha`),
    INDEX `idx_noti_modulo`  (`noti_modulo`, `noti_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
