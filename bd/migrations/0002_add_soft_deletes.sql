-- Migration: 0002_add_soft_deletes
-- Description: Agrega columna deleted_at para soft deletes en tablas principales
ALTER TABLE `wr_usuario`  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `wr_persona`  ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `wr_elemento` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL;
ALTER TABLE `wr_grupo`    ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL;
