-- Migration: 0005_create_notificacion_destino_down
-- Description: Eliminar tabla de entregas por usuario de notificaciones
DROP TABLE IF EXISTS `notificacion_destino`;

-- Intentar eliminar tambien la variante prefijada (si existe).
SET @schema_name := DATABASE();
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
SET @tbl_destino_pref := CONCAT(@tbl_prefix, 'notificacion_destino');

SET @sql_drop_pref := IF(
    @tbl_destino_pref <> 'notificacion_destino',
    CONCAT('DROP TABLE IF EXISTS `', @tbl_destino_pref, '`'),
    'SELECT 1'
);

PREPARE stmt_drop_pref FROM @sql_drop_pref;
EXECUTE stmt_drop_pref;
DEALLOCATE PREPARE stmt_drop_pref;
