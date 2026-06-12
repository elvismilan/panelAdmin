-- Migration: 0007_backfill_notificacion_lectura
-- Description: Migra estados historicos de lectura hacia wr_notificacion_lectura

INSERT IGNORE INTO `wr_notificacion_lectura` (`nrl_noti_id`, `nrl_usu_id`, `nrl_leida_en`)
SELECT
    d.`nd_noti_id`,
    d.`nd_usu_id`,
    COALESCE(d.`nd_leida_en`, d.`nd_entregada_en`, NOW())
FROM `wr_notificacion_destino` d
WHERE d.`nd_leida` = 1
  AND d.`nd_usu_id` <> '__notification_hidden__';
