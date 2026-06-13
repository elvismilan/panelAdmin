-- Migration down: 0006_seed_wr_parametro_defaults

DELETE FROM `wr_parametro`
WHERE `par_clave` IN (
    'rbac_version',
    'ui_items_per_page',
    'logs_retencion_dias',
    'notificaciones_retencion_dias',
    'usuarios_reset_token_minutos',
    'caja_moneda',
    'caja_decimales'
);
