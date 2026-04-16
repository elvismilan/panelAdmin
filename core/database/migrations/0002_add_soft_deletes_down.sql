-- Rollback: 0002_add_soft_deletes
ALTER TABLE `wr_usuario`  DROP COLUMN `deleted_at`;
ALTER TABLE `wr_persona`  DROP COLUMN `deleted_at`;
ALTER TABLE `wr_elemento` DROP COLUMN `deleted_at`;
ALTER TABLE `wr_grupo`    DROP COLUMN `deleted_at`;
