-- panelAdmin plantilla 0004: seccion Website del admin, sus modulos de contenido, las tareas
-- FOTOS y ORDENAR y los permisos del grupo Administrador. Idempotente: todo se busca por nombre.
-- Requiere el RBAC por defecto del core (core/0007), que crea el grupo Administrador y las tareas base.

-- 1) Tareas propias de los modulos.
INSERT INTO `wr_tarea` (`tar_nombre`) SELECT 'FOTOS' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `wr_tarea` WHERE `tar_nombre` = 'FOTOS');
INSERT INTO `wr_tarea` (`tar_nombre`) SELECT 'ORDENAR' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `wr_tarea` WHERE `tar_nombre` = 'ORDENAR');

-- 2) Seccion Website (raiz del menu).
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'website', 'Website', 'M', NULL, 8, 'fa fa-th-large', 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'website');

SET @website := (SELECT MIN(`ele_id`) FROM `wr_elemento` WHERE `ele_nombre` = 'website');

-- 3) Modulos bajo Website.
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'blogs', 'Blog', 'S', @website, 2, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'blogs');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'contenidos', 'Contenido', 'S', @website, 4, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'contenidos');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'galerias', 'Galerias', 'S', @website, 7, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'galerias');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'slider', 'Slider', 'S', @website, 8, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'slider');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'redes', 'Redes Sociales', 'S', @website, 9, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'redes');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'servicios', 'Servicios', 'S', @website, 13, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'servicios');
INSERT INTO `wr_elemento` (`ele_nombre`, `ele_titulo`, `ele_tipo`, `ele_padre`, `ele_orden`, `ele_icono`, `ele_estado`, `ele_tarea`)
SELECT 'testimonios', 'Testimonios', 'S', @website, 19, NULL, 'H', 'ACCEDER' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `wr_elemento` WHERE `ele_nombre` = 'testimonios');

-- 4) Tareas de cada elemento y permisos de Administrador, las que falten.
INSERT INTO `wr_elemento_tarea` (`eta_ele_id`, `eta_tar_id`)
SELECT e.`ele_id`, t.`tar_id`
FROM `wr_elemento` e
INNER JOIN `wr_tarea` t
WHERE (
    (e.`ele_nombre` = 'website' AND t.`tar_nombre` = 'ACCEDER')
    OR (e.`ele_nombre` = 'blogs' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'contenidos' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'galerias' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'FOTOS'))
    OR (e.`ele_nombre` = 'slider' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'ORDENAR'))
    OR (e.`ele_nombre` = 'redes' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'servicios' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'testimonios' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'ORDENAR'))
  )
  AND NOT EXISTS (SELECT 1 FROM `wr_elemento_tarea` x WHERE x.`eta_ele_id` = e.`ele_id` AND x.`eta_tar_id` = t.`tar_id`)
ORDER BY e.`ele_id`, t.`tar_id`;

INSERT INTO `wr_permiso` (`pmo_ele_id`, `pmo_tar_id`, `pmo_gru_id`)
SELECT e.`ele_id`, t.`tar_id`, 'Administrador'
FROM `wr_elemento` e
INNER JOIN `wr_tarea` t
WHERE (
    (e.`ele_nombre` = 'website' AND t.`tar_nombre` = 'ACCEDER')
    OR (e.`ele_nombre` = 'blogs' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'contenidos' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'galerias' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'FOTOS'))
    OR (e.`ele_nombre` = 'slider' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'ORDENAR'))
    OR (e.`ele_nombre` = 'redes' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'servicios' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR'))
    OR (e.`ele_nombre` = 'testimonios' AND t.`tar_nombre` IN ('ACCEDER', 'AGREGAR', 'EDITAR', 'ELIMINAR', 'ORDENAR'))
  )
  AND EXISTS (SELECT 1 FROM `wr_grupo` g WHERE g.`gru_id` = 'Administrador')
  AND NOT EXISTS (
      SELECT 1 FROM `wr_permiso` p
      WHERE p.`pmo_ele_id` = e.`ele_id` AND p.`pmo_tar_id` = t.`tar_id` AND p.`pmo_gru_id` = 'Administrador'
  )
ORDER BY e.`ele_id`, t.`tar_id`;

-- Los usuarios con sesion recargan su menu y sus permisos.
UPDATE `wr_parametro` SET `par_valor` = CAST(CAST(`par_valor` AS UNSIGNED) + 1 AS CHAR) WHERE `par_clave` = 'rbac_version';
