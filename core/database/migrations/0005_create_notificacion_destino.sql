-- Migration: 0005_create_notificacion_destino
-- Description: Entregas por usuario para notificaciones (estado de lectura por destinatario)
CREATE TABLE IF NOT EXISTS `notificacion_destino` (
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

-- Backfill inicial compatible con tablas con/sin prefijo.
SET @schema_name := DATABASE();

-- Detectar tabla real de usuarios (prioriza prefijada si existe).
SET @tbl_usuario := (
    SELECT c.TABLE_NAME
    FROM INFORMATION_SCHEMA.COLUMNS c
    WHERE c.TABLE_SCHEMA = @schema_name
      AND c.COLUMN_NAME = 'usu_estado'
      AND c.TABLE_NAME LIKE '%usuario'
    ORDER BY
      CASE WHEN c.TABLE_NAME = 'usuario' THEN 1 ELSE 0 END,
      LENGTH(c.TABLE_NAME) DESC
    LIMIT 1
);

SET @tbl_prefix := IF(@tbl_usuario IS NULL, '', REPLACE(@tbl_usuario, 'usuario', ''));

-- Detectar tabla real de notificaciones (prioriza la del mismo prefijo de usuario).
SET @tbl_noti := (
    SELECT c.TABLE_NAME
    FROM INFORMATION_SCHEMA.COLUMNS c
    WHERE c.TABLE_SCHEMA = @schema_name
      AND c.COLUMN_NAME = 'noti_id'
      AND c.TABLE_NAME IN (CONCAT(@tbl_prefix, 'notificacion'), 'notificacion')
    ORDER BY
      CASE WHEN c.TABLE_NAME = CONCAT(@tbl_prefix, 'notificacion') THEN 0 ELSE 1 END,
      LENGTH(c.TABLE_NAME) DESC
    LIMIT 1
);

-- Fallback final: tomar cualquier tabla valida de notificacion.
SET @tbl_noti := COALESCE(
    @tbl_noti,
    (
        SELECT c.TABLE_NAME
        FROM INFORMATION_SCHEMA.COLUMNS c
        WHERE c.TABLE_SCHEMA = @schema_name
          AND c.COLUMN_NAME = 'noti_id'
          AND c.TABLE_NAME LIKE '%notificacion'
        ORDER BY
          CASE WHEN c.TABLE_NAME = 'notificacion' THEN 1 ELSE 0 END,
          LENGTH(c.TABLE_NAME) DESC
        LIMIT 1
    )
);

SET @can_backfill := IF(@tbl_noti IS NOT NULL AND @tbl_usuario IS NOT NULL, 1, 0);

SET @sql_backfill := IF(
    @can_backfill = 1,
    CONCAT(
        'INSERT IGNORE INTO `notificacion_destino` (`nd_noti_id`,`nd_usu_id`,`nd_estado`,`nd_leida`,`nd_leida_en`,`nd_entregada_en`) ',
        'SELECT n.noti_id, u.usu_id, ',
        'CASE WHEN n.noti_leida = 1 THEN ''read'' ELSE ''unread'' END, ',
        'n.noti_leida, n.noti_leida_en, n.noti_fecha ',
        'FROM `', @tbl_noti, '` n ',
        'INNER JOIN `', @tbl_usuario, '` u ON u.usu_estado = ''H'''
    ),
    'SELECT 1'
);

PREPARE stmt_backfill FROM @sql_backfill;
EXECUTE stmt_backfill;
DEALLOCATE PREPARE stmt_backfill;
