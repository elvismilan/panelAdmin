-- Quita los modulos de contenido y la seccion Website con sus permisos. Deja las tareas FOTOS y ORDENAR.
DELETE p FROM `wr_permiso` p INNER JOIN `wr_elemento` e ON e.`ele_id` = p.`pmo_ele_id` WHERE e.`ele_nombre` IN ('website', 'blogs', 'contenidos', 'galerias', 'slider', 'redes', 'servicios', 'testimonios');
DELETE x FROM `wr_elemento_tarea` x INNER JOIN `wr_elemento` e ON e.`ele_id` = x.`eta_ele_id` WHERE e.`ele_nombre` IN ('website', 'blogs', 'contenidos', 'galerias', 'slider', 'redes', 'servicios', 'testimonios');
DELETE FROM `wr_elemento` WHERE `ele_nombre` IN ('blogs', 'contenidos', 'galerias', 'slider', 'redes', 'servicios', 'testimonios');
DELETE FROM `wr_elemento` WHERE `ele_nombre` = 'website';
UPDATE `wr_parametro` SET `par_valor` = CAST(CAST(`par_valor` AS UNSIGNED) + 1 AS CHAR) WHERE `par_clave` = 'rbac_version';
