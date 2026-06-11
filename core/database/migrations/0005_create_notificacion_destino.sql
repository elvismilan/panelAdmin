-- Migration: 0005_create_notificacion_destino
-- Description: Entregas por usuario para notificaciones (estado de lectura por destinatario)
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

-- Backfill inicial: copiar datos historicos si las tablas existen
INSERT IGNORE INTO `wr_notificacion_destino` (`nd_noti_id`, `nd_usu_id`, `nd_estado`, `nd_leida`, `nd_leida_en`, `nd_entregada_en`)
SELECT
    n.`noti_id`,
    u.`usu_id`,
    CASE WHEN n.`noti_leida` = 1 THEN 'read' ELSE 'unread' END,
    n.`noti_leida`,
    n.`noti_leida_en`,
    n.`noti_fecha`
FROM `wr_notificacion` n
INNER JOIN `wr_usuario` u ON u.`usu_estado` = 'H'
WHERE NOT EXISTS (
    SELECT 1 FROM `wr_notificacion_destino` d
    WHERE d.`nd_noti_id` = n.`noti_id` AND d.`nd_usu_id` = u.`usu_id`
);
