-- Migration: 0002_add_soft_deletes
-- Description: Agrega columna deleted_at para soft deletes en tablas principales
SET @schema_name := DATABASE();

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'wr_usuario'
              AND COLUMN_NAME = 'deleted_at'
        ),
        'SELECT 1',
        'ALTER TABLE `wr_usuario` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'wr_persona'
              AND COLUMN_NAME = 'deleted_at'
        ),
        'SELECT 1',
        'ALTER TABLE `wr_persona` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'wr_elemento'
              AND COLUMN_NAME = 'deleted_at'
        ),
        'SELECT 1',
        'ALTER TABLE `wr_elemento` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := (
    SELECT IF(
        EXISTS(
            SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = @schema_name
              AND TABLE_NAME = 'wr_grupo'
              AND COLUMN_NAME = 'deleted_at'
        ),
        'SELECT 1',
        'ALTER TABLE `wr_grupo` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL'
    )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
