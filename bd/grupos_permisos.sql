-- =============================================================================
-- CRUD Grupos con Permisos
-- Ejecutar en orden. Ajustar el prefijo 'wr_' segun tu configuracion DB_PREFIX.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. Tabla de grupos (si no existe ya)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wr_grupo` (
    `gru_id`          VARCHAR(100)  NOT NULL,
    `gru_descripcion` VARCHAR(250)  NOT NULL,
    `gru_estado`      CHAR(1)       NOT NULL DEFAULT 'H'
                      COMMENT 'H=Activo, I=Inactivo',
    PRIMARY KEY (`gru_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Tabla de elementos/modulos (modulo del sistema) - referencia
--    (solo se incluye si no existe; la estructura debe coincidir con la app)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wr_elemento` (
    `ele_id`     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `ele_nombre` VARCHAR(150)  NOT NULL,
    `ele_titulo` VARCHAR(250)  NOT NULL,
    `ele_tipo`   VARCHAR(50)   DEFAULT NULL,
    `ele_estado` CHAR(1)       DEFAULT 'H' COMMENT 'H=Activo, I=Inactivo',
    `ele_icono`  VARCHAR(100)  DEFAULT NULL,
    `ele_orden`  INT           DEFAULT 0,
    `ele_padre`  INT UNSIGNED  DEFAULT 0,
    `ele_tarea`  INT UNSIGNED  DEFAULT NULL,
    PRIMARY KEY (`ele_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Tabla de permisos grupo <-> elemento
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wr_permiso` (
    `pmo_gru_id` VARCHAR(100) NOT NULL,
    `pmo_ele_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`pmo_gru_id`, `pmo_ele_id`),
    CONSTRAINT `fk_pmo_grupo`
        FOREIGN KEY (`pmo_gru_id`)
        REFERENCES `wr_grupo` (`gru_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_pmo_elemento`
        FOREIGN KEY (`pmo_ele_id`)
        REFERENCES `wr_elemento` (`ele_id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Columna gru_estado (por si la tabla grupo ya existia sin ella)
-- -----------------------------------------------------------------------------
ALTER TABLE `wr_grupo`
    MODIFY COLUMN `gru_estado` CHAR(1) NOT NULL DEFAULT 'H'
    COMMENT 'H=Activo, I=Inactivo';

-- -----------------------------------------------------------------------------
-- 5. Datos de ejemplo (opcional)
-- -----------------------------------------------------------------------------
-- INSERT IGNORE INTO `wr_grupo` (`gru_id`, `gru_descripcion`, `gru_estado`) VALUES
--     ('ADMIN',    'Administrador',    'H'),
--     ('OPERADOR', 'Operador',         'H'),
--     ('CONSULTA', 'Solo consulta',    'H');
