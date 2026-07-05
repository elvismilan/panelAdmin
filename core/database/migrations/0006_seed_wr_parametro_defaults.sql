-- Migration: 0006_seed_wr_parametro_defaults
-- Description: Seed inicial recomendado para parametros core del sistema

INSERT INTO `wr_parametro` (`par_clave`, `par_valor`, `par_tipo`, `par_grupo`, `par_label`) VALUES
    ('rbac_version', '0', 'int', 'cache', 'Version cache RBAC'),
    ('ui_items_per_page', '10', 'int', 'ui', 'Items por pagina en listados'),
    ('logs_retencion_dias', '90', 'int', 'logs', 'Retencion de logs en dias'),
    ('notificaciones_retencion_dias', '30', 'int', 'notificaciones', 'Retencion de notificaciones en dias'),
    ('usuarios_reset_token_minutos', '60', 'int', 'seguridad', 'Duracion token reset password en minutos'),
    ('caja_moneda', 'BOB', 'string', 'flujo_caja', 'Moneda por defecto flujo de caja'),
    ('caja_decimales', '2', 'int', 'flujo_caja', 'Decimales por defecto flujo de caja')
ON DUPLICATE KEY UPDATE
    `par_valor` = VALUES(`par_valor`),
    `par_tipo` = VALUES(`par_tipo`),
    `par_grupo` = VALUES(`par_grupo`),
    `par_label` = VALUES(`par_label`);
